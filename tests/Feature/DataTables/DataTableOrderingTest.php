<?php

namespace Tests\Feature\DataTables;

use App\Models\{Contact, CoursePending, Department, GuestQuizzer, Histrories, LineApiSetting, Position, Quiz, TelegramSetting, User, bookuse};
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\{Permission, Role};

class DataTableOrderingTest extends DataTableTestCase
{
    public function test_roles_sort_display_names_and_full_permission_lists_and_search_permissions(): void
    {
        $roles = [];
        foreach ([['internal-z', 'Audit Alpha', ['alpha-one', 'alpha-two']], ['internal-a', 'Audit Zulu', ['alpha-one', 'zulu']], ['internal-m', 'Audit Mike', ['beta']]] as [$name, $display, $permissions]) {
            $role = Role::create(['name' => $name, 'display_name' => $display, 'guard_name' => 'web']);
            foreach ($permissions as $permission) {
                $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
            }
            $roles[] = $role->id;
        }
        $filter = ['search' => ['value' => 'Audit ', 'regex' => false]];
        $this->assertOrder('roles', 'name', [$roles[0], $roles[2], $roles[1]], $filter);
        $this->assertOrder('roles', 'role', $roles, $filter);
        $this->assertSame([$roles[0]], array_column($this->search('roles', 'alpha-two')['data'], 'id'));
        $this->assertSame([$roles[0]], array_column($this->search('roles', 'alpha-two', 'role')['data'], 'id'));
        $first = $this->sort('roles', 'name', 'asc', $filter + ['length' => 1]);
        $second = $this->sort('roles', 'name', 'asc', $filter + ['length' => 1, 'start' => 1]);
        $this->assertSame($roles[0], $first['data'][0]['id']);
        $this->assertSame($roles[2], $second['data'][0]['id']);
    }

    public function test_branch_role_search_and_sort_keep_owner_and_other_branch_roles_hidden(): void
    {
        $a = Department::create(['name' => 'A', 'code' => 'A', 'status' => 1]);
        $b = Department::create(['name' => 'B', 'code' => 'B', 'status' => 1]);
        $own = Role::create(['name' => 'branch-a-role', 'display_name' => 'Local role', 'branch_id' => $a->id, 'guard_name' => 'web']);
        $other = Role::create(['name' => 'branch-b-role', 'display_name' => 'Hidden role', 'branch_id' => $b->id, 'guard_name' => 'web']);
        $actor = User::factory()->create(['password' => bcrypt('datatable-test-password'), 'department_id' => $a->id, 'disable' => 0]);
        $actor->assignRole(Role::findOrCreate('BranchAdmin', 'web'));
        $actor->givePermissionTo(Permission::findOrCreate('role-list', 'web'));
        $this->actingAs($actor);
        $ids = array_column($this->sort('roles', 'role', 'desc')['data'], 'id');
        $this->assertContains($own->id, $ids);
        $this->assertNotContains($other->id, $ids);
        $this->assertNotContains(Role::findByName('SystemAdmin', 'web')->id, $ids);
        $this->assertSame([], $this->search('roles', 'SystemAdmin')['data']);
        $this->assertSame([], $this->search('roles', 'Hidden role')['data']);
    }

