<?php

namespace Tests\Feature\Contact;

use App\Models\Contact;
use App\Models\Department;
use App\Models\User;
use App\Models\bookuse;
use App\Models\level;
use App\Models\term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentCodeIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        Http::preventStrayRequests();
        Mail::fake();
        Notification::fake();
        Department::forceCreate(['id' => 2, 'name' => 'Branch', 'code' => 'BR', 'status' => 1]);
        Department::forceCreate(['id' => 3, 'name' => 'Other branch', 'code' => 'OTHER', 'status' => 1]);
    }

    #[Test]
    public function DuplicateStudentCodeCannotAuthenticateAnArbitrarilySelectedStudent(): void
    {
        $this->Student('First student', 'DUPLICATE', 'first-password');
        $this->Student('Second student', 'DUPLICATE', 'second-password');

        $this->from('/students-login')->post('/students-login-check', [
            'code' => 'DUPLICATE',
            'password' => 'first-password',
        ])->assertRedirect('/students-login')->assertSessionHasErrors('error');
        $this->assertGuest('student');
    }

    #[Test]
    public function UniqueStudentCodeStillAuthenticatesOnlyWithTheCorrectPassword(): void
    {
        $student = $this->Student('Unique student', 'BR0001', 'correct-password');
        $this->from('/students-login')->post('/students-login-check', [
            'code' => 'BR0001', 'password' => 'incorrect-password',
        ])->assertSessionHasErrors('error');
        $this->assertGuest('student');

        $this->post('/students-login-check', [
            'code' => 'BR0001', 'password' => 'correct-password',
        ])->assertRedirect();
        $this->assertAuthenticatedAs($student, 'student');
    }

    public static function DuplicateCodeBranches(): array
    {
        return ['same-branch' => [2], 'other-branch' => [3]];
    }

    #[Test]
    #[DataProvider('DuplicateCodeBranches')]
    public function StudentCodeCannotBeChangedToAnotherStudentsCode(int $existing_branch_id): void
    {
        $student = $this->Student('Student to edit', 'BR0001', 'first-password');
        $this->Student('Existing student', 'BR0002', 'second-password', $existing_branch_id);
        $response = $this->putJson('/contacts/save/'.$student->id, $this->UpdatePayload($student, 'BR0002'));

        $response->assertOk()->assertJsonStructure(['errors']);
        $this->assertDatabaseHas('contacts', ['id' => $student->id, 'code' => 'BR0001']);
    }

    #[Test]
    public function StudentCanChangeToAnAvailableCode(): void
    {
        $student = $this->Student('Student to edit', 'BR0001', 'first-password');
        $this->putJson('/contacts/save/'.$student->id, $this->UpdatePayload($student, 'BR0003'))
            ->assertOk()->assertJsonStructure(['success']);
        $this->assertDatabaseHas('contacts', ['id' => $student->id, 'code' => 'BR0003']);
    }

    #[Test]
    public function ExistingDuplicateCodesCanBeKeptWhileEditingOtherStudentFields(): void
    {
        $student = $this->Student('Student to edit', 'DUPLICATE', 'first-password');
        $this->Student('Existing student', 'DUPLICATE', 'second-password');
        $payload = $this->UpdatePayload($student, 'DUPLICATE');
        $payload['name'] = 'Updated student name';
        $this->putJson('/contacts/save/'.$student->id, $payload)
            ->assertOk()->assertJsonStructure(['success']);
        $this->assertDatabaseHas('contacts', ['id' => $student->id, 'code' => 'DUPLICATE', 'name' => 'Updated student name']);
    }

    private function UpdatePayload(Contact $student, string $submitted_code): array
    {
        $actor = User::factory()->create(['department_id' => 2, 'disable' => 0, 'password' => bcrypt('staff-password')]);
        $actor->assignRole(Role::findOrCreate('BranchAdmin', 'web'));
        $actor->givePermissionTo(Permission::findOrCreate('student-edit', 'web'));
        $level = level::create(['name' => 'K1', 'price' => 100, 'book_price' => 20, 'status' => 1]);
        $term = term::create(['level_id' => $level->id, 'name' => '1', 'status' => 1]);
        $book = bookuse::forceCreate(['level_id' => $level->id, 'term_id' => $term->id, 'type' => 1, 'name' => 'Book', 'status' => 1]);

        $this->actingAs($actor);

        return [
            'name' => $student->name,
            'centre' => 2,
            'code' => $submitted_code,
            'start_date' => '2026-10-06',
            'school' => 'Test school',
            'level' => $level->id,
            'term' => $term->id,
            'bookuse' => $book->id,
            'free_course' => 0,
            'gender' => 1,
            'discontinued' => 0,
        ];
    }

    private function Student(string $name, string $code, string $password, int $branch_id = 2): Contact
    {
        return Contact::create([
            'centre' => $branch_id,
            'name' => $name,
            'code' => $code,
            'type' => 'student',
            'password' => bcrypt($password),
        ]);
    }
}
