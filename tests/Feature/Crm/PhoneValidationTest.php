<?php

namespace Tests\Feature\Crm;

use App\Services\CrmLeadNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PhoneValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.crm_integrations.token' => 'isolated-phone-token']);
        Http::preventStrayRequests();
        Mail::fake();
        $this->app->instance(CrmLeadNotificationService::class, $this->createMock(CrmLeadNotificationService::class));
    }

    private function Register(string $phone): TestResponse
    {
        return $this->postJson('/api/n8n/marketing/leads', [
            'parent_name' => 'Phone Test Parent',
            'phone' => $phone,
            'student_name' => 'Phone Test Child',
            'student_grade' => 'P1',
        ], ['Authorization' => 'Bearer isolated-phone-token']);
    }

    public static function MalformedPhones(): array
    {
        return [
            ['++++++++'],
            ['1234+5678'],
            ['+1234567'],
        ];
    }

    #[Test]
    #[DataProvider('MalformedPhones')]
    public function RejectsMalformedPlusAndTooFewDigits(string $phone): void
    {
        $this->Register($phone)->assertUnprocessable()->assertJsonValidationErrors(['phone']);
        $this->assertDatabaseCount('crm_leads', 0);
    }

    #[Test]
    public function AcceptsALeadingPlusWithFifteenDigits(): void
    {
        $this->Register('+123456789012345')->assertCreated()
            ->assertJsonPath('lead.phone', '+123456789012345');
        $this->assertDatabaseHas('crm_leads', ['phone' => '+123456789012345']);
    }
}
