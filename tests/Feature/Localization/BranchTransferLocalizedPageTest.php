<?php

namespace Tests\Feature\Localization;

use App\Models\Contact;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BranchTransferLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private Department $source_branch;

    private Department $destination_branch;

    protected function setUp(): void
    {
        parent::setUp();

        $headquarters = Department::forceCreate([
            'id' => Department::HQ_ID,
            'name' => 'Transfer HQ',
            'code' => 'TRANSFER-HQ',
            'status' => 1,
            'branch_type' => 'hq',
            'enabled_modules' => null,
        ]);
        $this->source_branch = Department::create([
            'name' => 'Source Branch',
            'code' => 'SOURCE',
            'status' => 1,
            'branch_type' => 'branch',
            'enabled_modules' => null,
        ]);
        $this->destination_branch = Department::create([
            'name' => 'Destination Branch',
            'code' => 'DESTINATION',
            'status' => 1,
            'branch_type' => 'branch',
            'enabled_modules' => null,
        ]);

        $role = Role::findOrCreate('SystemAdmin', 'web');
        $role->givePermissionTo(Permission::findOrCreate('branch-transfer', 'web'));
        $this->actor = User::factory()->create([
            'department_id' => $headquarters->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $this->actor->assignRole($role);
    }

    public static function Locales(): array
    {
        return [
            [
                'en',
                'Transfer Branch',
                'Move a user or student while keeping existing documents and history with the issuing branch.',
                ['Record type', 'Student', 'User', 'Search by student name or code', 'Destination branch', 'Reason', 'Confirm branch transfer'],
                'Branch transfer completed. Existing documents and history remain with the issuing branch.',
            ],
            [
                'th',
                'ย้ายสาขา',
                'ย้ายผู้ใช้หรือนักเรียน โดยเก็บเอกสารและประวัติเดิมไว้ที่สาขาผู้ออก',
                ['ประเภทข้อมูล', 'นักเรียน', 'ผู้ใช้', 'ค้นหาชื่อนักเรียนหรือรหัส', 'สาขาปลายทาง', 'เหตุผล', 'ยืนยันย้ายสาขา'],
                'ย้ายสาขาแล้ว เอกสารและประวัติเดิมยังอยู่กับสาขาผู้ออก',
            ],
        ];
    }

    #[Test]
    #[DataProvider('Locales')]
    public function BranchTransferPageRendersTheSelectedLocale(
        string $locale,
        string $title,
        string $description,
        array $labels,
        string $expected_message,
    ): void {
        $this->actor->update(['language_code' => $locale]);

        $response = $this->actingAs($this->actor, 'web')->get(route('branch-transfers.index'));

        $response->assertOk()
            ->assertSee('<html lang="' . $locale . '">', false)
            ->assertSee($title)
            ->assertSee($description)
            ->assertDontSee('centre-management.transfers.');

        foreach ($labels as $label) {
            $response->assertSee($label);
        }
    }

    #[Test]
    #[DataProvider('Locales')]
    public function BranchTransferSuccessUsesTheSelectedLocale(
        string $locale,
        string $title,
        string $description,
        array $labels,
        string $expected_message,
    ): void {
        $this->actor->update(['language_code' => $locale]);
        $student = Contact::create([
            'centre' => $this->source_branch->id,
            'name' => 'Transfer Student',
        ]);

        $response = $this->actingAs($this->actor, 'web')->post(route('branch-transfers.store'), [
            'type' => 'student',
            'record_id' => $student->id,
            'to_branch_id' => $this->destination_branch->id,
            'reason' => 'Family moved to another district',
        ]);

        $response->assertRedirect()
            ->assertSessionHas('success', $expected_message);
        $this->assertSame($this->destination_branch->id, (int) $student->fresh()->centre);
    }
}
