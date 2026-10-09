<?php

namespace Tests\Feature;

use App\Domain\Ebook\BookCatalog;
use App\Domain\Ebook\DemoData;
use App\Http\Controllers\EbookAdminController;
use App\Jobs\ConvertEbookPdf;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class EbookAdminTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = storage_path('framework/testing/ebook-admin-'.Str::uuid());
        File::makeDirectory($this->root, 0700, true);
        config(['ebook.root' => $this->root]);
        DemoData::schema(); DemoData::seed();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->root);
        parent::tearDown();
    }

    private function actor(bool $central = true): User
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->forceFill(['id' => 58, 'name' => 'Importer', 'department_id' => $central ? 1 : 2]);
        $user->shouldReceive('isHq')->andReturn($central);
        $user->shouldReceive('hasRole')->andReturn($central);
        $user->shouldReceive('can')->andReturn(true);
        return $user;
    }

    public function test_catalog_combines_full_and_half_term_sets_without_inventing_volumes(): void
    {
        $catalog = app(BookCatalog::class);
        $this->assertCount(10, $catalog->volumes());
        $this->assertSame([1, 2], $catalog->selected('1:1:G11')['catalog_ids']);
        $this->assertSame([1, 3], $catalog->selected('1:1:G20')['catalog_ids']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $catalog->selected('1:1:G99');
    }

    public function test_import_uses_catalog_code_tracks_actor_and_keeps_previous_edition(): void
    {
        Bus::fake([ConvertEbookPdf::class]);
        $this->withoutMiddleware()->actingAs($this->actor());
        $this->post('/ebooks/import', ['catalog_key' => '1:1:G12', 'code' => 'FORGED', 'title' => 'FORGED',
            'catalog_ids' => [3], 'pdf' => UploadedFile::fake()->create('011.pdf', 20, 'application/pdf')])->assertRedirect();
        $book = DB::table('ebook_books')->where('code', 'G12')->first();
        $this->assertNotNull($book);
        $this->assertSame('หนังสือ G12', $book->title);
        $this->assertEquals([1, 2], DB::table('ebook_catalog')->where('book_id', $book->id)->orderBy('bookuse_id')->pluck('bookuse_id')->all());
        $version = DB::table('ebook_versions')->where('book_id', $book->id)->first();
        $this->assertSame('011.pdf', $version->original_filename);
        $this->assertSame('Importer', $version->uploaded_by_name);
        $this->assertEquals(58, $version->uploaded_by);
        $this->assertFileExists($this->root.'/'.$version->source_path);
        $this->post('/ebooks/import', ['catalog_key' => '1:1:G12', 'pdf' => UploadedFile::fake()->create('new.pdf', 20, 'application/pdf')])->assertRedirect();
        $this->assertSame(2, DB::table('ebook_versions')->where('book_id', $book->id)->count());
        $this->assertSame(2, DB::table('ebook_admin_events')->where('book_id', $book->id)->where('action', 'imported')->count());
        $this->assertSame(0, DB::table('ebook_events')->count());
        Bus::assertDispatchedTimes(ConvertEbookPdf::class, 2);
    }

    public function test_import_rejects_unknown_code_and_conflicting_legacy_mapping(): void
    {
        Bus::fake([ConvertEbookPdf::class]);
        $this->withoutMiddleware()->actingAs($this->actor());
        $this->postJson('/ebooks/import', ['catalog_key' => '1:1:G99', 'pdf' => UploadedFile::fake()->create('x.pdf', 20, 'application/pdf')])->assertUnprocessable();
        DB::table('ebook_catalog')->insert(['book_id' => 1, 'bookuse_id' => 3]);
        $this->postJson('/ebooks/import', ['catalog_key' => '1:1:G11', 'pdf' => UploadedFile::fake()->create('x.pdf', 20, 'application/pdf')])->assertUnprocessable();
        $this->assertSame(2, DB::table('ebook_versions')->count());
        Bus::assertNothingDispatched();
    }

    public function test_publish_audits_staff_without_changing_student_work_or_first_publish_time(): void
    {
        $this->withoutMiddleware()->actingAs($this->actor());
        DB::table('ebook_versions')->where('id', 1)->update(['status' => 'ready']);
        DB::table('ebook_pages')->insert(['id' => 1, 'version_id' => 1, 'number' => 1, 'image_path' => 'page.png', 'width' => 1, 'height' => 1]);
        DB::table('ebook_work')->insert(['student_id' => 1, 'page_id' => 1, 'document' => '{"schema":1,"objects":[]}', 'revision' => 5, 'last_mutation_id' => Str::uuid()]);
        $this->post('/ebooks/versions/1/publish')->assertRedirect();
        $first = DB::table('ebook_versions')->find(1)->published_at;
        $this->travel(1)->hours();
        $this->post('/ebooks/versions/1/publish')->assertRedirect();
        $this->assertSame($first, DB::table('ebook_versions')->find(1)->published_at);
        $this->assertSame(1, DB::table('ebook_admin_events')->where('action', 'published')->count());
        $this->assertEquals(5, DB::table('ebook_work')->value('revision'));
    }

    public function test_activity_filters_use_occurrence_time_and_keep_branch_scope(): void
    {
        foreach ([[1, '2026-10-08 18:00:00', 'pen'], [1, '2026-10-09 18:00:00', 'pen'], [2, '2026-10-08 18:00:00', 'pen'], [1, '2026-10-08 18:00:00', 'erase']] as [$student, $at, $action]) {
            DB::table('ebook_events')->insert(['event_id' => Str::uuid(), 'student_id' => $student, 'action' => $action, 'source' => 'client', 'occurred_at' => $at, 'received_at' => '2026-10-10 12:00:00']);
        }
        DB::table('contacts')->where('id', 1)->update(['centre' => 2]);
        $request = Request::create('/ebooks', 'GET', ['section' => 'activity', 'from' => '2026-10-09', 'to' => '2026-10-09', 'event_action' => 'pen']);
        $request->setUserResolver(fn () => $this->actor(false));
        $view = app(EbookAdminController::class)->index($request, app(BookCatalog::class));
        $events = $view->getData()['events'];
        $this->assertSame(1, $events->total());
        $this->assertEquals(1, $events->first()->student_id);
        $this->assertSame('2026-10-08 18:00:00', $events->first()->occurred_at);
    }

    public function test_pages_and_log_details_follow_the_staff_locale(): void
    {
        config(['ebook.demo' => true]); // Use the lightweight layout with the real locale middleware.
        $this->withoutMiddleware(\App\Http\Middleware\EnforceBranchContext::class);
        $actor = $this->actor();
        DB::table('ebook_versions')->where('id', 1)->update(['status' => 'ready', 'page_count' => 2]);
        DB::table('ebook_pages')->insert(['id' => 1, 'version_id' => 1, 'number' => 1, 'image_path' => 'page.png', 'width' => 1000, 'height' => 1400]);
        DB::table('ebook_events')->insert(['event_id' => Str::uuid(), 'student_id' => 1, 'book_id' => 1,
            'page_id' => 1, 'action' => 'capture_detected', 'source' => 'client', 'metadata' => '{"version":2}',
            'occurred_at' => now('UTC'), 'received_at' => now()]);
        foreach ([
            'en' => ['Book Library', 'Import PDF by book code', 'Book Management History', 'OS reports App visible in recording / screen sharing', 'Preview G11', 'Edition 2'],
            'th' => ['คลังหนังสือ', 'นำเข้า PDF ตามรหัสหนังสือ', 'ประวัติการจัดการหนังสือ', 'OS แจ้งว่า App อยู่ในภาพอัด / แชร์จอ', 'ดูตัวอย่าง G11', 'ฉบับ 2'],
        ] as $locale => [$library, $import, $history, $activity, $preview, $detail]) {
            $this->actingAs($actor->forceFill(['language_code' => $locale]));
            foreach (['library' => $library, 'import' => $import, 'history' => $history, 'activity' => $activity] as $section => $label) {
                $response = $this->get('/ebooks?section='.$section)->assertOk()
                    ->assertSee('<html lang="'.$locale.'">', false)->assertSee($label)->assertDontSee('ebooks.', false);
                if ($section === 'activity') { $response->assertSee($detail)->assertSee('eb-capture-status', false); }
            }
            $this->get('/ebooks/versions/1/preview')->assertOk()->assertSee($preview)->assertDontSee('ebooks.', false);
        }
    }

    public function test_import_and_filter_validation_use_the_staff_locale(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\EnforceBranchContext::class);
        $actor = $this->actor();
        foreach ([
            'en' => ['Please choose a PDF file.', 'The to date must be on or after the from date.'],
            'th' => ['กรุณาเลือกไฟล์ PDF', 'วันที่สิ้นสุดต้องไม่ก่อนวันที่เริ่มต้น'],
        ] as $locale => [$pdfMessage, $dateMessage]) {
            $this->actingAs($actor->forceFill(['language_code' => $locale]));
            $this->postJson('/ebooks/import', ['catalog_key' => '1:1:G11'])->assertUnprocessable()
                ->assertJsonPath('errors.pdf.0', $pdfMessage);
            $this->getJson('/ebooks?section=activity&from=2026-10-09&to=2026-10-08')->assertUnprocessable()
                ->assertJsonPath('errors.to.0', $dateMessage);
        }
    }
}
