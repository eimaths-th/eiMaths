<?php

namespace Tests\Unit\CreditNote;

use App\Domain\CreditNote\Enums\CreditNoteReasonCode;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditNoteTranslationStructureTest extends TestCase
{
    #[Test]
    public function english_and_thai_credit_note_translation_trees_have_matching_leaf_keys_and_reason_codes(): void
    {
        $english = require base_path('lang/en/credit-note.php');
        $thai = require base_path('lang/th/credit-note.php');
        $this->assertSame($this->Leaves($english), $this->Leaves($thai));
        foreach (CreditNoteReasonCode::cases() as $reason) {
            $this->assertArrayHasKey($reason->value, $english['reasons']);
            $this->assertArrayHasKey($reason->value, $thai['reasons']);
        }
        foreach (['CREDIT_NOTE_DISABLED', 'CREDIT_NOTE_OVER_CREDIT', 'CREDIT_NOTE_INTERNAL_ERROR'] as $error_code) {
            $this->assertArrayHasKey($error_code, $english['errors']['codes']);
            $this->assertArrayHasKey($error_code, $thai['errors']['codes']);
        }
    }

    private function Leaves(array $tree, string $prefix = ''): array
    {
        $leaves = [];
        foreach ($tree as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
            if (is_array($value)) {
                $leaves = array_merge($leaves, $this->Leaves($value, $path));
            } else {
                $leaves[] = $path;
            }
        }
        sort($leaves);
        return $leaves;
    }
}
