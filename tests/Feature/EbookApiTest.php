<?php

namespace Tests\Feature;

use App\Domain\Ebook\DemoData;
use App\Domain\Ebook\PdfConverter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class EbookApiTest extends TestCase
{
    private string $device;
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->device = (string) Str::uuid();
        $this->root = storage_path('framework/testing/ebook-'.Str::uuid());
        File::makeDirectory($this->root, 0700, true);
        config(['ebook.root' => $this->root]);
        DemoData::schema(); DemoData::seed();
        foreach ([1, 2] as $id) {
            DB::table('ebook_versions')->where('id', $id)->update(['status' => 'ready', 'page_count' => 1, 'published_at' => now()]);
            DB::table('ebook_books')->where('id', $id)->update(['published_version_id' => $id]);
            DB::table('ebook_pages')->insert(['id' => $id, 'version_id' => $id, 'number' => 1, 'image_path' => 'page.png', 'width' => 1000, 'height' => 1400]);
        }
        file_put_contents($this->root.'/page.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aZ1sAAAAASUVORK5CYII='));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->root);
        parent::tearDown();
    }

    private function login(string $code = 'DEMO001'): string
    {
        return $this->postJson('/api/ebook/v1/login', ['code' => $code, 'password' => 'Demo@eiMaths26',
            'device_id' => $this->device, 'device_label' => 'Test tablet'])->assertOk()->json('token');
    }

    private function authenticate(string $code = 'DEMO001'): void
    {
        $token = $this->login($code);
        $this->withHeaders(['Authorization' => 'Bearer '.$token, 'X-Device-Id' => $this->device]);
    }

    private function work(int $revision = 0): array
    {
        return ['revision' => $revision, 'mutation_id' => (string) Str::uuid(), 'document' => ['schema' => 1, 'objects' => [[
            'id' => (string) Str::uuid(), 'type' => 'pen', 'x' => .2, 'y' => .4, 'w' => .3, 'h' => .1,
            'rotation' => 0, 'color' => 4280572326, 'strokeWidth' => .004, 'points' => [[0, 0], [1, 1]],
        ]]]];
    }

    private function loginData(): array
    {
        return ['code' => 'DEMO001', 'password' => 'Demo@eiMaths26',
            'device_id' => $this->device, 'device_label' => 'Test tablet'];
    }

    private function lessons(int $count, ?string $endDate = null): void
    {
        for ($i = 0; $i < $count; $i++) {
            DB::table('histrories')->insert([
                'student_id' => 1, 'level_id' => 1, 'term' => '1', 'bookuse' => '1',
                'date' => today()->subDays($count - $i)->toDateString(),
                'start_date' => today()->subMonths(4)->toDateString(),
                'end_date' => $endDate ?? today()->addMonth()->toDateString(),
            ]);
        }
    }

    public function test_expired_course_cannot_login_even_with_remaining_lessons(): void
    {
        DB::table('contacts')->where('id', 1)->update(['free_course' => 2]);
        $this->lessons(1, today()->subDay()->toDateString());
        $this->postJson('/api/ebook/v1/login', $this->loginData())
            ->assertForbidden()->assertJsonPath('code', 'course_expired');
        $this->assertSame(0, DB::table('ebook_sessions')->count());
        $this->assertEquals(0, DB::table('contacts')->find(1)->discontinued);
        $invalid = $this->loginData(); $invalid['password'] = 'incorrect';
        $this->postJson('/api/ebook/v1/login', $invalid)->assertUnprocessable();
    }

    public function test_paid_and_free_lessons_are_counted_before_login_is_blocked(): void
    {
        DB::table('contacts')->where('id', 1)->update(['free_course' => 2]);
        $this->lessons(11);
        $this->authenticate();
        $this->getJson('/api/ebook/v1/books')->assertOk();
        $this->postJson('/api/ebook/v1/logout')->assertOk();
        $this->lessons(1);
        $this->postJson('/api/ebook/v1/login', $this->loginData())
            ->assertForbidden()->assertJsonPath('code', 'course_exhausted');
        $this->assertSame(0, DB::table('ebook_sessions')->count());
    }

    public function test_free_lessons_do_not_extend_an_expired_course(): void
    {
        DB::table('contacts')->where('id', 1)->update(['free_course' => 2]);
        $this->lessons(10, today()->subDay()->toDateString());
        $this->postJson('/api/ebook/v1/login', $this->loginData())
            ->assertForbidden()->assertJsonPath('code', 'course_expired');
    }

    public function test_expiry_day_is_inclusive_and_existing_session_cannot_bypass_midnight_expiry(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-15 23:59:50', 'Asia/Bangkok'));
        $this->lessons(1, '2026-10-15');
        $this->authenticate();
        $this->putJson('/api/ebook/v1/pages/1/work', $this->work())->assertOk();
        $saved = DB::table('ebook_work')->first()->document;
        $sessionExpiry = DB::table('ebook_sessions')->value('expires_at');
        $this->travel(11)->seconds();

        foreach (['/me', '/books', '/pages/1/image', '/pages/1/work'] as $path) {
            $this->getJson('/api/ebook/v1'.$path)->assertForbidden()->assertJsonPath('code', 'course_expired');
        }
        $this->postJson('/api/ebook/v1/books/1/open')->assertForbidden()->assertJsonPath('code', 'course_expired');
        $this->putJson('/api/ebook/v1/pages/1/work', $this->work(1))->assertForbidden()->assertJsonPath('code', 'course_expired');
        $this->postJson('/api/ebook/v1/events', ['events' => []])->assertForbidden()->assertJsonPath('code', 'course_expired');
        $this->assertSame($sessionExpiry, DB::table('ebook_sessions')->value('expires_at'));
        $this->assertSame($saved, DB::table('ebook_work')->first()->document);
        $this->postJson('/api/ebook/v1/logout')->assertOk();
        $this->postJson('/api/ebook/v1/login', $this->loginData())->assertForbidden()->assertJsonPath('code', 'course_expired');
        $this->travelBack();
    }

    public function test_exhausting_the_final_lesson_blocks_an_existing_session(): void
    {
        $this->lessons(9);
        $this->authenticate();
        $this->lessons(1);
        $this->getJson('/api/ebook/v1/me')->assertForbidden()->assertJsonPath('code', 'course_exhausted');
        $this->getJson('/api/ebook/v1/pages/1/image')->assertForbidden();
        $this->postJson('/api/ebook/v1/login', $this->loginData())
            ->assertForbidden()->assertJsonPath('code', 'course_exhausted');
        $this->postJson('/api/ebook/v1/logout')->assertOk();
    }

    public function test_discontinued_student_is_blocked_even_when_the_course_is_active(): void
    {
        $this->authenticate();
        foreach ([1, 2] as $status) {
            DB::table('contacts')->where('id', 1)->update(['discontinued' => $status]);
            $this->postJson('/api/ebook/v1/login', $this->loginData())
                ->assertForbidden()->assertJsonPath('code', 'account_discontinued');
            $this->getJson('/api/ebook/v1/me')->assertForbidden()->assertJsonPath('code', 'account_discontinued');
        }
        $this->postJson('/api/ebook/v1/logout')->assertOk();
    }

    public function test_unverifiable_course_cannot_grant_ebook_access(): void
    {
        DB::table('contacts')->where('id', 1)->update(['level' => 0]);
        $this->postJson('/api/ebook/v1/login', $this->loginData())
            ->assertForbidden()->assertJsonPath('code', 'course_data_unavailable');
        $this->assertSame(0, DB::table('ebook_sessions')->count());
    }

    public function test_extending_a_course_restores_access_without_reassigning_books(): void
    {
        $this->lessons(1, today()->subDay()->toDateString());
        $this->postJson('/api/ebook/v1/login', $this->loginData())->assertForbidden();
        DB::table('histrories')->where('student_id', 1)->update(['end_date' => today()->addMonth()->toDateString()]);
        $this->authenticate();
        $this->getJson('/api/ebook/v1/books')->assertOk()->assertJsonCount(2, 'books');
        $this->getJson('/api/ebook/v1/pages/1/image')->assertOk();
    }

    public function test_completed_old_course_does_not_block_a_remaining_registered_term(): void
    {
        $this->lessons(10, today()->subDay()->toDateString());
        DB::table('terms')->insert(['id' => 2, 'name' => '2', 'level_id' => 1]);
        DB::table('bookuses')->insert(['id' => 4, 'name' => 'G21-G30', 'level_id' => 1, 'term_id' => 2, 'qty' => 10]);
        DB::table('contacts')->where('id', 1)->update(['level2' => 1, 'term2' => 2, 'bookuse2' => 4]);
        $this->authenticate();
        $this->getJson('/api/ebook/v1/me')->assertOk();
    }

    public function test_production_api_uses_student_authentication_without_module_activation(): void
    {
        // An old cached config must not disable the module after deployment.
        config(['app.env' => 'production', 'ebook.enabled' => false]);
        $this->getJson('/api/ebook/v1/books')->assertUnauthorized();
        $this->postJson('/api/ebook/v1/login', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'password', 'device_id', 'device_label']);
        $this->authenticate();
        $this->getJson('/api/ebook/v1/books')->assertOk();
    }

    public function test_catalog_uses_existing_registration_and_protects_unregistered_books(): void
    {
        $this->authenticate('DEMO002');
        $this->getJson('/api/ebook/v1/books')->assertOk()->assertJsonCount(1, 'books')->assertJsonPath('books.0.code', 'G16');
        $this->postJson('/api/ebook/v1/books/1/open')->assertNotFound();
        $this->getJson('/api/ebook/v1/pages/1/image')->assertNotFound();
        $this->putJson('/api/ebook/v1/pages/1/work', $this->work())->assertNotFound();
    }

    public function test_duplicate_login_is_blocked_logged_and_logout_allows_a_new_session(): void
    {
        $this->authenticate();
        $data = ['code' => 'DEMO001', 'password' => 'Demo@eiMaths26', 'device_id' => (string) Str::uuid(), 'device_label' => 'Another phone'];
        $this->postJson('/api/ebook/v1/login', $data)->assertStatus(409)->assertJsonPath('code', 'session_active');
        $this->assertSame(1, DB::table('ebook_events')->where('action', 'login_blocked')->count());
        $this->postJson('/api/ebook/v1/logout')->assertOk();
        $this->postJson('/api/ebook/v1/login', $data)->assertOk();
    }

    public function test_expired_sessions_and_wrong_device_cannot_fetch_images(): void
    {
        $this->getJson('/api/ebook/v1/pages/1/image')->assertUnauthorized();
        $this->authenticate();
        $this->withHeader('X-Device-Id', (string) Str::uuid())->getJson('/api/ebook/v1/pages/1/image')->assertUnauthorized();
        $this->withHeader('X-Device-Id', $this->device);
        DB::table('ebook_sessions')->update(['expires_at' => now()->subMinute()]);
        $this->getJson('/api/ebook/v1/pages/1/image')->assertUnauthorized();
    }

    public function test_page_image_is_the_original_raster_without_a_watermark(): void
    {
        $this->authenticate();
        $response = $this->get('/api/ebook/v1/pages/1/image')->assertOk();
        $this->assertSame(file_get_contents($this->root.'/page.png'), file_get_contents($response->baseResponse->getFile()->getPathname()));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_annotations_are_private_versioned_and_retry_safe(): void
    {
        $this->authenticate();
        $work = $this->work();
        $this->putJson('/api/ebook/v1/pages/2/work', $work)->assertOk()->assertJsonPath('revision', 1);
        $this->putJson('/api/ebook/v1/pages/2/work', $work)->assertOk()->assertJsonPath('revision', 1);
        $this->assertSame(1, DB::table('ebook_events')->where('action', 'work_saved')->count());
        $this->putJson('/api/ebook/v1/pages/2/work', $this->work())->assertStatus(409);
        $this->getJson('/api/ebook/v1/pages/2/work')->assertJsonCount(1, 'document.objects');
        $this->postJson('/api/ebook/v1/logout');
        $this->authenticate('DEMO002');
        $this->getJson('/api/ebook/v1/pages/2/work')->assertJsonPath('revision', 0)->assertJsonCount(0, 'document.objects');
    }

    public function test_clip_references_must_stay_in_the_same_book_and_edition(): void
    {
        $this->authenticate();
        $work = $this->work();
        $work['document']['objects'][0]['type'] = 'clip';
        $work['document']['objects'][0]['sourcePageId'] = 2;
        $work['document']['objects'][0]['sourceRect'] = [.1, .1, .2, .2];
        $this->putJson('/api/ebook/v1/pages/1/work', $work)->assertUnprocessable();
        $work['document']['objects'][0]['sourcePageId'] = 1;
        $this->putJson('/api/ebook/v1/pages/1/work', $work)->assertOk();
    }

    public function test_log_retries_do_not_duplicate_events_and_cannot_claim_other_students_books(): void
    {
        $this->authenticate('DEMO002');
        $event = ['events' => [['event_id' => (string) Str::uuid(), 'action' => 'pen', 'book_id' => 2, 'page_id' => 2, 'client_at' => now()->toIso8601String(), 'metadata' => []]]];
        $this->postJson('/api/ebook/v1/events', $event)->assertOk();
        $this->postJson('/api/ebook/v1/events', $event)->assertOk();
        $this->assertSame(1, DB::table('ebook_events')->where('action', 'pen')->where('student_id', 2)->count());
        $event['events'][0]['book_id'] = 1;
        $event['events'][0]['event_id'] = (string) Str::uuid();
        $this->postJson('/api/ebook/v1/events', $event)->assertOk()->assertJsonPath('rejected.0.code', 'content_unavailable')->assertJsonCount(0, 'accepted');
    }

    public function test_one_invalid_event_does_not_block_valid_events_in_the_batch(): void
    {
        $this->authenticate();
        $valid = ['event_id' => (string) Str::uuid(), 'action' => 'pen', 'book_id' => 1, 'page_id' => 1,
            'client_at' => '2026-10-08T15:10:00+07:00', 'session_id' => DB::table('ebook_sessions')->value('id'), 'metadata' => []];
        $bad = $valid; $bad['event_id'] = (string) Str::uuid(); $bad['action'] = 'invented_action';
        $this->postJson('/api/ebook/v1/events', ['events' => [$bad, $valid]])->assertOk()
            ->assertJsonPath('accepted.0', $valid['event_id'])->assertJsonPath('rejected.0.index', 0);
        $row = DB::table('ebook_events')->where('event_id', $valid['event_id'])->first();
        $this->assertSame('2026-10-08 08:10:00', $row->occurred_at);
        $this->assertSame($valid['session_id'], $row->session_id);
        $this->assertNotSame($row->occurred_at, $row->received_at);
    }

    public function test_offline_events_keep_original_session_after_login_again(): void
    {
        $this->authenticate();
        $original = DB::table('ebook_sessions')->value('id');
        $this->postJson('/api/ebook/v1/logout')->assertOk();
        $this->authenticate();
        $current = DB::table('ebook_sessions')->value('id');
        $event = ['event_id' => (string) Str::uuid(), 'action' => 'pen', 'book_id' => 1, 'page_id' => 1,
            'client_at' => now()->subHour()->toIso8601String(), 'session_id' => $original, 'metadata' => []];
        $this->postJson('/api/ebook/v1/events', ['events' => [$event]])->assertOk()->assertJsonCount(1, 'accepted');
        $row = DB::table('ebook_events')->where('event_id', $event['event_id'])->first();
        $this->assertSame($original, $row->session_id);
        $this->assertSame($current, $row->received_session_id);
        $event['event_id'] = (string) Str::uuid(); $event['session_id'] = (string) Str::uuid();
        $this->postJson('/api/ebook/v1/events', ['events' => [$event]])->assertOk()->assertJsonPath('rejected.0.code', 'invalid_context');
        unset($event['session_id']); $event['event_id'] = (string) Str::uuid();
        $this->postJson('/api/ebook/v1/events', ['events' => [$event]])->assertOk()->assertJsonCount(1, 'accepted');
        $this->assertNull(DB::table('ebook_events')->where('event_id', $event['event_id'])->value('session_id'));
    }

    public function test_capture_and_focus_events_keep_their_distinct_context_and_are_retry_safe(): void
    {
        $this->authenticate();
        $session = DB::table('ebook_sessions')->value('id');
        $actions = ['reader_hidden', 'reader_resumed', 'screenshot_detected', 'capture_detected', 'capture_stopped'];
        $events = array_map(fn ($action) => [
            'event_id' => (string) Str::uuid(), 'action' => $action, 'book_id' => 1, 'page_id' => 1,
            'session_id' => $session, 'client_at' => now()->toIso8601String(),
            'metadata' => $action === 'reader_hidden' ? ['reason' => 'focus_lost'] : ['platform' => 'android'],
        ], $actions);
        $this->postJson('/api/ebook/v1/events', ['events' => $events])->assertOk()
            ->assertJsonCount(5, 'accepted')->assertJsonCount(0, 'rejected');
        $this->postJson('/api/ebook/v1/events', ['events' => $events])->assertOk()->assertJsonCount(5, 'accepted');
        foreach ($actions as $action) {
            $query = DB::table('ebook_events')->where('action', $action);
            $this->assertSame(1, $query->count());
            $row = $query->first();
            $this->assertEquals(1, $row->book_id);
            $this->assertEquals(1, $row->page_id);
            $this->assertSame($session, $row->session_id);
        }
        foreach (['en', 'th'] as $locale) {
            app()->setLocale($locale);
            $hidden = DB::table('ebook_events')->where('action', 'reader_hidden')->first();
            $this->assertSame(__('ebooks.log_details.focus_lost'), \App\Domain\Ebook\LogLabels::detail($hidden));
            $this->assertNotSame(\App\Domain\Ebook\LogLabels::action('screenshot_detected'), \App\Domain\Ebook\LogLabels::action('reader_hidden'));
        }
    }

    public function test_publishing_a_new_edition_does_not_move_existing_handwriting(): void
    {
        $this->authenticate();
        $this->putJson('/api/ebook/v1/pages/1/work', $this->work())->assertOk();
        DB::table('ebook_versions')->insert(['id' => 3, 'book_id' => 1, 'number' => 2, 'source_path' => 'v2.pdf', 'status' => 'ready', 'published_at' => now(), 'page_count' => 1]);
        DB::table('ebook_pages')->insert(['id' => 3, 'version_id' => 3, 'number' => 1, 'image_path' => 'new.png', 'width' => 1000, 'height' => 1400]);
        DB::table('ebook_books')->where('id', 1)->update(['published_version_id' => 3]);
        $this->postJson('/api/ebook/v1/books/1/open')->assertOk()->assertJsonPath('version_id', 1)->assertJsonPath('latest_version_id', 3);
    }

    public function test_local_demo_admin_is_not_available_in_the_normal_app(): void
    {
        $this->get('/ebook-demo')->assertNotFound();
        $this->get('/ebooks')->assertRedirect();
        $this->get('/ebooks/versions/1/preview')->assertRedirect();
        $this->get('/ebooks/versions/1/image')->assertRedirect();
        $this->get('/ebook-demo/versions/1/image')->assertNotFound();
    }

    public function test_real_pdf_is_converted_into_private_page_images(): void
    {
        DB::table('ebook_pages')->where('version_id', 1)->delete();
        DB::table('ebook_versions')->where('id', 1)->update(['status' => 'queued', 'published_at' => null]);
        DemoData::createPdf(1);
        app(PdfConverter::class)->convert(1);
        $this->assertSame('ready', DB::table('ebook_versions')->find(1)->status);
        $pages = DB::table('ebook_pages')->where('version_id', 1)->get();
        $this->assertCount(3, $pages);
        foreach ($pages as $page) {
            $this->assertFileExists($this->root.'/'.$page->image_path);
            $this->assertLessThanOrEqual(1600, max($page->width, $page->height));
        }
    }
}