    public function test_centre_department_and_guest_ordering_is_not_overridden_by_id(): void
    {
        $z = Department::create(['name' => 'Zulu', 'code' => 'Z', 'status' => 1]);
        $a = Department::create(['name' => 'Alpha', 'code' => 'A', 'status' => 1]);
        $this->assertOrder('centres', 'name', [$a->id, $this->hq->id, $z->id]);
        $p1 = Position::create(['name' => 'Alpha', 'department_id' => $z->id, 'status' => 1]);
        $p2 = Position::create(['name' => 'Zulu', 'department_id' => $a->id, 'status' => 1]);
        $this->assertOrder('departments', 'name', [$p1->id, $p2->id]);
        $this->assertOrder('departments', 'dname', [$p2->id, $p1->id]);
        foreach (['Alpha', 'Zulu', 'Mike'] as $name) {
            $guests[] = GuestQuizzer::create(['first_name' => $name, 'last_name' => 'AuditGuest', 'grade' => 'P1', 'email' => strtolower($name).'@example.test', 'phone' => '0800000000', 'password' => bcrypt('test-password'), 'expires_at' => now()->addDay()])->id;
        }
        $this->assertOrder('guest-report', 'name', [$guests[0], $guests[2], $guests[1]]);
        $this->assertSame(3, $this->search('guest-report', 'AuditGuest')['recordsFiltered']);
    }

    public function test_history_sorts_and_searches_student_centre_book_and_time_values(): void
    {
        $z = Department::create(['name' => 'Zulu', 'code' => 'Z', 'status' => 1]);
        $a = Department::create(['name' => 'Alpha', 'code' => 'A', 'status' => 1]);
        $bookA = bookuse::forceCreate(['name' => 'Alpha book']);
        $bookZ = bookuse::forceCreate(['name' => 'Zulu book']);
        $studentA = Contact::create(['name' => 'Alpha student', 'nickname' => 'SpecialNick', 'code' => 'Z-001', 'centre' => $z->id]);
        $studentZ = Contact::create(['name' => 'Zulu student', 'code' => 'A-001', 'centre' => $a->id]);
        $h1 = Histrories::create(['teacher_id' => $this->owner->id, 'level_id' => 1, 'term' => '1', 'course_remaining' => '9', 'start_date' => '2026-10-01', 'comment' => '', 'signature' => '', 'centre' => $z->id, 'student_id' => $studentA->id, 'bookuse' => $bookA->id, 'stime' => '11:00', 'etime' => '12:00', 'date' => '2026-10-15']);
        $h2 = Histrories::create(['teacher_id' => $this->owner->id, 'level_id' => 1, 'term' => '1', 'course_remaining' => '9', 'start_date' => '2026-10-01', 'comment' => '', 'signature' => '', 'centre' => $a->id, 'student_id' => $studentZ->id, 'bookuse' => $bookZ->id, 'stime' => '09:00', 'etime' => '10:00', 'date' => '2026-10-15']);
        $this->assertOrder('histories', 'name', [$h1->id, $h2->id]);
        foreach (['code', 'centre', 'setime'] as $column) {
            $this->assertOrder('histories', $column, [$h2->id, $h1->id]);
        }
        $this->assertOrder('histories', 'bookuse', [$h1->id, $h2->id]);
        foreach ([['name', 'SpecialNick', $h1->id], ['centre', 'Zulu', $h1->id], ['bookuse', 'Zulu book', $h2->id], ['setime', '09:00', $h2->id]] as [$column, $search, $expected]) {
            $this->assertSame([$expected], array_column($this->search('histories', $search, $column)['data'], 'id'));
        }
        $this->assertStringContainsString('(SpecialNick)', $this->search('histories', 'SpecialNick', 'name')['data'][0]['name']);
        $legacy = $h1->replicate();
        $legacy->bookuse = 'Middle legacy';
        $legacy->save();
        $this->assertOrder('histories', 'bookuse', [$h1->id, $legacy->id, $h2->id]);
        $this->assertSame([$legacy->id], array_column($this->search('histories', 'Middle legacy', 'bookuse')['data'], 'id'));
        $branchActor = User::factory()->create(['password' => bcrypt('datatable-test-password'), 'department_id' => $a->id, 'disable' => 0]);
        $branchActor->assignRole(Role::findOrCreate('BranchAdmin', 'web'));
        $branchActor->givePermissionTo(Permission::findOrCreate('history-list', 'web'));
        $this->actingAs($branchActor);
        $this->assertSame([$h2->id], array_column($this->sort('histories', 'name', 'asc')['data'], 'id'));
        $this->assertSame([], $this->search('histories', 'SpecialNick')['data']);
    }

