<?php

namespace Tests\Feature\Guest;

use App\Models\Department;
use App\Models\GuestQuizzer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GuestMarketingConsentReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Department::forceCreate(['id' => Department::HQ_ID, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        Department::forceCreate(['id' => 2, 'name' => 'Branch', 'code' => 'BR', 'status' => 1]);
    }

    private function CreateActor(array $permission_names, bool $is_central = true): User
    {
        $actor = User::factory()->create([
            'department_id' => $is_central ? Department::HQ_ID : 2,
            'language_code' => 'en',
            'disable' => 0,
            'password' => Hash::make('test-password'),
        ]);
        $actor->assignRole(Role::findOrCreate('Admin', 'web'));
        foreach ($permission_names as $permission_name) {
            $actor->givePermissionTo(Permission::findOrCreate($permission_name, 'web'));
        }

        return $actor;
    }

    private function CreateGuest(?bool $marketing_consent, string $email = 'guest@example.com'): GuestQuizzer
    {
        return GuestQuizzer::create([
            'first_name' => 'Guest',
            'last_name' => 'Tester',
            'grade' => 'P1',
            'email' => $email,
            'phone' => '0812345678',
            'password' => Hash::make('0812345678'),
            'expires_at' => now()->addDays(10),
            'marketing_consent' => $marketing_consent,
            'marketing_consent_recorded_at' => $marketing_consent === null ? null : now()->subDay(),
        ]);
    }

    private function ReadReportRow(User $actor): array
    {
        return $this->actingAs($actor)
            ->getJson(route('guest.report').'?draw=1&start=0&length=10', ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->json('data.0');
    }

    public static function ConsentLabels(): array
    {
        return [
            'consented' => [true, 'marketing_consented', true],
            'declined' => [false, 'marketing_not_consented', false],
            'existing record' => [null, 'marketing_not_recorded', false],
        ];
    }

    #[Test]
    #[DataProvider('ConsentLabels')]
    public function ShowsTextLabelAndWithdrawButtonOnlyForConsent(?bool $marketing_consent, string $label_key, bool $is_withdrawable): void
    {
        $this->CreateGuest($marketing_consent);

        $report_row = $this->ReadReportRow($this->CreateActor(['student-list', 'student-edit']));

        $this->assertSame(__("students.guest_reports.{$label_key}"), $report_row['marketing_status']);
        $this->assertSame($is_withdrawable, str_contains($report_row['action'], 'btn-withdraw-marketing'));
    }

    #[Test]
    public function HidesTheWithdrawButtonWithoutEditPermission(): void
    {
        $this->CreateGuest(true);

        $report_row = $this->ReadReportRow($this->CreateActor(['student-list']));

        $this->assertStringNotContainsString('btn-withdraw-marketing', $report_row['action']);
    }

    #[Test]
    public function RecordsAWithdrawalAndLogsOnlyIdentifiers(): void
    {
        $guest = $this->CreateGuest(true);
        $actor = $this->CreateActor(['student-edit']);
        Log::spy();

        $this->actingAs($actor)
            ->postJson(route('guest.report.marketing.withdraw'), ['id' => $guest->id])
            ->assertOk()
            ->assertJson(['success' => true]);

        $withdrawn_guest = $guest->fresh();
        $this->assertFalse($withdrawn_guest->marketing_consent);
        $this->assertTrue($withdrawn_guest->marketing_consent_recorded_at->isToday());
        Log::shouldHaveReceived('info')->once()->with('MARKETING_CONSENT_WITHDRAWN', [
            'actor_id' => $actor->id,
            'guest_id' => $guest->id,
        ]);
    }

    #[Test]
    public function RepeatingAWithdrawalChangesNothing(): void
    {
        $guest = $this->CreateGuest(false);
        $original_recorded_at = $guest->fresh()->marketing_consent_recorded_at;

        $this->actingAs($this->CreateActor(['student-edit']))
            ->postJson(route('guest.report.marketing.withdraw'), ['id' => $guest->id])
            ->assertOk();

        $this->assertEquals($original_recorded_at, $guest->fresh()->marketing_consent_recorded_at);
    }

    public static function RefusedActors(): array
    {
        return [
            'central without edit permission' => [['student-list'], true],
            'branch actor with edit permission' => [['student-edit'], false],
        ];
    }

    #[Test]
    #[DataProvider('RefusedActors')]
    public function RefusesActorsWithoutAccess(array $permission_names, bool $is_central): void
    {
        $guest = $this->CreateGuest(true);

        $this->actingAs($this->CreateActor($permission_names, $is_central))
            ->postJson(route('guest.report.marketing.withdraw'), ['id' => $guest->id])
            ->assertForbidden();

        $this->assertTrue($guest->fresh()->marketing_consent);
    }

    #[Test]
    public function RejectsAnUnknownGuest(): void
    {
        $this->actingAs($this->CreateActor(['student-edit']))
            ->postJson(route('guest.report.marketing.withdraw'), ['id' => 999])
            ->assertUnprocessable();
    }
}
