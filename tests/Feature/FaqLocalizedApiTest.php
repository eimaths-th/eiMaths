<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Services\Faq\FaqSlugWriter;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\FaqSlugFixture;
use Tests\TestCase;

final class FaqLocalizedApiTest extends TestCase
{
    use RefreshDatabase, FaqSlugFixture;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function CurrentAndRepeatedAliasesResolveToTheLatestSameLanguage(): void
    {
        config(['faq-urls.enabled' => true]);
        CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes());
        $original = 'primary-question-'.$faq->id.'-1';
        app(FaqSlugWriter::class)->Update($faq->id, $this->Attributes(['question' => 'Second primary']));
        app(FaqSlugWriter::class)->Update($faq->id, $this->Attributes(['question' => 'Third primary']));
        foreach ([$original, 'second-primary-'.$faq->id.'-2'] as $old_slug) {
            $response = $this->getJson('/api/faq-pages/resolve/'.$old_slug.'?locale=th')->assertOk()
                ->assertJsonPath('match_type', 'alias')
                ->assertJsonPath('faq.id', $faq->id)
                ->assertJsonPath('faq.localized_slugs.th', 'third-primary-'.$faq->id.'-3')
                ->assertJsonPath('alias_expires_at', '2027-10-20T09:00:00.000000Z');
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
        $this->getJson('/api/faq-pages/resolve/third-primary-'.$faq->id.'-3')->assertOk()
            ->assertJsonPath('match_type', 'current')->assertJsonPath('alias_expires_at', null);
        $this->getJson('/api/faq-pages/'.$faq->id.'?locale=en')->assertOk()
            ->assertJsonPath('question', 'English question');
        $this->getJson('/api/faq-pages/resolve/'.$original.'?locale=en')->assertNotFound()
            ->assertJsonPath('error_code', 'FAQ_NOT_FOUND');
        CarbonImmutable::setTestNow('2027-10-20T08:59:59.999999Z');
        $this->getJson('/api/faq-pages/resolve/'.$original)->assertOk();
        CarbonImmutable::setTestNow('2027-10-20T09:00:00Z');
        $this->getJson('/api/faq-pages/resolve/'.$original)->assertNotFound();
        $this->getJson('/api/faq-pages/resolve/'.$original.'?locale=en')->assertNotFound();
    }

    #[Test]
    public function ExpiredEnglishFallbackDoesNotResolveThroughTheThaiCurrentSlug(): void
    {
        config(['faq-urls.enabled' => true]);
        CarbonImmutable::setTestNow('2026-10-20T09:00:00Z');
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes(['question_en' => null]));
        $shared_slug = 'primary-question-'.$faq->id.'-1';
        app(FaqSlugWriter::class)->Update($faq->id, $this->Attributes(['question_en' => 'Actual translation']));
        CarbonImmutable::setTestNow('2027-10-20T09:00:00Z');
        $this->getJson('/api/faq-pages/resolve/'.$shared_slug.'?locale=en')->assertNotFound();
        $this->getJson('/api/faq-pages/resolve/'.$shared_slug)->assertOk()
            ->assertJsonPath('match_type', 'current');
    }

    #[Test]
    public function HiddenDeletedDisabledAndIncompleteStatesKeepDistinctFailures(): void
    {
        config(['faq-urls.enabled' => true]);
        $faq = app(FaqSlugWriter::class)->Create($this->Attributes());
        $slug = $this->CurrentSlug($faq, 'th');
        $faq->category->update(['is_published' => false]);
        $this->getJson('/api/faq-pages/resolve/'.$slug)->assertNotFound();
        $this->getJson('/api/faq-pages/'.$faq->id)->assertNotFound();
        $faq->category->update(['is_published' => true]);
        $faq->update(['is_published' => false]);
        $this->getJson('/api/faq-pages/resolve/'.$slug)->assertNotFound();
        $faq->update(['is_published' => true]);
        DB::table('faq_localized_slug_states')->where('faq_id', $faq->id)->where('locale', 'en')->delete();
        $this->getJson('/api/faq-pages/'.$faq->id)->assertStatus(503)
            ->assertJsonPath('error_code', 'FAQ_CONTENT_UNAVAILABLE');
        config(['faq-urls.enabled' => false]);
        $this->getJson('/api/faq-pages/'.$faq->id)->assertStatus(503);
        config(['faq-urls.enabled' => true]);
        $faq->delete();
        $this->getJson('/api/faq-pages/resolve/'.$slug)->assertNotFound();
        $this->getJson('/api/faq-pages/resolve/invalid%2Fsegment')->assertNotFound();
        $this->getJson('/api/faq-pages/0')->assertNotFound();
        $this->getJson('/api/faq-pages/999999999999999999999999999999')->assertNotFound();
        $this->assertSame(0, DB::table('faq_daily_reads')->count());
    }

    #[Test]
    public function MaintenanceErrorsKeepTheJsonAndNoStoreContract(): void
    {
        $maintenance = new class implements \Illuminate\Contracts\Foundation\MaintenanceMode {
            private bool $is_active = false;
            private array $maintenance_payload = [];
            public function activate(array $payload): void
            {
                $this->maintenance_payload = $payload;
                $this->is_active = true;
            }
            public function deactivate(): void { $this->is_active = false; }
            public function active(): bool { return $this->is_active; }
            public function data(): array { return $this->maintenance_payload; }
        };
        $this->app->instance(\Illuminate\Contracts\Foundation\MaintenanceMode::class, $maintenance);
        try {
            foreach ([[], ['template' => 'Test-only maintenance template']] as $payload) {
                $maintenance->activate($payload + ['status' => 503]);
                foreach (['/api/faq-pages/11', '/api/faq-pages/resolve/earlier-title-11-1'] as $path) {
                    $response = $this->get($path)->assertStatus(503)
                        ->assertHeader('Content-Type', 'application/json')
                        ->assertJsonPath('error_code', 'FAQ_CONTENT_UNAVAILABLE');
                    $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
                    $response->assertDontSee('Test-only maintenance template');
                }
            }
        } finally {
            $maintenance->deactivate();
        }
    }
}
