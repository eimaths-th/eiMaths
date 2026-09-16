<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Domain\Affiliate\Services\AffiliateTutorialService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateTutorialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \App\Models\Department::firstOrCreate(['id' => 1], ['name' => 'Tutorial test branch', 'code' => 'TOUR', 'status' => 1]);
        Config::set('affiliate.features.wallet_withdrawals', true);
        Config::set('affiliate.features.media_library', false);
        Config::set('affiliate.features.analytics', false);
    }

    #[Test]
    public function tutorial_launcher_is_only_available_on_the_guide_page(): void
    {
        $this->actingAs($this->Affiliate());
        $this->get('/affiliate/dashboard')->assertOk()
            ->assertSee('"starterSteps":[', false)
            ->assertSee('"autoStart":true', false)
            ->assertDontSee('data-affiliate-tutorial-start', false);
        $this->get('/affiliate/payout-accounts')->assertOk()
            ->assertSee('"autoStart":false', false)
            ->assertDontSee('data-affiliate-tutorial-start', false);
        $this->get('/affiliate/guide')->assertOk()
            ->assertSee('data-affiliate-tutorial-start', false)
            ->assertSee('data-affiliate-tutorial-mode="full"', false);
        $this->assertDatabaseCount('affiliate_tutorial_progress', 0);
    }

    #[Test]
    public function progress_survives_navigation_and_completion_can_be_replayed(): void
    {
        $user = $this->Affiliate();
        $this->actingAs($user);
        $this->Save('start', 'welcome')->assertOk()->assertJsonPath('status', 'in_progress');
        $this->Save('advance', 'bank')->assertOk();
        $this->get('/affiliate/dashboard')->assertOk()->assertSee('"step":"bank"', false)->assertSee('"autoStart":false', false);
        $this->Save('finish', 'bank')->assertStatus(422);
        $this->Save('advance', 'done')->assertOk();
        $this->Save('finish', 'done')->assertOk()->assertJsonPath('status', 'completed');
        $this->get('/affiliate/dashboard')->assertOk()->assertSee('"autoStart":false', false);
        $this->Save('advance', 'link')->assertStatus(409);
        $this->Save('start', 'welcome')->assertOk()->assertJsonPath('step', 'welcome');
        $this->assertDatabaseCount('affiliate_tutorial_progress', 1);
        // Exploring the tour must not create financial records or payout accounts.
        $this->assertDatabaseCount('affiliate_payout_accounts', 0);
        $this->assertDatabaseCount('affiliate_withdrawal_requests', 0);
    }

    #[Test]
    public function skipping_suppresses_auto_start_only_for_the_signed_in_owner(): void
    {
        $owner = $this->Affiliate();
        $other = $this->Affiliate();
        $this->actingAs($owner)->postJson('/affiliate/tutorial/progress', [
            'version' => AffiliateTutorialService::VERSION, 'action' => 'skip', 'step' => 'welcome',
            'affiliate_profile_id' => $other->AffiliateProfile->id, 'user_id' => $other->id,
        ])->assertOk();
        $this->assertDatabaseHas('affiliate_tutorial_progress', ['affiliate_profile_id' => $owner->AffiliateProfile->id, 'status' => 'skipped']);
        $this->get('/affiliate/dashboard')->assertSee('"autoStart":false', false);
        $this->actingAs($other)->get('/affiliate/dashboard')->assertSee('"autoStart":true', false);
    }

    #[Test]
    public function unapproved_and_unauthenticated_users_cannot_write_progress(): void
    {
        $this->Save('start', 'welcome')->assertUnauthorized();
        foreach ([AffiliateProfileStatus::PENDING_EMAIL, AffiliateProfileStatus::PENDING_IDENTITY, AffiliateProfileStatus::PENDING_APPROVAL, AffiliateProfileStatus::SUSPENDED] as $status) {
            $this->actingAs($this->Affiliate($status));
            $this->Save('start', 'welcome')->assertForbidden();
        }
        $this->assertDatabaseCount('affiliate_tutorial_progress', 0);
    }

    #[Test]
    public function disabled_features_and_invalid_steps_cannot_be_requested(): void
    {
        $this->actingAs($this->Affiliate());
        $service = app(AffiliateTutorialService::class);
        $keys = array_column($service->Steps(), 'key');
        $this->assertNotContains('media', $keys);
        $this->assertNotContains('dates', $keys);
        $this->Save('advance', 'not-a-step')->assertUnprocessable();
        $this->Save('advance', 'media')->assertUnprocessable();
        Config::set('affiliate.features.media_library', true);
        Config::set('affiliate.features.analytics', true);
        $this->assertContains('media', array_column($service->Steps(), 'key'));
        $this->assertContains('dates', array_column($service->Steps(), 'key'));
        Config::set('affiliate.features.wallet_withdrawals', false);
        $this->Save('start', 'welcome')->assertStatus(503);
    }

    #[Test]
    public function thai_and_english_cover_every_tutorial_step_and_label(): void
    {
        $thai = require base_path('lang/th/affiliate_tutorial.php');
        $english = require base_path('lang/en/affiliate_tutorial.php');
        $this->assertSame(array_keys($thai['labels']), array_keys($english['labels']));
        $this->assertSame(array_keys($thai['starter_steps']), array_keys($english['starter_steps']));
        $this->assertSame(array_keys($thai['steps']), array_keys($english['steps']));
        foreach ($thai['starter_steps'] as $key => $step) {
            $this->assertSame(array_keys($step), array_keys($english['starter_steps'][$key]));
        }
        foreach ($thai['steps'] as $key => $step) {
            $this->assertSame(array_keys($step), array_keys($english['steps'][$key]));
        }
    }

    #[Test]
    public function paused_progress_does_not_auto_open_and_can_resume_or_jump_to_a_chapter(): void
    {
        $this->actingAs($this->Affiliate());
        $this->Save('pause', 'promptpay')->assertOk()->assertJsonPath('status', 'paused');
        $this->get('/affiliate/dashboard')->assertOk()->assertSee('"autoStart":false', false)
            ->assertSee('"status":"paused","step":"promptpay"', false);
        $this->Save('advance', 'bank')->assertStatus(409);
        $this->Save('start', 'promptpay')->assertOk()->assertJsonPath('step', 'promptpay');
        $this->Save('start', 'withdraw')->assertOk()->assertJsonPath('step', 'withdraw');
        $this->assertDatabaseCount('affiliate_tutorial_progress', 1);
    }

    #[Test]
    public function version_one_completion_is_preserved_and_removed_steps_have_a_resume_destination(): void
    {
        $user = $this->Affiliate();
        $this->actingAs($user);
        $record = ['affiliate_profile_id' => $user->AffiliateProfile->id, 'version' => 1,
            'status' => 'completed', 'step_key' => 'done', 'started_at' => now(), 'finished_at' => now(),
            'created_at' => now(), 'updated_at' => now()];
        \Illuminate\Support\Facades\DB::table('affiliate_tutorial_progress')->insert($record);
        $this->get('/affiliate/dashboard')->assertOk()->assertSee('"autoStart":false', false)
            ->assertSee('"status":"completed","step":"done"', false);
        \Illuminate\Support\Facades\DB::table('affiliate_tutorial_progress')->update(['status' => 'in_progress', 'step_key' => 'code']);
        $this->assertSame('link', app(AffiliateTutorialService::class)->ForUser($user)['progress']['step']);
        \Illuminate\Support\Facades\DB::table('affiliate_tutorial_progress')->update(['step_key' => 'password']);
        $this->assertSame('bank', app(AffiliateTutorialService::class)->ForUser($user)['progress']['step']);
    }

    #[Test]
    public function lessons_use_current_business_settings_and_examples_are_available_in_both_languages(): void
    {
        foreach (['commission_rate_basis_points' => '1250', 'commission_holding_days' => '9',
            'attribution_duration_days' => '45', 'minimum_withdrawal_minor' => '123450'] as $key => $value) {
            \Illuminate\Support\Facades\DB::table('affiliate_configurations')->where('key', $key)->update(['value' => $value]);
        }
        Config::set('affiliate.legal.support_phone', '02-000-1234');
        Config::set('affiliate.features.media_library', true);
        Config::set('affiliate.features.analytics', true);
        $this->actingAs($this->Affiliate());
        app()->setLocale('th');
        $service = app(AffiliateTutorialService::class);
        $steps = collect($service->Steps())->keyBy('key');
        $starterSteps = collect($service->StarterSteps($steps->values()->all()))->keyBy('key');
        $this->assertCount(21, $steps);
        $this->assertCount(4, $starterSteps);
        $this->assertSame(['copy', 'commission', 'promptpay', 'withdraw'], $starterSteps->keys()->all());
        $this->assertCount(5, $steps->pluck('chapter')->unique());
        $this->assertStringContainsString('12.5%', $steps['commission']['body']);
        $this->assertStringContainsString('9', $steps['commission']['body']);
        $this->assertStringContainsString('45', $steps['link']['body']);
        $this->assertStringContainsString('1,234.50', $steps['withdraw']['body']);
        $this->assertStringContainsString('02-000-1234', $steps['help']['body']);
        $this->assertStringContainsString('9', $starterSteps['commission']['body']);
        $this->assertStringContainsString('1,234.50', $starterSteps['withdraw']['body']);
        $this->assertStringContainsString('21', $starterSteps['withdraw']['body']);
        $this->get('/affiliate/dashboard')->assertOk()->assertSee('affiliate-example-share', false)
            ->assertSee('affiliate-example-withdraw', false)->assertSee('journey-v2.png', false)
            ->assertSee('ตัวอย่าง — ข้อมูลสมมติ');
        $view = file_get_contents(resource_path('views/affiliate/partials/tutorial-examples.blade.php'));
        preg_match_all("/__\('([^']+)'\)/", $view, $keys);
        foreach (['th', 'en'] as $locale) {
            foreach ($keys[1] as $key) $this->assertTrue(trans()->hasForLocale($key, $locale), $locale . ': ' . $key);
        }
        $this->assertDatabaseCount('affiliate_payout_accounts', 0);
        $this->assertDatabaseCount('affiliate_withdrawal_requests', 0);
    }

    #[Test]
    public function every_tutorial_destination_renders_but_only_the_guide_has_a_launcher(): void
    {
        Config::set('affiliate.features.media_library', true);
        Config::set('affiliate.features.analytics', true);
        $this->actingAs($this->Affiliate());
        foreach (array_unique(array_column(app(AffiliateTutorialService::class)->Steps(), 'url')) as $url) {
            $response = $this->get($url)->assertOk()->assertSee('affiliate-tutorial-config', false);
            if (parse_url($url, PHP_URL_PATH) === '/affiliate/guide') {
                $response->assertSee('data-affiliate-tutorial-start', false)
                    ->assertSee('data-affiliate-tutorial-mode="full"', false);
            } else {
                $response->assertDontSee('data-affiliate-tutorial-start', false);
            }
        }
    }

    private function Save(string $action, string $step)
    {
        return $this->postJson('/affiliate/tutorial/progress', ['version' => AffiliateTutorialService::VERSION, 'action' => $action, 'step' => $step]);
    }

    private function Affiliate(AffiliateProfileStatus $status = AffiliateProfileStatus::ACTIVE): User
    {
        $user = User::factory()->create(['department_id' => 1, 'disable' => 0, 'email_verified_at' => now(), 'language_code' => 'th', 'password' => Hash::make('tutorial-test')]);
        $user->assignRole(Role::findOrCreate('Affiliate-user', 'web'));
        AffiliateProfile::create(['user_id' => $user->id, 'referral_code' => 'AFF-TUTORIAL-' . $user->id, 'status' => $status]);

        return $user;
    }
}