    public function test_course_applications_sort_centre_code_phone_and_parent_type(): void
    {
        $a = Department::create(['name' => 'Alpha', 'code' => 'A', 'status' => 1]);
        $z = Department::create(['name' => 'Zulu', 'code' => 'Z', 'status' => 1]);
        $one = CoursePending::create(['name' => 'Alpha', 'course_id' => 1, 'department_id' => $z->id, 'telp' => '0999999999', 'type_parent' => 'Zulu', 'status' => 0]);
        $two = CoursePending::create(['name' => 'Zulu', 'course_id' => 1, 'department_id' => $a->id, 'telp' => '0111111111', 'type_parent' => 'Alpha', 'status' => 0]);
        $this->assertOrder('course-applications', 'name', [$one->id, $two->id]);
        foreach (['department', 'telp', 'type_parent'] as $column) {
            $this->assertOrder('course-applications', $column, [$two->id, $one->id]);
        }
        $this->assertSame([$two->id], array_column($this->search('course-applications', '0111111111')['data'], 'id'));
    }

    public function test_quiz_free_flag_orders_and_filters_and_question_count_search_is_not_ignored(): void
    {
        $paid = Quiz::create(['level' => 'K1', 'term' => '1', 'title' => 'Paid', 'slug' => 'paid', 'is_free' => 0]);
        $free = Quiz::create(['level' => 'K1', 'term' => '1', 'title' => 'Free', 'slug' => 'free', 'is_free' => 1]);
        $this->assertOrder('quizzes', 'is_free', [$paid->id, $free->id]);
        $this->assertSame([$free->id], array_column($this->search('quizzes', '1', 'is_free')['data'], 'id'));
        $this->assertSame(2, $this->search('quizzes', '0', 'questions_count')['recordsFiltered']);
        $this->assertSame([], $this->search('quizzes', '99', 'questions_count')['data']);
    }

    public function test_integration_tables_sort_names_and_authors_and_number_paginated_rows(): void
    {
        $alpha = User::factory()->create(['password' => bcrypt('datatable-test-password'), 'name' => 'Alpha author', 'department_id' => 1, 'disable' => 0]);
        $zulu = User::factory()->create(['password' => bcrypt('datatable-test-password'), 'name' => 'Zulu author', 'department_id' => 1, 'disable' => 0]);
        $a = LineApiSetting::forceCreate(['name' => 'Alpha', 'create_by' => $zulu->id, 'update_by' => $alpha->id]);
        $z = LineApiSetting::forceCreate(['name' => 'Zulu', 'create_by' => $alpha->id, 'update_by' => $zulu->id]);
        $this->assertOrder('line-settings', 'name', [$a->id, $z->id]);
        $this->assertOrder('line-settings', 'create_by', [$z->id, $a->id]);
        $this->assertOrder('line-settings', 'update_by', [$a->id, $z->id]);
        $this->assertSame([$z->id], array_column($this->search('line-settings', 'Alpha author', 'create_by')['data'], 'id'));
        $this->assertSame(2, $this->sort('line-settings', 'name', 'asc', ['start' => 1, 'length' => 1])['data'][0]['DT_RowIndex']);
        $ta = TelegramSetting::create(['description' => 'Alpha', 'bot_token' => 'test-only', 'chat_id' => '1', 'status' => 0]);
        $tz = TelegramSetting::create(['description' => 'Zulu', 'bot_token' => 'test-only', 'chat_id' => '2', 'status' => 0]);
        $this->assertOrder('telegram-settings', 'description', [$ta->id, $tz->id]);
        $this->assertSame(2, $this->sort('telegram-settings', 'description', 'asc', ['start' => 1, 'length' => 1])['data'][0]['DT_RowIndex']);
    }
}
