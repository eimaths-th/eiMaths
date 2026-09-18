<?php

namespace Tests\Feature\Localization;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UiLocalePreferenceTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Department::forceCreate([
            'id' => 2,
            'name' => 'Locale Test Branch',
            'code' => 'LOCALE',
            'status' => 1,
            'enabled_modules' => null,
        ]);
        $this->actor = User::factory()->create([
            'department_id' => $branch->id,
            'disable' => 0,
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
    }

    public static function SupportedLocales(): array
    {
        return [['th'], ['en']];
    }

    public static function InvalidLocales(): array
    {
        return [['ja'], ['TH'], [''], [null], [['th']], [42]];
    }

    #[Test]
    #[DataProvider('SupportedLocales')]
    public function PersistsOnlyTheCurrentUsersPreferenceAndReturnsToTheSamePage(string $locale): void
    {
        $other_user = User::factory()->create([
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);
        $return_url = route('contacts', ['renewal_status' => 'pending']);

        $this->actingAs($this->actor, 'web')->from($return_url)
            ->post('/settings/locale', ['locale' => $locale])
            ->assertRedirect($return_url);

        $this->assertSame($locale, $this->actor->fresh()->language_code);
        $this->assertSame('en', $other_user->fresh()->language_code);

        $this->actingAs($this->actor->fresh(), 'web')->get(route('home'))
            ->assertOk()->assertSee('<html lang="' . $locale . '">', false);
    }

    #[Test]
    #[DataProvider('InvalidLocales')]
    public function RejectsUnsupportedPreferencesWithoutWriting(mixed $locale): void
    {
        $this->actingAs($this->actor, 'web')->postJson('/settings/locale', [
            'locale' => $locale,
        ])->assertUnprocessable()->assertJsonValidationErrors('locale');

        $this->assertSame('en', $this->actor->fresh()->language_code);
    }

    #[Test]
    public function RejectsAnotherUserIdentifier(): void
    {
        $other_user = User::factory()->create([
            'language_code' => 'en',
            'password' => Hash::make('secret'),
        ]);

        $this->actingAs($this->actor, 'web')->postJson('/settings/locale', [
            'locale' => 'th',
            'user_id' => $other_user->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('user_id');

        $this->assertSame('en', $this->actor->fresh()->language_code);
        $this->assertSame('en', $other_user->fresh()->language_code);
    }

    #[Test]
    public function RequiresWebAuthentication(): void
    {
        $this->postJson('/settings/locale', ['locale' => 'th'])->assertUnauthorized();
        $this->assertSame('en', $this->actor->fresh()->language_code);
    }

    #[Test]
    public function DisabledAccountsRemainForbidden(): void
    {
        $this->actor->update(['disable' => 1]);

        $this->actingAs($this->actor, 'web')->postJson('/settings/locale', [
            'locale' => 'th',
        ])->assertForbidden();

        $this->assertSame('en', $this->actor->fresh()->language_code);
    }

    #[Test]
    public function ExternalReturnUrlsFallBackToHome(): void
    {
        $this->actingAs($this->actor, 'web')->from('https://outside.example.test/path')
            ->post('/settings/locale', ['locale' => 'th'])
            ->assertRedirect(route('home'));
    }

    #[Test]
    public function MissingReturnUrlFallsBackToHome(): void
    {
        $this->actingAs($this->actor, 'web')
            ->post('/settings/locale', ['locale' => 'th'])
            ->assertRedirect(route('home'));
    }

    #[Test]
    public function RequiresCsrfOutsideTheFrameworksTestBypass(): void
    {
        $this->app->bind(VerifyCsrfToken::class, function ($application) {
            return new class($application, $application['encrypter']) extends VerifyCsrfToken
            {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });

        $this->actingAs($this->actor, 'web')
            ->post('/settings/locale', ['locale' => 'th'])
            ->assertStatus(419);

        $this->assertSame('en', $this->actor->fresh()->language_code);
    }
}
