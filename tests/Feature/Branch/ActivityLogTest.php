<?php

namespace Tests\Feature\Branch;

use App\Http\Middleware\RecordBranchActivity;
use App\Models\{Contact, Department, Histrories, Order, Parents, Receipt, TeachingPeriod, User};
use App\Support\Activity\ActivityRecorder;
use App\Support\Branch\BranchContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\{DB, Hash, Http, Mail, Notification};
use Spatie\Permission\Models\{Permission, Role};
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private Department $hq;
    private Department $a;
    private Department $b;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests(); Mail::fake(); Notification::fake();
        $this->hq = Department::forceCreate(['id' => 1, 'name' => 'HQ', 'code' => 'HQ', 'status' => 1]);
        $this->a = Department::create(['name' => 'Branch A', 'code' => 'AA', 'status' => 1]);
        $this->b = Department::create(['name' => 'Branch B', 'code' => 'BB', 'status' => 1]);
    }

    private function actor(string $role = 'BranchAdmin', ?Department $branch = null, array $permissions = ['activity-log-list']): User
    {
        $user = User::factory()->create(['department_id' => ($branch ?? $this->a)->id, 'disable' => 0, 'language_code' => 'en', 'password' => Hash::make('activity-test-password')]);
        $user->assignRole(Role::findOrCreate($role, 'web'));
        foreach ($permissions as $permission) { $user->givePermissionTo(Permission::findOrCreate($permission, 'web')); }
        return $user;
    }

    private function record(User $actor, Department $branch, string $name = 'Audit student'): int
    {
        app(BranchContext::class)->runAs($actor, fn () => Contact::create(['centre' => $branch->id, 'name' => $name, 'code' => 'AUD'.random_int(100000, 999999)]));
        return DB::table('branch_audit_logs')->where('category', 'activity')->orderByDesc('id')->value('id');
    }

    public function test_new_branch_has_readable_empty_page_and_menu_in_both_languages(): void
    {
        $actor = $this->actor();
        $this->actingAs($actor)->get('/activity-logs')->assertOk()->assertSee('No activity matches these filters.')->assertSee('Sign-in history')->assertSee('Data changes');
        $actor->update(['language_code' => 'th']);
        $this->get('/activity-logs?category=activity')->assertOk()->assertSee('ประวัติการใช้งาน')->assertSee('ยังไม่มีประวัติตามเงื่อนไขที่เลือก')->assertDontSee('activity-log.');
    }

    public function test_permission_and_branch_module_are_enforced(): void
    {
        $actor = $this->actor('Teacher', permissions: []);
        $this->actingAs($actor)->get('/activity-logs')->assertForbidden();
        $actor->givePermissionTo('activity-log-list');
        $this->a->update(['enabled_modules' => ['students']]);
        $this->get('/activity-logs')->assertForbidden();
    }

    public function test_branch_cannot_read_other_branch_or_forge_a_filter(): void
    {
        $actor = $this->actor();
        $other = $this->actor(branch: $this->b);
        $ownId = $this->record($actor, $this->a, 'Visible own record');
        $otherId = $this->record($other, $this->b, 'Private other branch record');
        $this->actingAs($actor)->get('/activity-logs?category=activity')->assertOk()->assertViewHas('logs', fn ($logs) => $logs->total() === 1);
        $this->get('/activity-logs/'.$ownId)->assertOk()->assertSee('Visible own record');
        $this->get('/activity-logs/'.$otherId)->assertNotFound();
        $this->get('/activity-logs?branch_id='.$this->b->id)->assertForbidden();
    }

    public function test_owner_actions_are_retained_but_hidden_from_every_non_owner_viewer(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        $id = $this->record($owner, $this->a, 'Private owner change');
        $row = DB::table('branch_audit_logs')->find($id);
        $this->assertEquals($this->a->id, $row->branch_id);
        $this->assertEquals($this->hq->id, $row->actor_branch_id);
        $this->assertEquals(1, $row->actor_is_owner);
        $this->assertEquals(0, $row->subject_is_owner);

        $viewers = [
            $this->actor('Admin', $this->hq, ['activity-log-list', 'all-centre']),
            $this->actor(),
            $this->actor('Teacher'),
            $this->actor('AuditReviewer'),
            $this->actor('SystemAdmin', $this->a), // A misplaced role cannot grant HQ ownership.
        ];
        foreach ($viewers as $viewer) {
            $this->actingAs($viewer)->get('/activity-logs?category=activity')->assertOk()
                ->assertViewHas('logs', fn ($logs) => $logs->total() === 0)
                ->assertViewHas('actors', fn ($actors) => $actors->isEmpty())
                ->assertDontSee($owner->name);
            $this->get('/activity-logs/'.$id)->assertNotFound();
            $this->get('/activity-logs?category=activity&actor_id='.$owner->id)->assertOk()
                ->assertViewHas('logs', fn ($logs) => $logs->total() === 0);
        }
        $this->assertEquals($row, DB::table('branch_audit_logs')->find($id));
        $this->actingAs($owner)->get('/activity-logs/'.$id)->assertOk()->assertSee('Private owner change')
            ->assertViewHas('log', fn ($log) => (int) $log['actor_id'] === $owner->id && $log['actor'] === $owner->name);
    }

    public function test_admin_cannot_read_owner_account_events_but_owner_can(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        app(BranchContext::class)->runAs($owner, fn () => $owner->update(['name' => 'Owner secret display name']));
        $id = DB::table('branch_audit_logs')->where('category', 'activity')->value('id');
        $admin = $this->actor('Admin', $this->hq, ['activity-log-list', 'all-centre']);
        $this->actingAs($admin)->get('/activity-logs/'.$id)->assertNotFound();
        $this->actingAs($owner)->get('/activity-logs/'.$id)->assertOk();
    }

    public function test_cross_branch_access_requires_central_role_and_all_centre(): void
    {
        $source = $this->actor(branch: $this->b);
        $id = $this->record($source, $this->b);
        $admin = $this->actor('Admin', $this->hq);
        $this->actingAs($admin)->get('/activity-logs/'.$id)->assertNotFound();
        $admin->givePermissionTo(Permission::findOrCreate('all-centre', 'web'));
        $this->get('/activity-logs/'.$id)->assertOk();
        $branch = $this->actor(permissions: ['activity-log-list', 'all-centre']);
        $this->actingAs($branch)->get('/activity-logs/'.$id)->assertNotFound();
    }

    public function test_before_after_and_password_change_are_recorded_without_secrets(): void
    {
        $actor = $this->actor();
        $target = $this->actor('Teacher');
        $oldName = $target->name;
        app(BranchContext::class)->runAs($actor, fn () => $target->update(['name' => '<script>unsafe</script>', 'password' => 'secret-new-password']));
        $row = DB::table('branch_audit_logs')->where('category', 'activity')->first();
        $this->assertSame($oldName, json_decode($row->before_values, true)['name']);
        $this->assertTrue(json_decode($row->metadata, true)['password_changed']);
        $serialized = json_encode($row);
        $this->assertStringNotContainsString('secret-new-password', $serialized);
        $this->assertStringNotContainsString($target->password, $serialized);
        $this->actingAs($actor)->get('/activity-logs/'.$row->id)->assertOk()->assertSee('&lt;script&gt;unsafe&lt;/script&gt;', false)->assertDontSee('<script>unsafe</script>', false);
    }

    public function test_role_assignment_records_before_and_after(): void
    {
        $actor = $this->actor(permissions: ['activity-log-list', 'user-edit']);
        $target = $this->actor('Teacher');
        Role::findOrCreate('BranchHelper', 'web');
        $this->actingAs($actor)->putJson('/users/save/'.$target->id, [
            'name' => $target->name, 'email' => $target->email, 'department' => $this->a->id,
            'position' => null, 'role' => 'BranchHelper', 'disable' => 0,
        ])->assertOk();
        $row = DB::table('branch_audit_logs')->where('action', 'roles.changed')->first();
        $this->assertSame(['roles' => ['Teacher']], json_decode($row->before_values, true));
        $this->assertSame(['roles' => ['BranchHelper']], json_decode($row->after_values, true));
    }

    public function test_role_permission_changes_record_permission_names(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq, ['activity-log-list', 'role-edit']);
        $role = Role::findOrCreate('BranchHelper', 'web');
        $role->branch_id = $this->a->id; $role->save();
        $old = Permission::findOrCreate('student-list', 'web');
        $new = Permission::findOrCreate('student-edit', 'web');
        $role->givePermissionTo($old);
        $this->actingAs($owner)->putJson('/roles/save/'.$role->id, ['name' => 'Helper', 'permission' => [$new->id]])->assertOk();
        $row = DB::table('branch_audit_logs')->where('action', 'permissions.changed')->first();
        $this->assertSame(['permissions' => ['student-list']], json_decode($row->before_values, true));
        $this->assertSame(['permissions' => ['student-edit']], json_decode($row->after_values, true));
        $this->assertEquals($this->a->id, $row->branch_id);
    }

    public function test_actual_login_and_logout_are_recorded(): void
    {
        $actor = $this->actor();
        $this->post('/login', ['email' => $actor->email, 'password' => 'activity-test-password'])->assertRedirect();
        $this->assertDatabaseHas('branch_audit_logs', ['action' => 'login', 'actor_id' => $actor->id, 'branch_id' => $this->a->id, 'outcome' => 'success']);
        $this->post('/logout')->assertRedirect();
        $this->assertDatabaseHas('branch_audit_logs', ['action' => 'logout', 'actor_id' => $actor->id]);
    }

    public function test_bad_password_is_central_and_never_records_credentials(): void
    {
        $actor = $this->actor();
        $this->postJson('/login', ['email' => $actor->email, 'password' => 'WRONG-SECRET'])->assertUnprocessable();
        $row = DB::table('branch_audit_logs')->where('action', 'login.failed')->first();
        $this->assertNotNull($row);
        $this->assertNull($row->actor_id); $this->assertNull($row->branch_id);
        $this->assertStringNotContainsString('WRONG-SECRET', json_encode($row));
        $this->assertStringNotContainsString($actor->email, json_encode($row));
        $this->actingAs($actor)->get('/activity-logs/'.$row->id)->assertNotFound();
        $admin = $this->actor('Admin', $this->hq, ['activity-log-list', 'all-centre']);
        $this->actingAs($admin)->get('/activity-logs/'.$row->id)->assertOk()
            ->assertViewHas('log', fn ($log) => $log['actor_id'] === null);
        $this->get('/activity-logs?category=authentication')->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 1)
            ->assertViewHas('actors', fn ($actors) => $actors->isEmpty());
    }

    public function test_disabled_login_is_denied_without_success_or_extra_logout(): void
    {
        $actor = $this->actor(); $actor->update(['disable' => 1]);
        $this->post('/login', ['email' => $actor->email, 'password' => 'activity-test-password'])->assertRedirect('/login');
        $this->assertDatabaseHas('branch_audit_logs', ['action' => 'login.denied', 'actor_id' => $actor->id, 'outcome' => 'denied']);
        $this->assertDatabaseMissing('branch_audit_logs', ['action' => 'login']);
        $this->assertDatabaseMissing('branch_audit_logs', ['action' => 'logout']);
    }

    public function test_no_op_and_timestamp_updates_do_not_create_noise(): void
    {
        $actor = $this->actor();
        app(BranchContext::class)->runAs($actor, function () use ($actor) {
            $actor->save();
            $actor->forceFill(['updated_at' => now()->addHour()])->save();
        });
        $this->assertSame(0, DB::table('branch_audit_logs')->whereNotNull('category')->count());
    }

    public function test_data_and_success_log_roll_back_together_and_failure_remains(): void
    {
        $actor = $this->actor();
        $request = Request::create('/contacts/store', 'POST');
        $request->setUserResolver(fn () => $actor);
        $request->setRouteResolver(fn () => (new Route('POST', 'contacts/store', [\App\Http\Controllers\ContactController::class, 'store']))->name('contacts.store'));
        $originalRequest = $this->app['request']; $this->app->instance('request', $request);
        $request->setUserResolver(fn () => $actor);
        try {
            (new RecordBranchActivity)->handle($request, function () {
                Contact::create(['centre' => $this->a->id, 'name' => 'Must roll back']);
                throw new \RuntimeException('Never copy this exception body into the log');
            });
            $this->fail('Expected exception');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Never copy', $exception->getMessage());
        } finally { $this->app->instance('request', $originalRequest); }
        $this->assertDatabaseMissing('contacts', ['name' => 'Must roll back']);
        $this->assertDatabaseMissing('branch_audit_logs', ['action' => 'created']);
        $this->assertDatabaseHas('branch_audit_logs', ['action' => 'request.failed', 'outcome' => 'failed']);
    }

    public function test_rejected_cross_branch_mutation_logs_failure_only_in_actor_branch(): void
    {
        $actor = $this->actor(permissions: ['activity-log-list', 'department-edit']);
        $position = \App\Models\Position::create(['department_id' => $this->b->id, 'name' => 'Private target', 'status' => 1]);
        $this->actingAs($actor)->putJson('/positions/save/'.$position->id, ['name' => 'Attempt'])->assertForbidden();
        $row = DB::table('branch_audit_logs')->where('action', 'request.failed')->first();
        $this->assertNotNull($row); $this->assertEquals($this->a->id, $row->branch_id);
        $this->assertNull($row->subject_id);
        $this->assertStringNotContainsString('Private target', json_encode($row));
    }

    public function test_bulk_user_delete_records_each_target(): void
    {
        $actor = $this->actor(permissions: ['activity-log-list', 'user-delete']);
        $one = $this->actor('Teacher'); $two = $this->actor('Teacher');
        $this->actingAs($actor)->post('/users/destroy_all', ['table_records' => [$one->id, $two->id]])->assertRedirect();
        $this->assertEqualsCanonicalizing([$one->id, $two->id], DB::table('branch_audit_logs')->where('action', 'deleted')->pluck('subject_id')->all());
    }

    public function test_transfer_is_visible_to_both_branches_and_records_reason(): void
    {
        $admin = $this->actor('Admin', $this->hq, ['activity-log-list', 'all-centre']);
        $student = Contact::create(['centre' => $this->a->id, 'name' => 'Transfer student']);
        app(BranchContext::class)->runAs($admin, function () use ($student) {
            app(BranchContext::class)->allowTransfer = true;
            $student->update(['centre' => $this->b->id]);
            app(BranchContext::class)->allowTransfer = false;
        });
        $row = DB::table('branch_audit_logs')->where('action', 'branch.transferred')->first();
        $this->assertEquals($this->a->id, $row->related_branch_id);
        $a = $this->actor(); $b = $this->actor(branch: $this->b);
        $this->actingAs($a)->get('/activity-logs/'.$row->id)->assertOk();
        $this->actingAs($b)->get('/activity-logs/'.$row->id)->assertOk();
    }

    public function test_filters_pagination_and_legacy_rows_do_not_leak(): void
    {
        $actor = $this->actor();
        for ($i = 0; $i < 28; $i++) { $this->record($actor, $this->a); }
        $owner = $this->actor('SystemAdmin', $this->hq);
        for ($i = 0; $i < 30; $i++) { $this->record($owner, $this->a); }
        DB::table('branch_audit_logs')->insert(['actor_id' => $actor->id, 'branch_id' => $this->a->id, 'action' => 'legacy', 'subject_type' => 'Legacy', 'created_at' => now()]);
        $this->actingAs($actor)->get('/activity-logs?category=activity&module=students&outcome=success&action=created')->assertOk()->assertViewHas('logs', fn ($logs) => $logs->total() === 28 && $logs->count() === 25 && $logs->lastPage() === 2)
            ->assertViewHas('actors', fn ($actors) => $actors->pluck('actor_id')->all() === [$actor->id]);
        $this->get('/activity-logs?category=activity&page=2')->assertViewHas('logs', fn ($logs) => $logs->count() === 3);
        $this->get('/activity-logs?category=activity&from=2000-01-01&to=2000-01-02')->assertViewHas('logs', fn ($logs) => $logs->total() === 0);
        $this->get('/activity-logs?category=activity&to=2000-01-02')->assertOk()->assertViewHas('logs', fn ($logs) => $logs->total() === 0);
        $this->get('/activity-logs?category=activity&from=2000-01-01')->assertOk()->assertViewHas('logs', fn ($logs) => $logs->total() === 28);
        $this->getJson('/activity-logs?from=2026-10-12&to=2026-10-11')->assertUnprocessable();
    }

    public function test_excluded_controllers_do_not_generate_new_activity_rows(): void
    {
        $actor = $this->actor();
        $originalRequest = $this->app['request'];
        foreach (['CrmLeadController', 'BookShipmentController', 'IncomingBookShipmentController', 'AffiliateAuthController'] as $controller) {
            $request = Request::create('/excluded', 'POST');
            $request->setRouteResolver(fn () => new Route('POST', 'excluded', ['uses' => 'App\\Http\\Controllers\\'.$controller.'@store']));
            $this->app->instance('request', $request);
            app(BranchContext::class)->runAs($actor, fn () => $actor->update(['name' => $controller]));
        }
        $this->app->instance('request', $originalRequest);
        $this->assertSame(0, DB::table('branch_audit_logs')->whereNotNull('category')->count());
    }

    public function test_core_finance_and_teaching_changes_have_values_and_target_branch(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        $student = Contact::create(['centre' => $this->a->id, 'name' => 'Student']);
        $receipt = Receipt::create(['centre' => $this->a->id, 'cid' => $student->id, 'total_fee' => '500.00']);
        $period = TeachingPeriod::create(['department_id' => $this->a->id, 'day' => 'Monday', 'period' => '09:00-10:00']);
        app(BranchContext::class)->runAs($owner, function () use ($receipt, $period) {
            $receipt->update(['total_fee' => '750.00', 'status' => 1]);
            $period->update(['period' => '10:00-11:00']);
            $this->a->update(['enabled_modules' => ['users', 'students']]);
        });
        $finance = DB::table('branch_audit_logs')->where('category', 'activity')->where('subject_type', Receipt::class)->first();
        $this->assertEquals(500, json_decode($finance->before_values, true)['total_fee']);
        $this->assertEquals(750, json_decode($finance->after_values, true)['total_fee']);
        $this->assertEquals($this->a->id, $finance->branch_id);
        $teaching = DB::table('branch_audit_logs')->where('category', 'activity')->where('subject_type', TeachingPeriod::class)->first();
        $this->assertSame('09:00-10:00', json_decode($teaching->before_values, true)['period']);
        $this->assertDatabaseHas('branch_audit_logs', ['category' => 'activity', 'module' => 'branches', 'branch_id' => $this->a->id]);
    }

    public function test_inactive_branch_login_is_not_recorded_as_success(): void
    {
        $actor = $this->actor(); $this->a->update(['status' => 0]);
        $this->post('/login', ['email' => $actor->email, 'password' => 'activity-test-password'])->assertRedirect();
        $this->assertDatabaseHas('branch_audit_logs', ['action' => 'login.denied', 'actor_id' => $actor->id]);
        $this->assertDatabaseMissing('branch_audit_logs', ['action' => 'login']);
    }

    public function test_log_details_cannot_be_edited_or_deleted(): void
    {
        $actor = $this->actor(); $id = $this->record($actor, $this->a);
        $before = DB::table('branch_audit_logs')->find($id);
        foreach ([$actor, $this->actor('SystemAdmin', $this->hq)] as $viewer) {
            $this->actingAs($viewer)->putJson('/activity-logs/'.$id, ['action' => 'tampered'])->assertStatus(405);
            $this->deleteJson('/activity-logs/'.$id)->assertStatus(405);
        }
        $this->assertEquals($before, DB::table('branch_audit_logs')->find($id));
    }

    public function test_denied_response_rolls_back_and_retains_denied_result(): void
    {
        $actor = $this->actor();
        $request = Request::create('/contacts/store', 'POST');
        $request->setUserResolver(fn () => $actor);
        $request->setRouteResolver(fn () => new Route('POST', 'contacts/store', [\App\Http\Controllers\ContactController::class, 'store']));
        $originalRequest = $this->app['request']; $this->app->instance('request', $request);
        $request->setUserResolver(fn () => $actor);
        try {
            (new RecordBranchActivity)->handle($request, function () {
                Contact::create(['centre' => $this->a->id, 'name' => 'Rejected write']);
                return response()->json(['message' => 'denied'], 403);
            });
        } finally { $this->app->instance('request', $originalRequest); }
        $this->assertDatabaseMissing('contacts', ['name' => 'Rejected write']);
        $this->assertDatabaseHas('branch_audit_logs', ['action' => 'request.failed', 'outcome' => 'denied']);
    }

    public function test_explicit_unsuccessful_json_does_not_commit_success_entries(): void
    {
        $actor = $this->actor();
        $request = Request::create('/contacts/store', 'POST');
        $request->setRouteResolver(fn () => new Route('POST', 'contacts/store', [\App\Http\Controllers\ContactController::class, 'store']));
        $originalRequest = $this->app['request']; $this->app->instance('request', $request);
        $request->setUserResolver(fn () => $actor);
        try {
            (new RecordBranchActivity)->handle($request, function () {
                Contact::create(['centre' => $this->a->id, 'name' => 'Unsuccessful write']);
                return response()->json(['success' => false]);
            });
        } finally { $this->app->instance('request', $originalRequest); }
        $this->assertDatabaseMissing('contacts', ['name' => 'Unsuccessful write']);
        $this->assertDatabaseHas('branch_audit_logs', ['action' => 'request.failed', 'outcome' => 'failed']);
    }

    public function test_affiliate_only_login_keeps_existing_flow_without_new_staff_logs(): void
    {
        $affiliate = $this->actor('Affiliate-user', permissions: []);
        $this->post('/login', ['email' => $affiliate->email, 'password' => 'activity-test-password'])->assertRedirect(route('affiliate.waiting'));
        $this->assertSame(0, DB::table('branch_audit_logs')->whereNotNull('category')->count());
    }

    public function test_owner_teacher_reference_is_anonymized_in_details(): void
    {
        $owner = $this->actor('Teacher', $this->hq);
        $branchActor = $this->actor('Admin', $this->hq);
        $student = Contact::create(['centre' => $this->hq->id, 'name' => 'Lesson student']);
        app(BranchContext::class)->runAs($branchActor, fn () => Histrories::create([
            'centre' => $this->hq->id, 'student_id' => $student->id, 'teacher_id' => $owner->id,
            'level_id' => 1, 'term' => '1', 'bookuse' => '1', 'course_remaining' => '1',
            'date' => '2026-10-12', 'stime' => '09:00:00', 'etime' => '10:00:00',
            'start_date' => '2026-10-12', 'comment' => '', 'signature' => '',
        ]));
        $id = DB::table('branch_audit_logs')->where('category', 'activity')->orderByDesc('id')->value('id');
        $owner->syncRoles(Role::findOrCreate('SystemAdmin', 'web'));
        $this->actingAs($branchActor)->get('/activity-logs/'.$id)->assertOk()
            ->assertViewHas('log', fn ($log) => $log['after']['teacher_id'] === 'Central administrator' && (int) $log['actor_id'] === $branchActor->id);
    }

    public function test_system_admins_can_see_each_other_and_every_branch(): void
    {
        $one = $this->actor('SystemAdmin', $this->hq);
        $two = $this->actor('SystemAdmin', $this->hq);
        $a = $this->actor();
        $b = $this->actor(branch: $this->b);
        $ids = [
            $this->record($one, $this->a), $this->record($two, $this->b),
            $this->record($a, $this->a), $this->record($b, $this->b),
        ];
        foreach ([$one, $two] as $viewer) {
            $page = $this->actingAs($viewer)->get('/activity-logs?category=activity')->assertOk()
                ->assertViewHas('logs', fn ($logs) => $logs->total() === 4);
            $this->assertEqualsCanonicalizing([$one->id, $two->id, $a->id, $b->id], $page->viewData('actors')->pluck('actor_id')->all());
            foreach ($ids as $id) { $this->get('/activity-logs/'.$id)->assertOk(); }
            $this->get('/activity-logs?category=activity&actor_id='.$one->id)->assertOk()
                ->assertViewHas('logs', fn ($logs) => $logs->total() === 1 && $logs->first()['actor'] === $one->name);
        }
        $this->assertSame(4, DB::table('branch_audit_logs')->where('category', 'activity')->count());
    }

    public function test_owner_actor_snapshot_stays_private_after_demotion_or_deletion(): void
    {
        $reviewer = $this->actor('SystemAdmin', $this->hq);
        $demoted = $this->actor('SystemAdmin', $this->hq);
        $deleted = $this->actor('SystemAdmin', $this->hq);
        $ids = [$this->record($demoted, $this->a), $this->record($deleted, $this->a)];
        $demoted->syncRoles(Role::findOrCreate('Admin', 'web'));
        $deleted->delete();
        $admin = $this->actor('Admin', $this->hq, ['activity-log-list', 'all-centre']);
        $this->actingAs($admin)->get('/activity-logs?category=activity')->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 0)
            ->assertViewHas('actors', fn ($actors) => $actors->isEmpty());
        foreach ($ids as $id) { $this->get('/activity-logs/'.$id)->assertNotFound(); }
        $this->actingAs($reviewer)->get('/activity-logs?category=activity')->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 2);
        foreach ($ids as $id) { $this->get('/activity-logs/'.$id)->assertOk(); }
    }

    public function test_current_system_admin_role_hides_actor_events_from_before_promotion(): void
    {
        $promoted = $this->actor('Admin', $this->hq, ['activity-log-list', 'all-centre']);
        $id = $this->record($promoted, $this->a);
        $this->assertDatabaseHas('branch_audit_logs', ['id' => $id, 'actor_is_owner' => false]);
        $promoted->syncRoles(Role::findOrCreate('SystemAdmin', 'web'));
        $admin = $this->actor('Admin', $this->hq, ['activity-log-list', 'all-centre']);
        $this->actingAs($admin)->get('/activity-logs?category=activity')->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 0)
            ->assertViewHas('actors', fn ($actors) => $actors->isEmpty());
        $this->get('/activity-logs/'.$id)->assertNotFound();
        $this->get('/activity-logs?category=activity&actor_id='.$promoted->id)->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 0);
        $this->actingAs($promoted)->get('/activity-logs/'.$id)->assertOk();
    }

    public function test_protected_user_subject_is_hidden_even_when_actor_is_not_an_owner(): void
    {
        $reviewer = $this->actor('SystemAdmin', $this->hq);
        $admin = $this->actor('Admin', $this->hq, ['activity-log-list', 'all-centre']);
        $promoted = $this->actor('Teacher', $this->hq);
        app(BranchContext::class)->runAs($admin, fn () => $promoted->update(['name' => 'Before promotion']));
        $older = DB::table('branch_audit_logs')->where('category', 'activity')->first();
        $this->assertEquals(0, $older->actor_is_owner);
        $this->assertEquals(0, $older->subject_is_owner);
        $promoted->syncRoles(Role::findOrCreate('SystemAdmin', 'web'));

        $formerOwner = $this->actor('SystemAdmin', $this->hq);
        // A protected account event from a non-owner source must also remain private.
        app(ActivityRecorder::class)->write([
            'category' => 'activity', 'module' => 'users', 'action' => 'updated',
            'branch_id' => $this->hq->id, 'subject_type' => User::class,
            'subject_id' => $formerOwner->id, 'subject_is_owner' => true,
        ], $admin);
        $formerOwner->syncRoles(Role::findOrCreate('Teacher', 'web'));
        $ids = DB::table('branch_audit_logs')->where('category', 'activity')->pluck('id')->all();
        $this->assertCount(2, $ids);
        $this->actingAs($admin)->get('/activity-logs?category=activity')->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 0)
            ->assertViewHas('actors', fn ($actors) => $actors->isEmpty());
        foreach ($ids as $id) { $this->get('/activity-logs/'.$id)->assertNotFound(); }
        $this->actingAs($reviewer)->get('/activity-logs?category=activity')->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 2);
        foreach ($ids as $id) { $this->get('/activity-logs/'.$id)->assertOk(); }
    }

    public function test_owner_login_and_logout_are_only_visible_to_owners(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        $reviewer = $this->actor('SystemAdmin', $this->hq);
        $admin = $this->actor('Admin', $this->hq, ['activity-log-list', 'all-centre']);
        $this->post('/login', ['email' => $owner->email, 'password' => 'activity-test-password'])->assertRedirect();
        $this->post('/logout')->assertRedirect();
        $ids = DB::table('branch_audit_logs')->where('category', 'authentication')->pluck('id')->all();
        $this->assertCount(2, $ids);
        $this->actingAs($admin)->get('/activity-logs?category=authentication')->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 0)
            ->assertViewHas('actors', fn ($actors) => $actors->isEmpty());
        foreach ($ids as $id) { $this->get('/activity-logs/'.$id)->assertNotFound(); }
        $this->actingAs($reviewer)->get('/activity-logs?category=authentication')->assertOk()
            ->assertViewHas('logs', fn ($logs) => $logs->total() === 2)
            ->assertViewHas('actors', fn ($actors) => $actors->pluck('actor_id')->all() === [$owner->id]);
        foreach ($ids as $id) { $this->get('/activity-logs/'.$id)->assertOk(); }
    }

    public function test_owner_transfer_is_hidden_from_both_affected_branches(): void
    {
        $owner = $this->actor('SystemAdmin', $this->hq);
        $student = Contact::create(['centre' => $this->a->id, 'name' => 'Owner transfer']);
        app(BranchContext::class)->runAs($owner, function () use ($student) {
            app(BranchContext::class)->allowTransfer = true;
            try { $student->update(['centre' => $this->b->id]); }
            finally { app(BranchContext::class)->allowTransfer = false; }
        });
        $row = DB::table('branch_audit_logs')->where('action', 'branch.transferred')->first();
        $this->assertEquals($this->a->id, $row->related_branch_id);
        $this->assertEquals($this->b->id, $row->branch_id);
        foreach ([$this->actor(), $this->actor(branch: $this->b)] as $viewer) {
            $this->actingAs($viewer)->get('/activity-logs/'.$row->id)->assertNotFound();
            $this->get('/activity-logs?category=activity')->assertOk()
                ->assertViewHas('logs', fn ($logs) => $logs->total() === 0)
                ->assertViewHas('actors', fn ($actors) => $actors->isEmpty());
        }
        $this->actingAs($owner)->get('/activity-logs/'.$row->id)->assertOk();
        $this->assertDatabaseHas('contacts', ['id' => $student->id, 'centre' => $this->b->id]);
    }
}
