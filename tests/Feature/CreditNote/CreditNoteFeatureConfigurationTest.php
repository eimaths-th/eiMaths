<?php

namespace Tests\Feature\CreditNote;

use App\Domain\CreditNote\Exceptions\CreditNoteDomainException;
use App\Domain\CreditNote\Services\CreditNoteFeatureGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteFeatureConfigurationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function CreditNoteIsDisabledAndIssuerValuesAreBlankByDefault(): void
    {
        Config::set([
            'financial-documents.credit_note.enabled' => false,
            'financial-documents.credit_note.financial_actions_enabled' => false,
            'financial-documents.credit_note.issuer.legal_name' => '',
            'financial-documents.credit_note.issuer.legal_address' => '',
            'financial-documents.credit_note.issuer.tax_id' => '',
        ]);

        $this->assertFalse((bool) config('financial-documents.credit_note.enabled', false));
        $this->assertFalse((bool) config('financial-documents.credit_note.financial_actions_enabled', false));
        $this->assertSame('', (string) config('financial-documents.credit_note.issuer.legal_name', ''));
        $this->assertSame('', (string) config('financial-documents.credit_note.issuer.legal_address', ''));
        $this->assertSame('', (string) config('financial-documents.credit_note.issuer.tax_id', ''));
        $this->assertSame('head_office', config('financial-documents.credit_note.issuer.branch_type'));
        $this->assertSame('00000', config('financial-documents.credit_note.issuer.branch_code'));
    }

    #[Test]
    public function FeatureGateFailsClosedUntilEnabled(): void
    {
        Config::set('financial-documents.credit_note.enabled', false);
        Config::set('financial-documents.credit_note.financial_actions_enabled', false);

        $feature_gate = app(CreditNoteFeatureGate::class);

        $this->assertFalse($feature_gate->IsEnabled());
        $this->assertFalse($feature_gate->AreFinancialActionsEnabled());

        $this->expectException(CreditNoteDomainException::class);
        $this->expectExceptionCode(0);
        $feature_gate->AssertEnabled();
    }

    #[Test]
    public function FinancialActionsRemainBlockedWhenModuleIsEnabledButFinancialFlagIsOff(): void
    {
        Config::set('financial-documents.credit_note.enabled', true);
        Config::set('financial-documents.credit_note.financial_actions_enabled', false);

        $feature_gate = app(CreditNoteFeatureGate::class);

        $this->assertTrue($feature_gate->IsEnabled());
        $this->assertFalse($feature_gate->AreFinancialActionsEnabled());

        $feature_gate->AssertEnabled();
        $this->expectException(CreditNoteDomainException::class);
        $feature_gate->AssertFinancialActionsEnabled();
    }
}
