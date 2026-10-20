<?php
namespace Tests\Feature;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
final class FaqReadApiTest extends TestCase
{
    use RefreshDatabase;
    #[Test] public function ValidIntegrationRequestRecordsThroughThePublicEndpoint(): void
    {
        config(['faq-analytics.integration_token' => 'test-only-faq-token', 'cache.default' => 'array']);
        $category = FaqCategory::create(['name' => 'General', 'slug' => 'new-api-read', 'is_published' => true]);
        $faq = Faq::create(['category_id' => $category->id, 'question' => 'Question', 'answer' => 'Answer', 'is_published' => true]);
        $this->withToken('test-only-faq-token')->postJson('/api/faq-reads', ['faq_id' => $faq->id, 'event_key' => str_repeat('a', 64)])->assertNoContent();
        $this->assertDatabaseHas('faq_daily_reads', ['faq_reference_id' => $faq->id, 'read_count' => 1]);
    }
    #[Test] public function IntegrationTokenAndIndependentBudgetProtectWrites(): void
    {
        config(['faq-analytics.integration_token' => 'test-only-faq-token',
            'faq-analytics.integration_requests_per_minute' => 100]);
        config(['cache.default' => 'array']);
        Cache::store('array')->flush();
        $category = FaqCategory::create(['name' => 'General', 'slug' => 'api-reads', 'is_published' => true]);
        $faq = Faq::create(['category_id' => $category->id, 'question' => 'Question', 'answer' => 'Answer', 'is_published' => true]);
        $this->postJson('/api/faq-reads', ['faq_id' => $faq->id, 'event_key' => str_repeat('a', 64)])->assertUnauthorized();
        $this->assertDatabaseCount('faq_read_receipts', 0);
        $this->withToken('test-only-faq-token');
        for ($request_number = 1; $request_number <= 61; $request_number++) {
            $this->postJson('/api/faq-reads', ['faq_id' => $faq->id,
                'event_key' => str_pad(dechex($request_number), 64, '0', STR_PAD_LEFT)])->assertNoContent();
        }
        $this->assertDatabaseHas('faq_daily_reads', ['faq_reference_id' => $faq->id, 'read_count' => 61]);
        $this->postJson('/api/faq-reads', ['faq_id' => $faq->id,
            'event_key' => str_pad('1', 64, '0', STR_PAD_LEFT)])->assertNoContent();
        $this->assertDatabaseCount('faq_read_receipts', 61);
    }
    private function Payload(): array
    {
        config(['faq-analytics.integration_token' => 'test-only-faq-token', 'cache.default' => 'array',
            'faq-analytics.integration_requests_per_minute' => 200, 'faq-analytics.invalid_requests_per_minute' => 100]);
        $category = FaqCategory::create(['name' => 'General', 'slug' => 'payload-fixture', 'is_published' => true]);
        $faq = Faq::create(['category_id' => $category->id, 'question' => 'Question', 'answer' => 'Answer', 'is_published' => true]);
        $this->withToken('test-only-faq-token');
        return ['faq_id' => $faq->id, 'event_key' => str_repeat('a', 64)];
    }
    #[Test] public function StrictJsonTypesKeysAndWhitespaceAreRejected(): void
    {
        $payload = $this->Payload();
        $invalid_payloads = [];
        foreach ([0, -1, '1', true, null, []] as $faq_id) $invalid_payloads[] = array_replace($payload, ['faq_id' => $faq_id]);
        foreach ([' '.str_repeat('a', 64).' ', str_repeat('A', 64), str_repeat('a', 65), str_repeat('a', 63), null, 123] as $event_key) $invalid_payloads[] = array_replace($payload, ['event_key' => $event_key]);
        $invalid_payloads[] = $payload + ['read_count' => 5];
        $invalid_payloads[] = ['faq_id' => $payload['faq_id']];
        foreach ($invalid_payloads as $invalid_payload) $this->postJson('/api/faq-reads', $invalid_payload)->assertUnprocessable();
        foreach (['[]', '{}', 'null', '{broken', '{"faq_id":1.0,"event_key":"'.str_repeat('a', 64).'"}', '{"faq_id":1e0,"event_key":"'.str_repeat('a', 64).'"}'] as $body) {
            $this->call('POST', '/api/faq-reads', [], [], [], [
                'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer test-only-faq-token',
            ], $body)->assertUnprocessable();
        }
        $this->assertDatabaseCount('faq_read_receipts', 0);
        $this->assertDatabaseCount('faq_daily_reads', 0);
    }
    #[Test] public function InvalidCredentialBudgetDoesNotConsumeTheIntegrationBudget(): void
    {
        $payload = $this->Payload();
        config(['faq-analytics.invalid_requests_per_minute' => 2]);
        $this->withToken('wrong-token');
        $this->postJson('/api/faq-reads', $payload)->assertUnauthorized();
        $this->postJson('/api/faq-reads', $payload)->assertUnauthorized();
        $this->postJson('/api/faq-reads', $payload)->assertStatus(429)->assertHeader('Retry-After');
        $this->withToken('test-only-faq-token')->postJson('/api/faq-reads', $payload)->assertNoContent();
        config(['faq-analytics.integration_token' => '', 'faq-analytics.invalid_requests_per_minute' => 100]);
        $this->postJson('/api/faq-reads', $payload)->assertStatus(503);
    }
    #[Test] public function IntegrationBudgetIsBoundedAndTheExistingApiBudgetRemains(): void
    {
        $payload = $this->Payload();
        config(['faq-analytics.integration_requests_per_minute' => 2]);
        $this->postJson('/api/faq-reads', $payload)->assertNoContent();
        $this->postJson('/api/faq-reads', array_replace($payload, ['event_key' => str_repeat('b', 64)]))->assertNoContent();
        $this->postJson('/api/faq-reads', array_replace($payload, ['event_key' => str_repeat('c', 64)]))->assertStatus(429)->assertHeader('Retry-After');
        $this->assertDatabaseCount('faq_read_receipts', 2);
        for ($request_number = 1; $request_number <= 60; $request_number++) $this->getJson('/api/faqs')->assertOk();
        $this->getJson('/api/faqs')->assertStatus(429);
    }
    #[Test] public function PublicationConflictAndDatabaseFailureKeepSafeStatusesAndLogs(): void
    {
        $payload = $this->Payload();
        $this->postJson('/api/faq-reads', $payload)->assertNoContent();
        $faq = Faq::findOrFail($payload['faq_id']);
        $other_faq = Faq::create(['category_id' => $faq->category_id, 'question' => 'Other', 'answer' => 'Answer', 'is_published' => true]);
        $this->postJson('/api/faq-reads', array_replace($payload, ['faq_id' => $other_faq->id]))->assertStatus(409);
        $this->postJson('/api/faq-reads', array_replace($payload, ['faq_id' => 999999]))->assertNotFound();
        $faq->update(['is_published' => false]);
        $this->postJson('/api/faq-reads', $payload)->assertNotFound();
        $faq->update(['is_published' => true]);
        $faq->category->update(['is_published' => false]);
        $this->postJson('/api/faq-reads', $payload)->assertNotFound();
        $faq->category->update(['is_published' => true]);
        \Illuminate\Support\Facades\Log::spy();
        \Illuminate\Support\Facades\DB::unprepared("CREATE TRIGGER faq_read_api_test_failure BEFORE UPDATE OF read_count ON faq_daily_reads BEGIN SELECT RAISE(ABORT, 'test-only database failure'); END");
        try {
            $this->withHeader('X-Request-Id', '7D8D8589-6709-409B-AEAE-0EA2AF8CBA18')
                ->postJson('/api/faq-reads', array_replace($payload, ['event_key' => str_repeat('b', 64)]))
                ->assertStatus(503)->assertJsonPath('error_code', 'FAQ_READ_UNAVAILABLE');
            \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->once()->withArgs(function ($message, $context): bool {
                return $message === 'faq.read.failed' && count($context) === 5
                    && $context['correlation_id'] === '7d8d8589-6709-409b-aeae-0ea2af8cba18'
                    && array_diff(array_keys($context), ['operation', 'faq_id', 'outcome', 'error_code', 'correlation_id']) === [];
            });
        } finally {
            \Illuminate\Support\Facades\DB::unprepared('DROP TRIGGER faq_read_api_test_failure');
        }
        $this->assertDatabaseCount('faq_read_receipts', 1);
        $this->assertSame(1, (int) \Illuminate\Support\Facades\DB::table('faq_daily_reads')->sum('read_count'));
    }
    #[Test] public function AdmissionStorageFailureIsUnavailableInsteadOfAnUnhandledError(): void
    {
        $payload = $this->Payload();
        \Illuminate\Support\Facades\RateLimiter::shouldReceive('hit')->once()->andThrow(new \RuntimeException('test-only quota storage failure'));
        $this->postJson('/api/faq-reads', $payload)->assertStatus(503)->assertJsonPath('error_code', 'FAQ_READ_UNAVAILABLE');
        $this->assertDatabaseCount('faq_read_receipts', 0);
    }
}
