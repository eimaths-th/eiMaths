<?php

namespace Tests\Unit\Localization;

use App\Http\Middleware\HandleCreditNoteResponses;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CreditNoteResponseLocalizationTest extends TestCase
{
    #[Test]
    public function validation_response_uses_the_current_credit_note_locale(): void
    {
        $previous_locale = app()->getLocale();
        app()->setLocale('th');

        try {
            $request = Request::create('/credit-notes', 'POST', [], [], [], [
                'HTTP_ACCEPT' => 'application/json',
            ]);
            $response = (new HandleCreditNoteResponses())->handle(
                $request,
                static function (): Response {
                    throw ValidationException::withMessages(['field' => ['invalid']]);
                },
            );

            $this->assertSame(422, $response->getStatusCode());
            $this->assertSame('ข้อมูลใบลดหนี้ไม่ถูกต้อง', $response->getData(true)['message']);
        } finally {
            app()->setLocale($previous_locale);
        }
    }
}
