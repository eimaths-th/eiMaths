<?php
namespace Tests\Feature;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Services\Faq\FaqReadRecorder;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
final class FaqReadRecorderTest extends TestCase
{
    use RefreshDatabase;
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }
    private function PublishedFaq(): Faq
    {
        $category = FaqCategory::create(['name' => 'Primary category', 'name_en' => 'General',
            'slug' => 'reads-fixture', 'is_published' => true]);
        return Faq::create(['category_id' => $category->id, 'question' => 'Primary question',
            'question_en' => 'English question', 'answer' => 'Answer', 'is_published' => true]);
    }
    #[Test] public function SchemaExists(): void
    {
        $this->assertTrue(Schema::hasTable('faq_daily_reads'));
        $this->assertTrue(Schema::hasTable('faq_read_receipts'));
    }
    #[Test] public function DuplicateAcrossMidnightDoesNotAddAnEmptyDay(): void
    {
        $faq = $this->PublishedFaq();
        $recorder = app(FaqReadRecorder::class);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T16:59:59Z'));
        $recorder->Record($faq->id, str_repeat('a', 64));
        $this->assertDatabaseHas('faq_daily_reads', ['faq_reference_id' => $faq->id,
            'read_date' => '2026-10-20', 'read_count' => 1]);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T17:00:01Z'));
        $recorder->Record($faq->id, str_repeat('a', 64));
        $this->assertDatabaseCount('faq_daily_reads', 1);
        $this->assertDatabaseCount('faq_read_receipts', 1);
        $recorder->Record($faq->id, str_repeat('b', 64));
        $this->assertDatabaseHas('faq_daily_reads', ['faq_reference_id' => $faq->id,
            'read_date' => '2026-10-21', 'read_count' => 1]);
        $faq->delete();
        $this->assertSame(2, (int) DB::table('faq_daily_reads')->sum('read_count'));
        $this->assertDatabaseCount('faq_read_receipts', 2);
    }
    private function AssertHttpStatus(callable $operation, int $status): void
    {
        try {
            $operation();
            $this->fail('Expected the recording request to be rejected.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $exception) {
            $this->assertSame($status, $exception->getStatusCode());
        }
    }
    #[Test] public function IncrementFailureRollsBackBothWritesAndAllowsRetry(): void
    {
        $faq = $this->PublishedFaq();
        $recorder = app(FaqReadRecorder::class);
        DB::unprepared("CREATE TRIGGER faq_read_test_failure BEFORE UPDATE OF read_count ON faq_daily_reads BEGIN SELECT RAISE(ABORT, 'test-only increment failure'); END");
        try {
            try {
                $recorder->Record($faq->id, str_repeat('a', 64));
                $this->fail('The injected increment failure must propagate.');
            } catch (\Illuminate\Database\QueryException $exception) {
                $this->assertDatabaseCount('faq_read_receipts', 0);
                $this->assertDatabaseCount('faq_daily_reads', 0);
            }
        } finally {
            DB::unprepared('DROP TRIGGER faq_read_test_failure');
        }
        $recorder->Record($faq->id, str_repeat('a', 64));
        $this->assertDatabaseCount('faq_read_receipts', 1);
        $this->assertSame(1, (int) DB::table('faq_daily_reads')->sum('read_count'));
    }
    #[Test] public function PublicationAndCrossQuestionKeyConflictsCannotAddReads(): void
    {
        $faq = $this->PublishedFaq();
        $other_faq = Faq::create(['category_id' => $faq->category_id, 'question' => 'Other question', 'answer' => 'Answer', 'is_published' => true]);
        $recorder = app(FaqReadRecorder::class);
        $recorder->Record($faq->id, str_repeat('a', 64));
        $this->AssertHttpStatus(fn () => $recorder->Record($other_faq->id, str_repeat('a', 64)), 409);
        $this->AssertHttpStatus(fn () => $recorder->Record(999999, str_repeat('b', 64)), 404);
        $faq->update(['is_published' => false]);
        $this->AssertHttpStatus(fn () => $recorder->Record($faq->id, str_repeat('a', 64)), 404);
        $faq->update(['is_published' => true]);
        $faq->category->update(['is_published' => false]);
        $this->AssertHttpStatus(fn () => $recorder->Record($faq->id, str_repeat('b', 64)), 404);
        $faq->delete();
        $this->AssertHttpStatus(fn () => $recorder->Record($faq->id, str_repeat('a', 64)), 404);
        $this->assertDatabaseCount('faq_daily_reads', 1);
        $this->assertDatabaseCount('faq_read_receipts', 1);
        $this->assertSame(1, (int) DB::table('faq_daily_reads')->sum('read_count'));
    }
    #[Test] public function LatestSnapshotsAndUtcReceiptsContainOnlyApprovedFields(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-10-20T18:00:00Z'));
        $faq = $this->PublishedFaq();
        $recorder = app(FaqReadRecorder::class);
        $recorder->Record($faq->id, str_repeat('a', 64));
        $faq->update(['question' => 'Changed primary', 'question_en' => null]);
        $faq->category->update(['name' => 'Changed category', 'name_en' => null]);
        $recorder->Record($faq->id, str_repeat('b', 64));
        $this->assertDatabaseHas('faq_daily_reads', ['faq_reference_id' => $faq->id, 'read_date' => '2026-10-21',
            'read_count' => 2, 'question_th' => 'Changed primary', 'question_en' => null,
            'category_th' => 'Changed category', 'category_en' => null]);
        $this->assertDatabaseHas('faq_read_receipts', ['event_key' => str_repeat('a', 64), 'first_recorded_at' => '2026-10-20 18:00:00.000000']);
        $this->assertEqualsCanonicalizing(['event_key', 'daily_read_id', 'first_recorded_at'], Schema::getColumnListing('faq_read_receipts'));
        $this->assertEqualsCanonicalizing(['id', 'faq_reference_id', 'read_date', 'read_count', 'question_th', 'question_en', 'category_th', 'category_en'], Schema::getColumnListing('faq_daily_reads'));
    }
    #[Test] public function AnUnrelatedReceiptUniquenessFailureIsNotAcknowledged(): void
    {
        $faq = $this->PublishedFaq();
        $recorder = app(FaqReadRecorder::class);
        $recorder->Record($faq->id, str_repeat('a', 64));
        DB::unprepared('CREATE UNIQUE INDEX faq_read_test_extra_unique ON faq_read_receipts(daily_read_id)');
        try {
            try {
                $recorder->Record($faq->id, str_repeat('b', 64));
                $this->fail('A missing matching receipt must remain an explicit failure.');
            } catch (\App\Services\Faq\DuplicateFaqRead $exception) {
                $this->assertDatabaseCount('faq_read_receipts', 1);
                $this->assertSame(1, (int) DB::table('faq_daily_reads')->sum('read_count'));
            }
        } finally {
            DB::unprepared('DROP INDEX faq_read_test_extra_unique');
        }
    }
}
