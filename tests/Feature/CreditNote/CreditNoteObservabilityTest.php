<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Services\CreditNoteObservabilityService;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteObservabilityTest extends TestCase
{
    #[Test]
    public function structured_signals_remove_buyer_and_secret_context(): void
    {
        Log::spy();
        app(CreditNoteObservabilityService::class)->Record('issue', 'SUCCESS', 'corr-observe', [
            'credit_note_id' => 12,
            'buyer_name' => 'Sensitive buyer',
            'buyer_tax_id' => '0100000000000',
            'token' => 'secret-token',
        ]);
        Log::shouldHaveReceived('info')->withArgs(function (string $message, array $context): bool {
            return $message === 'credit_note.issue'
                && ($context['credit_note_id'] ?? null) === 12
                && !array_key_exists('buyer_name', $context)
                && !array_key_exists('token', $context);
        });
    }
}
