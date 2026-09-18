<?php

namespace Tests\Unit\Localization;

use App\Http\Middleware\SetAffiliateLocale;
use App\Http\Middleware\SetUiLocale;
use App\Models\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class UiLocaleMiddlewareTest extends TestCase
{
    public static function Preferences(): array
    {
        return [
            'english' => ['en', 'en'],
            'thai' => ['th', 'th'],
            'missing' => [null, 'en'],
            'unsupported' => ['ja', 'en'],
            'uppercase' => ['TH', 'en'],
            'empty' => ['', 'en'],
        ];
    }

    #[Test]
    #[DataProvider('Preferences')]
    public function ResolvesPreferenceAndRestoresPreviousLocale(
        ?string $stored_locale,
        string $expected_locale
    ): void {
        $user = new User(['language_code' => $stored_locale]);
        $request = Request::create('/home');
        $request->setUserResolver(static fn ($guard = null) => $user);
        app()->setLocale('fr');

        $response = app(SetUiLocale::class)->handle(
            $request,
            static fn (Request $request) => response(app()->getLocale()),
        );

        $this->assertSame($expected_locale, $response->getContent());
        $this->assertSame('fr', app()->getLocale());
    }

    #[Test]
    public function GuestUsesEnglishAndOnlyTheWebGuardIsConsulted(): void
    {
        $request = Request::create('/home');
        $request->setUserResolver(function ($guard = null) {
            $this->assertSame('web', $guard);

            return null;
        });

        $response = app(SetUiLocale::class)->handle(
            $request,
            static fn (Request $request) => response(app()->getLocale()),
        );

        $this->assertSame('en', $response->getContent());
    }

    #[Test]
    public function RestoresLocaleWhenDownstreamCodeThrows(): void
    {
        $request = Request::create('/home');
        $request->setUserResolver(static fn ($guard = null) => new User(['language_code' => 'th']));
        app()->setLocale('en');

        try {
            app(SetUiLocale::class)->handle($request, function () {
                $this->assertSame('th', app()->getLocale());
                throw new RuntimeException('Downstream failure');
            });
            $this->fail('The exception must propagate.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Downstream failure', $exception->getMessage());
        }

        $this->assertSame('en', app()->getLocale());
    }

    #[Test]
    public function AffiliateOverrideStillDefaultsToThaiAndBothLayersRestoreState(): void
    {
        $user = new User(['language_code' => null]);
        $request = Request::create('/affiliate');
        $request->setUserResolver(static fn ($guard = null) => $user);
        config(['affiliate.default_locale' => 'th']);
        app()->setLocale('en');

        $response = app(SetUiLocale::class)->handle($request, function (Request $request) {
            $this->assertSame('en', app()->getLocale());

            $response = app(SetAffiliateLocale::class)->handle(
                $request,
                static fn (Request $request) => response(app()->getLocale()),
            );
            $this->assertSame('en', app()->getLocale());

            return $response;
        });

        $this->assertSame('th', $response->getContent());
        $this->assertSame('en', app()->getLocale());
    }
}
