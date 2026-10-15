<?php

namespace Tests\Feature\Branch;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfilePageTest extends TestCase
{
    use RefreshDatabase;

    private Department $hq;
    private Department $branch;
    private Position $position;

    protected function beforeRefreshingDatabase(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        $this->hq = Department::forceCreate(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        $this->branch = Department::create(['name' => 'Profile test centre', 'code' => 'PRF', 'status' => 1]);
        $this->position = Position::create(['department_id' => $this->branch->id, 'name' => 'Profile test department', 'status' => 1]);
    }

    public function test_guest_cannot_view_update_or_delete_a_profile(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->patch('/profile', ['name' => 'Guest', 'email' => 'guest@example.test'])->assertRedirect('/login');
        $this->delete('/profile', ['password' => 'profile-test-password'])->assertRedirect('/login');
        $this->assertDatabaseCount('users', 0);
    }

    /** @dataProvider supportedLanguages */
    public function test_staff_without_user_management_access_can_open_own_localized_profile(string $locale, string $title): void
    {
        $actor = $this->actor(locale: $locale);
        $this->branch->update(['enabled_modules' => ['students']]);
        $this->assertFalse($actor->can('user-edit'));
        $response = $this->actingAs($actor)->get('/profile')->assertOk()
            ->assertViewIs('profile.edit')
            ->assertViewHas('user', fn (User $user) => $user->is($actor))
            ->assertSee($title)
            ->assertSee($actor->name)
            ->assertSee($actor->email)
            ->assertSee($this->branch->name)
            ->assertSee($this->position->name)
            ->assertSee('BranchAdmin')
            ->assertDontSee($actor->password, false);

        $document = new \DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(1, $xpath->query('//nav//a[@href="'.route('profile.edit').'"]')->length,
            'The topbar Profile button must lead to the real profile page.');
        foreach (['department_id', 'position_id', 'role', 'roles', 'disable', 'password', 'password_confirmation'] as $field) {
            $this->assertSame(0, $xpath->query('//form//*[self::input or self::select or self::textarea][@name="'.$field.'"][not(@disabled) and not(@readonly)]')->length,
                'Self-service profile must not expose editable account authority or password-change controls.');
        }
    }

    public static function supportedLanguages(): array
    {
        return ['English' => ['en', 'My profile'], 'Thai' => ['th', 'โปรไฟล์ของฉัน']];
    }

    public function test_name_update_preserves_email_verification_and_records_own_change(): void
    {
        $actor = $this->actor();
        $before = $actor->fresh()->getAttributes();
        $this->actingAs($actor)->patch('/profile', ['name' => 'Updated profile name', 'email' => $actor->email])
            ->assertRedirect(route('profile.edit'))->assertSessionHas('status', 'profile-updated')->assertSessionHasNoErrors();

        $updated = $actor->fresh();
        $this->assertSame('Updated profile name', $updated->name);
        foreach (['email', 'email_verified_at', 'password', 'department_id', 'position_id', 'disable'] as $attribute) {
            $this->assertSame($before[$attribute], $updated->getRawOriginal($attribute), $attribute.' must be preserved.');
        }
        $this->assertSame(['BranchAdmin'], $updated->getRoleNames()->all());
        $log = DB::table('branch_audit_logs')->where('subject_type', User::class)->where('subject_id', $actor->id)->where('action', 'updated')->sole();
        $this->assertSame($actor->id, (int) $log->actor_id);
        $this->assertSame($this->branch->id, (int) $log->branch_id);
        $this->assertSame(['name' => $before['name']], json_decode($log->before_values, true));
        $this->assertSame(['name' => 'Updated profile name'], json_decode($log->after_values, true));
    }

    /** @dataProvider rejectedPasswords */
    public function test_email_change_needs_current_password_and_cannot_partially_save_or_flash_it(?string $password): void
    {
        $actor = $this->actor();
        $before = $actor->fresh()->getAttributes();
        $payload = ['name' => 'Must not be saved', 'email' => 'changed-profile@example.test'];
        if ($password !== null) {
            $payload['current_password'] = $password;
        }
        $this->actingAs($actor)->from('/profile')->patch('/profile', $payload)
            ->assertRedirect('/profile')->assertSessionHasErrors('current_password')
            ->assertSessionMissing('_old_input.current_password');

        $this->assertSame($before, $actor->fresh()->getAttributes());
        $this->assertDatabaseMissing('branch_audit_logs', ['subject_type' => User::class, 'subject_id' => $actor->id, 'action' => 'updated']);
        if ($password !== null) {
            $this->assertStringNotContainsString($password, DB::table('branch_audit_logs')->get()->toJson());
        }
    }

    public static function rejectedPasswords(): array
    {
        return ['missing' => [null], 'incorrect' => ['incorrect-profile-password']];
    }

    public function test_email_change_with_current_password_resets_verification_without_changing_password(): void
    {
        $actor = $this->actor();
        $passwordHash = $actor->fresh()->password;
        $this->actingAs($actor)->patch('/profile', [
            'name' => 'Changed name', 'email' => 'new-profile-address@example.test', 'current_password' => 'profile-test-password',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

        $updated = $actor->fresh();
        $this->assertSame('new-profile-address@example.test', $updated->email);
        $this->assertSame('Changed name', $updated->name);
        $this->assertNull($updated->email_verified_at);
        $this->assertSame($passwordHash, $updated->password);
        $this->assertTrue(Hash::check('profile-test-password', $updated->password));
        $this->assertArrayNotHasKey('current_password', $updated->getAttributes());
        $this->assertAuthenticatedAs($actor);
        $this->assertStringNotContainsString('profile-test-password', DB::table('branch_audit_logs')->get()->toJson());
    }

    public function test_duplicate_email_in_another_branch_is_rejected_without_partial_update(): void
    {
        $actor = $this->actor();
        $other = $this->actor(branch: $this->hq);
        $before = $actor->fresh()->getAttributes();
        $otherBefore = $other->fresh()->getAttributes();
        $this->actingAs($actor)->patchJson('/profile', [
            'name' => 'Must not save', 'email' => $other->email, 'current_password' => 'profile-test-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertSame($before, $actor->fresh()->getAttributes());
        $this->assertSame($otherBefore, $other->fresh()->getAttributes());
    }

    public function test_invalid_profile_fields_do_not_save_partial_changes(): void
    {
        $actor = $this->actor();
        $before = $actor->fresh()->getAttributes();
        $this->actingAs($actor)->patchJson('/profile', [
            'name' => '', 'email' => 'not-an-email', 'current_password' => 'profile-test-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'email']);
        $this->assertSame($before, $actor->fresh()->getAttributes());
    }

    public function test_payload_cannot_change_account_authority_password_or_another_users_profile(): void
    {
        $actor = $this->actor();
        $other = $this->actor('SystemAdmin', $this->hq);
        $before = $actor->fresh()->getAttributes();
        $otherBefore = $other->fresh()->getAttributes();
        $foreignPosition = Position::create(['department_id' => $this->hq->id, 'name' => 'Owner department', 'status' => 1]);
        $this->actingAs($actor)->patch('/profile', [
            'name' => 'Allowed own display name', 'email' => $actor->email,
            'id' => $other->id, 'user_id' => $other->id, 'department_id' => $this->hq->id,
            'position_id' => $foreignPosition->id, 'roles' => ['SystemAdmin'], 'role' => 'SystemAdmin',
            'permissions' => ['all-centre'], 'disable' => 1, 'email_verified_at' => null,
            'password' => 'attacker-replacement-password', 'password_confirmation' => 'attacker-replacement-password',
        ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

        $updated = $actor->fresh();
        $this->assertSame('Allowed own display name', $updated->name);
        foreach (['id', 'email', 'email_verified_at', 'department_id', 'position_id', 'password', 'disable'] as $attribute) {
            $this->assertSame($before[$attribute], $updated->getRawOriginal($attribute), $attribute.' cannot be assigned by profile input.');
        }
        $this->assertSame(['BranchAdmin'], $updated->getRoleNames()->all());
        $this->assertFalse($updated->can('all-centre'));
        $this->assertSame($otherBefore, $other->fresh()->getAttributes());
        $this->assertSame(['SystemAdmin'], $other->fresh()->getRoleNames()->all());
        $this->assertDatabaseMissing('branch_audit_logs', ['subject_id' => $other->id, 'subject_type' => User::class, 'action' => 'updated']);
    }

    public function test_system_admin_can_update_profile_and_its_log_remains_hidden_from_admin(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        $this->actingAs($owner)->get('/profile')->assertOk();
        $this->patch('/profile', ['name' => 'Owner profile update', 'email' => $owner->email])
            ->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();
        $log = DB::table('branch_audit_logs')->where('subject_type', User::class)->where('subject_id', $owner->id)->where('action', 'updated')->sole();
        $this->assertSame(1, (int) $log->actor_is_owner);
        $this->assertSame(1, (int) $log->subject_is_owner);
        $this->get('/activity-logs/'.$log->id)->assertOk();

        $admin = $this->actor('Admin', $this->hq);
        $admin->givePermissionTo([
            Permission::findOrCreate('activity-log-list', 'web'), Permission::findOrCreate('all-centre', 'web'),
        ]);
        $this->actingAs($admin)->get('/activity-logs/'.$log->id)->assertNotFound();
    }

    /** @dataProvider disabledContexts */
    public function test_disabled_account_or_branch_cannot_view_or_update_profile(bool $disableAccount): void
    {
        $actor = $this->actor();
        if ($disableAccount) {
            $actor->update(['disable' => 1]);
        } else {
            $this->branch->update(['status' => 0]);
        }
        $before = $actor->fresh()->getAttributes();
        $this->actingAs($actor)->get('/profile')->assertForbidden();
        $this->patchJson('/profile', ['name' => 'Blocked update', 'email' => $actor->email])->assertForbidden();
        $this->assertSame($before, $actor->fresh()->getAttributes());
    }

    public static function disabledContexts(): array
    {
        return ['disabled user' => [true], 'archived branch' => [false]];
    }

    public function test_existing_profile_delete_route_cannot_remove_last_system_admin(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        $before = $owner->fresh()->getAttributes();
        $this->actingAs($owner)->deleteJson('/profile', ['password' => 'profile-test-password'])->assertUnprocessable();
        $this->assertSame($before, $owner->fresh()->getAttributes());
        $this->assertSame(['SystemAdmin'], $owner->fresh()->getRoleNames()->all());
        $this->assertAuthenticatedAs($owner);
    }

    private function actor(string $role = 'BranchAdmin', ?Department $branch = null, string $locale = 'en'): User
    {
        $branch ??= $this->branch;
        $user = User::factory()->create([
            'name' => 'Profile staff', 'department_id' => $branch->id,
            'position_id' => $branch->is($this->branch) ? $this->position->id : null,
            'disable' => 0, 'language_code' => $locale, 'password' => bcrypt('profile-test-password'),
            'email_verified_at' => now()->subDay(),
        ]);
        $user->assignRole(Role::findOrCreate($role, 'web'));
        return $user;
    }
}
