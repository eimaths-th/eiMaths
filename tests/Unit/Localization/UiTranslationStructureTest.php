<?php

namespace Tests\Unit\Localization;

use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UiTranslationStructureTest extends TestCase
{
    public static function Catalogs(): array
    {
        return [['common'], ['navigation'], ['dashboard'], ['students'], ['crm'], ['course'], ['course-website'], ['quiz-management'], ['teaching_period'], ['receipts'], ['products'], ['book-shipments'], ['qrcode-payment'], ['term-fee'], ['parameter-settings'], ['centre-management'], ['user-access'], ['system-settings'], ['content-management'], ['affiliate-management'], ['feedback-management']];
    }

    #[Test]
    #[DataProvider('Catalogs')]
    public function LocalesHaveMatchingNonEmptyLeavesAndPlaceholders(string $catalog): void
    {
        $english_path = lang_path('en/' . $catalog . '.php');
        $thai_path = lang_path('th/' . $catalog . '.php');
        $this->assertFileExists($english_path);
        $this->assertFileExists($thai_path);
        $english = Arr::dot(require $english_path);
        $thai = Arr::dot(require $thai_path);
        ksort($english);
        ksort($thai);
        $this->assertNotEmpty($english);
        $this->assertSame(array_keys($english), array_keys($thai));

        foreach ($english as $key => $english_text) {
            $this->assertTrue(is_string($english_text) || is_int($english_text), $key);
            $this->assertSame(gettype($english_text), gettype($thai[$key]), $key);
            if (is_string($english_text)) {
                $this->assertSame($english_text === '', $thai[$key] === '', $key);
                if ($english_text === '') {
                    continue;
                }
                $this->assertNotSame('', trim($english_text), $key);
                $this->assertNotSame('', trim($thai[$key]), $key);
                $this->assertSame(
                    $this->Placeholders($english_text),
                    $this->Placeholders($thai[$key]),
                    $catalog . '.' . $key,
                );
            }
        }
    }

    private function Placeholders(string $text): array
    {
        preg_match_all('/:[a-zA-Z_][a-zA-Z0-9_]*|_[A-Z]+_/', $text, $matches);
        $placeholders = array_values(array_unique($matches[0]));
        sort($placeholders);

        return $placeholders;
    }
}
