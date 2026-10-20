<?php
namespace Tests\Feature;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Services\Faq\FaqReadRange;
use App\Services\Faq\FaqReadRecorder;
use App\Services\Faq\FaqReadReport;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
final class FaqReadReportTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void { CarbonImmutable::setTestNow(); parent::tearDown(); }
    #[Test] public function PresetsUseBangkokAndIncludeToday(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T18:00:00Z'));
        $seven = FaqReadRange::From(['range' => '7d']);
        $this->assertSame('2026-10-15', $seven->start_date);
        $this->assertSame('2026-10-21', $seven->end_date);
        $this->assertSame('2026-09-22', FaqReadRange::From([])->start_date);
    }
    #[Test] public function DeletedHistoryAndLiveZeroCountsAppearWithoutDuplicateRows(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T01:00:00Z'));
        $category = FaqCategory::create(['name' => 'Primary category', 'name_en' => 'General', 'slug' => 'report-fixture', 'is_published' => true]);
        $read_faq = Faq::create(['category_id' => $category->id, 'question' => 'Primary read',
            'question_en' => 'Read question', 'answer' => 'Answer', 'is_published' => true]);
        $zero_faq = Faq::create(['category_id' => $category->id, 'question' => 'Primary zero',
            'question_en' => 'Zero question', 'answer' => 'Answer', 'is_published' => false]);
        app(FaqReadRecorder::class)->Record($read_faq->id, str_repeat('a', 64));
        $read_id = $read_faq->id;
        $read_faq->delete();
        $report = app(FaqReadReport::class)->Build(FaqReadRange::From(['range' => 'custom',
            'start_date' => '2026-10-20', 'end_date' => '2026-10-20']), 'en');
        $rows = $report->getCollection();
        $this->assertSame(['faq_reference_id' => $read_id, 'read_count' => 1,
            'question' => 'Read question', 'category' => 'General', 'is_deleted' => true], $rows->first());
        $this->assertSame(0, $rows->firstWhere('faq_reference_id', $zero_faq->id)['read_count']);
        $this->assertSame($rows->count(), $rows->pluck('faq_reference_id')->unique()->count());
    }
    private function ClearOnlyIsolatedFaqs(): void
    {
        $this->assertSame('sqlite', \Illuminate\Support\Facades\DB::connection()->getDriverName());
        $this->assertSame(':memory:', \Illuminate\Support\Facades\DB::connection()->getDatabaseName());
        \Illuminate\Support\Facades\DB::table('faqs')->delete();
    }
    #[Test] public function InvalidRangesPagesAndCalendarBoundsCannotBuildAReport(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T01:00:00Z'));
        $invalid_parameters = [
            ['range' => '90d'], ['range' => []], ['page' => 0], ['page' => -1], ['page' => 'abc'], ['page' => []],
            ['range' => 'custom'],
            ['range' => 'custom', 'start_date' => '2026-10-21', 'end_date' => '2026-10-20'],
            ['range' => 'custom', 'start_date' => '2026-10-20', 'end_date' => '2026-10-21'],
            ['range' => 'custom', 'start_date' => '2026-02-30', 'end_date' => '2026-03-01'],
            ['range' => 'custom', 'start_date' => '2025-02-29', 'end_date' => '2025-03-01'],
            ['range' => 'custom', 'start_date' => [], 'end_date' => '2026-03-01'],
        ];
        foreach ($invalid_parameters as $parameters) {
            try {
                FaqReadRange::From($parameters);
                $this->fail('Invalid filters must not produce a range.');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
        $leap_range = FaqReadRange::From(['range' => 'custom', 'start_date' => '2024-02-29', 'end_date' => '2024-03-01']);
        $this->assertSame('2024-02-29', $leap_range->start_date);
        $this->assertSame('2024-03-01', $leap_range->end_date);
    }
    #[Test] public function TiesPaginationAndPreCollectionZeroCountsAreStableAndBounded(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T01:00:00Z'));
        $this->ClearOnlyIsolatedFaqs();
        $category = FaqCategory::query()->firstOrFail();
        for ($faq_id = 401; $faq_id <= 421; $faq_id++) {
            Faq::forceCreate(['id' => $faq_id, 'category_id' => $category->id, 'question' => 'Question '.$faq_id,
                'answer' => 'Answer', 'is_published' => false]);
        }
        \Illuminate\Support\Facades\DB::enableQueryLog();
        try {
            $first_page = app(FaqReadReport::class)->Build(FaqReadRange::From(['range' => 'custom', 'start_date' => '2020-01-01', 'end_date' => '2020-01-01']), 'en');
            $this->assertLessThanOrEqual(5, count(\Illuminate\Support\Facades\DB::getQueryLog()));
        } finally {
            \Illuminate\Support\Facades\DB::disableQueryLog();
            \Illuminate\Support\Facades\DB::flushQueryLog();
        }
        $this->assertSame(21, $first_page->total());
        $this->assertSame(20, $first_page->perPage());
        $this->assertSame(range(401, 420), $first_page->getCollection()->pluck('faq_reference_id')->all());
        $this->assertSame([0], $first_page->getCollection()->pluck('read_count')->unique()->all());
        $second_page = app(FaqReadReport::class)->Build(FaqReadRange::From(['page' => 2]), 'th');
        $this->assertSame([421], $second_page->getCollection()->pluck('faq_reference_id')->all());
        $this->assertSame(2, $second_page->currentPage());
    }
    #[Test] public function DeletedLabelsUseLatestSnapshotOutsideTheSelectedRangeWithFallback(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T01:00:00Z'));
        $category = FaqCategory::create(['name' => 'Old category', 'name_en' => 'Old category EN', 'slug' => 'snapshot-fixture', 'is_published' => true]);
        $faq = Faq::create(['category_id' => $category->id, 'question' => 'Old primary', 'question_en' => 'Old English', 'answer' => 'Answer', 'is_published' => true]);
        app(FaqReadRecorder::class)->Record($faq->id, str_repeat('a', 64));
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-21T01:00:00Z'));
        $faq->update(['question' => 'Latest primary', 'question_en' => null]);
        $category->update(['name' => 'Latest category', 'name_en' => null]);
        app(FaqReadRecorder::class)->Record($faq->id, str_repeat('b', 64));
        $faq_id = $faq->id;
        $faq->delete();
        $range = FaqReadRange::From(['range' => 'custom', 'start_date' => '2026-10-20', 'end_date' => '2026-10-20']);
        \Illuminate\Support\Facades\DB::enableQueryLog();
        try {
            $rows = app(FaqReadReport::class)->Build($range, 'en')->getCollection();
            $this->assertLessThanOrEqual(5, count(\Illuminate\Support\Facades\DB::getQueryLog()));
        } finally {
            \Illuminate\Support\Facades\DB::disableQueryLog();
            \Illuminate\Support\Facades\DB::flushQueryLog();
        }
        $this->assertSame(['faq_reference_id' => $faq_id, 'read_count' => 1,
            'question' => 'Latest primary', 'category' => 'Latest category', 'is_deleted' => true], $rows->first());
    }
    #[Test] public function LiveLabelsReflectCurrentContentAndAnActuallyEmptyReportStaysEmpty(): void
    {
        $this->ClearOnlyIsolatedFaqs();
        $this->assertSame(0, app(FaqReadReport::class)->Build(FaqReadRange::From([]), 'en')->total());
        $category = FaqCategory::query()->firstOrFail();
        $category->update(['is_published' => true]);
        $faq = Faq::create(['category_id' => $category->id, 'question' => 'Original primary', 'answer' => 'Answer', 'is_published' => true]);
        app(FaqReadRecorder::class)->Record($faq->id, str_repeat('a', 64));
        $faq->update(['question_en' => 'Current question']);
        $category->update(['name_en' => 'Current category']);
        $row = app(FaqReadReport::class)->Build(FaqReadRange::From([]), 'en')->getCollection()->first();
        $this->assertSame('Current question', $row['question']);
        $this->assertSame('Current category', $row['category']);
        $this->assertFalse($row['is_deleted']);
    }
}
