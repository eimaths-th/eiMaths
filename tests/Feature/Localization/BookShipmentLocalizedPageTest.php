<?php

namespace Tests\Feature\Localization;

use App\Models\Department;
use App\Models\BookShipment;
use App\Notifications\BookShipmentNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookShipmentLocalizedPageTest extends TestCase
{
    use RefreshDatabase;

    private Department $hq;
    private Department $centre;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hq = Department::create([
            'id' => Department::HQ_ID,
            'name' => 'Headquarters',
            'code' => 'HQ',
            'status' => 1,
            'branch_type' => 'hq',
        ]);
        $this->centre = Department::create([
            'name' => 'Ratchaphruek',
            'code' => 'RAT',
            'status' => 1,
        ]);
    }

    public function test_hq_book_shipment_page_uses_the_users_locale(): void
    {
        $english_response = $this->actingAs($this->Actor('en', $this->hq, ['book-shipment-list']))
            ->get(route('book-shipments.index'));

        $english_response
            ->assertOk()
            ->assertSee('Book Shipment Centre')
            ->assertSee('Book shipment ledger')
            ->assertDontSee('ศูนย์จัดส่งหนังสือ');

        $thai_response = $this->actingAs($this->Actor('th', $this->hq, ['book-shipment-list']))
            ->get(route('book-shipments.index'));

        $thai_response
            ->assertOk()
            ->assertSee('ศูนย์จัดส่งหนังสือ')
            ->assertSee('รายการจัดส่งหนังสือ')
            ->assertDontSee('Book Shipment Centre');
    }

    public function test_centre_book_shipment_page_uses_the_users_locale(): void
    {
        $english_response = $this->actingAs($this->Actor('en', $this->centre, ['book-shipment-receive']))
            ->get(route('incoming-book-shipments.index'));

        $english_response
            ->assertOk()
            ->assertSee('Incoming book shipments')
            ->assertSee('Shipments awaiting action')
            ->assertDontSee('ศูนย์รับหนังสือ');

        $thai_response = $this->actingAs($this->Actor('th', $this->centre, ['book-shipment-receive']))
            ->get(route('incoming-book-shipments.index'));

        $thai_response
            ->assertOk()
            ->assertSee('ศูนย์รับหนังสือ')
            ->assertSee('รายการหนังสือที่กำลังจัดส่งเข้า')
            ->assertDontSee('Incoming book shipments');
    }

    public function test_book_shipment_notifications_use_the_recipient_locale(): void
    {
        $shipment = new BookShipment(['shipment_number' => 'BS-202609-000001']);
        $notification = new BookShipmentNotification($shipment, BookShipmentNotification::EVENT_DISPATCHED);

        $english_payload = $notification->toDatabase($this->Actor('en', $this->centre, []));
        $thai_payload = $notification->toDatabase($this->Actor('th', $this->centre, []));

        $this->assertSame('Books dispatched', $english_payload['title']);
        $this->assertSame('เปิดรายการจัดส่ง', $thai_payload['actionText']);
        $this->assertSame('Open shipment', $english_payload['actionText']);
        $this->assertSame('จัดส่งหนังสือแล้ว', $thai_payload['title']);
    }

    private function Actor(string $language_code, Department $department, array $permissions): User
    {
        $actor = User::factory()->create([
            'department_id' => $department->id,
            'disable' => 0,
            'language_code' => $language_code,
            'password' => bcrypt('password-123'),
        ]);

        foreach ($permissions as $permission) {
            $actor->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        if ($department->id === Department::HQ_ID) {
            $actor->assignRole(Role::findOrCreate('Admin', 'web'));
            $actor->givePermissionTo(Permission::findOrCreate('all-centre', 'web'));
        }

        return $actor;
    }
}
