<?php

namespace Tests\Feature\Contact;

use App\Models\Contact;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudentLoginEventPruneTest extends TestCase
{
    use RefreshDatabase;

    private Contact $student;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-07 03:00:00');
        Department::forceCreate(['id' => 2, 'name' => 'Branch', 'code' => 'BR', 'status' => 1]);
        $this->student = Contact::create([
            'centre' => 2, 'name' => 'Student', 'code' => 'BR0001',
            'type' => 'student', 'password' => bcrypt('correct-password'),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function DeletesOnlyEventsOlderThanTheRetentionPeriod(): void
    {
        $kept_at_boundary = $this->CreateEvent(now()->subDays(365));
        $kept_recent = $this->CreateEvent(now()->subDay());
        $this->CreateEvent(now()->subDays(365)->subSecond());

        $this->artisan('student-activity:prune-login-events')->assertExitCode(0);

        $this->assertEqualsCanonicalizing(
            [$kept_at_boundary, $kept_recent],
            DB::table('student_login_events')->pluck('id')->all()
        );
    }

    #[Test]
    public function DeletesEveryExpiredEventAcrossBatches(): void
    {
        foreach (range(1, 1001) as $unused_index) {
            $this->CreateEvent(now()->subDays(400));
        }

        $this->artisan('student-activity:prune-login-events')->assertExitCode(0);

        $this->assertDatabaseCount('student_login_events', 0);
    }

    #[Test]
    public function RunsDailyAtBangkokTime(): void
    {
        $events = collect(app(Schedule::class)->events())->filter(
            fn ($event): bool => str_contains((string) $event->command, 'student-activity:prune-login-events'));

        $this->assertCount(1, $events);
        $this->assertSame('45 2 * * *', $events->first()->expression);
        $this->assertSame('Asia/Bangkok', $events->first()->timezone);
    }

    #[Test]
    public function ReportsFailureWithoutPersonalData(): void
    {
        Schema::drop('student_login_events');
        Log::spy();

        $this->artisan('student-activity:prune-login-events')->assertExitCode(1);

        Log::shouldHaveReceived('error')->once()->with('student_activity.login_events_prune_failed', [
            'error_code' => 'STUDENT_LOGIN_EVENT_PRUNE_FAILED',
        ]);
    }

    private function CreateEvent(Carbon $occurred_at): int
    {
        return DB::table('student_login_events')->insertGetId([
            'contact_id' => $this->student->id, 'event_type' => 'LOGIN', 'occurred_at' => $occurred_at,
        ]);
    }
}
