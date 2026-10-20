<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Services\Faq\FaqSlugWriter;
use Carbon\CarbonImmutable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqSlugFixture;
use Tests\TestCase;

final class FaqSlugMaintenanceTest extends TestCase
{
    use RefreshDatabase, FaqSlugFixture;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function BackfillPreservesExistingDataAndDoesNotRenewAliases(): void
    {
        CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
        $faq = Faq::forceCreate($this->Attributes(['slug' => 'existing-link', 'is_published' => false]));
        $before = (array) DB::table('faqs')->find($faq->id);
        $analytics_before = $this->RecordReadHistory($faq);
        $this->artisan('faq:backfill-slugs')->assertExitCode(0);
        $this->assertSame(2, DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->count());
        $this->assertSame($before, (array) DB::table('faqs')->find($faq->id));
        $routes_before = DB::table('faq_slug_routes')->where('faq_id', $faq->id)->orderBy('id')->get()->toJson();
        CarbonImmutable::setTestNow('2026-11-20T09:00:00Z');
        $this->artisan('faq:backfill-slugs')->assertExitCode(0);
        $this->assertSame($routes_before, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->orderBy('id')->get()->toJson());
        DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->where('locale', 'en')->delete();
        $this->artisan('faq:backfill-slugs')->assertExitCode(1);
        $this->assertSame($routes_before, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->orderBy('id')->get()->toJson());
        $this->assertSame($analytics_before, $this->ReadHistorySnapshot());
    }

    #[Test]
    public function PruningRemovesOnlyExpiredAliasesAndKeepsRevisionState(): void
    {
        $this->assertArrayHasKey('faq:prune-slug-history', Artisan::all());
        config(['faq-urls.enabled' => true]);
        CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes());
        $analytics_before = $this->RecordReadHistory($faq);
        app(FaqSlugWriter::class)->Update($faq->id, $this->Attributes(['question' => 'Changed primary']));
        $archived_routes = [];
        for ($number = 1; $number <= 501; $number++) {
            $archived_routes[] = ['faq_id' => $faq->id, 'locale' => 'th',
                'slug' => 'archived-'.$faq->id.'-'.$number, 'revision' => null,
                'current_marker' => null, 'retired_at' => '2026-10-20 09:00:00.000000',
                'expires_at' => '2027-10-20 09:00:00.000000'];
        }
        foreach (array_chunk($archived_routes, 100) as $route_chunk) {
            DB::table('faq_slug_routes')->insert($route_chunk);
        }
        $states_before = DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->orderBy('locale')->get()->toJson();
        $current_before = DB::table('faq_slug_routes')->where('faq_id', $faq->id)->where('current_marker', 1)->orderBy('locale')->get()->toJson();
        $content_before = (array) DB::table('faqs')->find($faq->id);
        CarbonImmutable::setTestNow('2027-10-20T08:59:59.999999Z');
        $this->artisan('faq:prune-slug-history')->assertExitCode(0);
        $this->assertGreaterThan(0, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->whereNull('current_marker')->count());
        CarbonImmutable::setTestNow('2027-10-20T09:00:00.000000Z');
        $this->artisan('faq:prune-slug-history')->assertExitCode(0);
        $this->artisan('faq:prune-slug-history')->assertExitCode(0);
        $this->assertSame(0, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->whereNull('current_marker')->count());
        $this->assertSame($states_before, DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->orderBy('locale')->get()->toJson());
        $this->assertSame($current_before, DB::table('faq_slug_routes')->where('faq_id', $faq->id)->where('current_marker', 1)->orderBy('locale')->get()->toJson());
        $this->assertSame($content_before, (array) DB::table('faqs')->find($faq->id));
        app(FaqSlugWriter::class)->Update($faq->id, $this->Attributes());
        $this->assertSame('primary-question-'.$faq->id.'-3', $this->CurrentSlug($faq, 'th'));
        $this->assertSame($analytics_before, $this->ReadHistorySnapshot());
    }

    #[Test]
    public function CleanupIsRegisteredAtTheApprovedTimeAndHonorsActivation(): void
    {
        $events = collect(app(Schedule::class)->events())->filter(
            fn ($event): bool => str_contains((string) $event->command, 'faq:prune-slug-history'));
        $this->assertCount(1, $events);
        $event = $events->first();
        $this->assertSame('30 2 * * *', $event->expression);
        $this->assertSame('Asia/Bangkok', $event->timezone);
        config(['faq-urls.enabled' => false]);
        $this->assertFalse($event->filtersPass(app()));
    }

    private function RecordReadHistory(Faq $faq): array
    {
        $daily_read_id = DB::table('faq_daily_reads')->insertGetId([
            'faq_reference_id' => $faq->id, 'read_date' => '2026-10-20', 'read_count' => 7,
            'question_th' => 'Earlier question', 'question_en' => 'Earlier English question',
            'category_th' => 'Earlier category', 'category_en' => null,
        ]);
        DB::table('faq_read_receipts')->insert([
            'event_key' => str_repeat('a', 64), 'daily_read_id' => $daily_read_id,
            'first_recorded_at' => '2026-10-20 09:00:00.123456',
        ]);
        return $this->ReadHistorySnapshot();
    }

    private function ReadHistorySnapshot(): array
    {
        return [
            DB::table('faq_daily_reads')->orderBy('id')->get()->toJson(),
            DB::table('faq_read_receipts')->orderBy('event_key')->get()->toJson(),
        ];
    }
}
