<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateIdentityStatus;
use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateIdentityProfile;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AffiliateIdentityVerificationTest extends TestCase
{
    use RefreshDatabase;

    private const NATIONAL_ID = '1101700207030';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.features.administration', true);
        Role::findOrCreate('Affiliate-user', 'web');
    }

    #[Test]
    public function LegalPagesArePublicAndRegistrationRequiresSeparateConsents(): void
    {
        $this->get(route('affiliate.terms'))->assertOk()->assertSee('ข้อกำหนดและเงื่อนไข');
        $this->get(route('affiliate.privacy'))->assertOk()->assertSee('นโยบายความเป็นส่วนตัว');
        $this->get('/terms')->assertRedirect('/affiliate/terms');
        $this->get('/privacy')->assertRedirect('/affiliate/privacy');

        $this->post(route('affiliate.register'), [
            'name' => 'Consent Missing', 'email' => 'missing-consent@example.test',
            'phone' => '0811111111', 'password' => 'password-123', 'password_confirmation' => 'password-123',
        ])->assertSessionHasErrors(['terms_accepted', 'privacy_acknowledged']);
    }

    #[Test]
    public function RegistrationStoresConsentEvidenceAndEmailVerificationStartsIdentityStep(): void
    {
        Notification::fake();
        $response = $this->post(route('affiliate.register'), [
            'name' => 'Natural Person', 'email' => 'natural-person@example.test',
            'phone' => '0812222222', 'password' => 'password-123', 'password_confirmation' => 'password-123',
            'terms_accepted' => '1', 'privacy_acknowledged' => '1', 'marketing_consent' => '1',
        ]);
        $response->assertRedirect(route('affiliate.waiting'));

        $user = User::query()->where('email', 'natural-person@example.test')->firstOrFail();
        $identity = $user->AffiliateProfile->IdentityProfile;
        $this->assertSame(AffiliateIdentityStatus::DRAFT, $identity->status);
        $this->assertNotNull($identity->terms_accepted_at);
        $this->assertNotNull($identity->privacy_acknowledged_at);
        $this->assertTrue($identity->marketing_consent);

        app(\App\Domain\Affiliate\Services\AffiliateAccountService::class)->MarkEmailVerified($user);
        $this->assertSame(AffiliateProfileStatus::PENDING_IDENTITY, $user->AffiliateProfile->fresh()->status);
        $user->unsetRelation('AffiliateProfile');
        $this->actingAs($user)->get(route('affiliate.waiting'))->assertRedirect(route('affiliate.identity.show'));
    }

    #[Test]
    public function AffiliateCanSubmitEncryptedIdentityAndPrivateDocument(): void
    {
        Storage::fake('local');
        [$user, $profile, $identity] = $this->CreatePendingIdentityAffiliate();

        $this->actingAs($user)->post(route('affiliate.identity.store'), $this->ValidIdentityPayload())
            ->assertRedirect(route('affiliate.waiting'));

        $profile->refresh();
        $identity->refresh();
        $this->assertSame(AffiliateProfileStatus::PENDING_APPROVAL, $profile->status);
        $this->assertSame(AffiliateIdentityStatus::PENDING, $identity->status);
        $this->assertSame(self::NATIONAL_ID, $identity->national_id);
        $this->assertNotSame(self::NATIONAL_ID, DB::table('affiliate_identity_profiles')->where('id', $identity->id)->value('national_id'));
        $this->assertSame(AffiliateIdentityProfile::SensitiveHash(self::NATIONAL_ID), $identity->national_id_hash);
        Storage::disk('local')->assertExists($identity->id_document_path);

        $audit = DB::table('affiliate_audit_logs')->where('operation', 'affiliate.identity-submitted')->latest('id')->first();
        $audit_json = json_encode($audit);
        $this->assertStringNotContainsString(self::NATIONAL_ID, $audit_json);
        $this->assertStringNotContainsString('99/1', $audit_json);
    }

    #[Test]
    public function InvalidNationalIdAndWrongDocumentTypeAreRejected(): void
    {
        Storage::fake('local');
        [$user, $profile] = $this->CreatePendingIdentityAffiliate();
        $payload = $this->ValidIdentityPayload();
        $payload['national_id'] = '1101700207031';
        $payload['id_document'] = UploadedFile::fake()->create('id.pdf', 20, 'application/pdf');

        $this->actingAs($user)->post(route('affiliate.identity.store'), $payload)
            ->assertSessionHasErrors(['national_id', 'id_document']);
        $this->assertSame(AffiliateProfileStatus::PENDING_IDENTITY, $profile->fresh()->status);
    }

    #[Test]
    public function AdminCanReviewRejectResubmissionAndApproveWhileEmployeeApprovalStillWorks(): void
    {
        Storage::fake('local');
        [$affiliate, $profile, $identity] = $this->CreatePendingIdentityAffiliate();
        $this->actingAs($affiliate)->post(route('affiliate.identity.store'), $this->ValidIdentityPayload());

        $admin = User::factory()->create(['password' => Hash::make('secret'), 'disable' => 0]);
        $admin->givePermissionTo([
            Permission::findOrCreate('affiliate-list', 'web'),
            Permission::findOrCreate('affiliate-approve', 'web'),
            Permission::findOrCreate('affiliate-identity-review', 'web'),
        ]);
        $confirmed = ['auth.password_confirmed_at' => time()];

        $this->actingAs($admin)->withSession($confirmed)->get(route('affiliate.admin.identity.show', $profile))
            ->assertOk()->assertSee(self::NATIONAL_ID)->assertSee('99/1');
        $this->actingAs($admin)->withSession($confirmed)->get(route('affiliate.admin.identity.document', $profile))
            ->assertOk()->assertHeader('Cache-Control', 'max-age=0, no-store, private');
        $this->assertDatabaseHas('affiliate_audit_logs', [
            'operation' => 'affiliate.identity-viewed', 'subject_id' => $identity->id, 'actor_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('affiliate_audit_logs', [
            'operation' => 'affiliate.identity-document-viewed', 'subject_id' => $identity->id, 'actor_id' => $admin->id,
        ]);

        $this->actingAs($admin)->withSession($confirmed)->post(route('affiliate.admin.identity.reject', $profile), [
            'rejection_reason' => 'Please upload a clearer image.',
        ])->assertRedirect(route('affiliate.admin.index'));
        $this->assertSame(AffiliateProfileStatus::PENDING_IDENTITY, $profile->fresh()->status);
        $this->assertSame(AffiliateIdentityStatus::REJECTED, $identity->fresh()->status);

        $this->actingAs($affiliate)->post(route('affiliate.identity.store'), $this->ValidIdentityPayload());
        $this->actingAs($admin)->post(route('affiliate.admin.approve', $profile))->assertRedirect();
        $this->assertSame(AffiliateProfileStatus::ACTIVE, $profile->fresh()->status);
        $this->assertSame(AffiliateIdentityStatus::VERIFIED, $identity->fresh()->status);

        $employee = User::factory()->create(['password' => Hash::make('secret')]);
        $employee->assignRole(Role::findOrCreate('Teacher', 'web'));
        $employee_profile = AffiliateProfile::create([
            'user_id' => $employee->id, 'referral_code' => 'AFF-EMPLOYEE-' . $employee->id,
            'status' => AffiliateProfileStatus::PENDING_APPROVAL,
        ]);
        $this->actingAs($admin)->post(route('affiliate.admin.approve', $employee_profile))->assertRedirect();
        $this->assertSame(AffiliateProfileStatus::ACTIVE, $employee_profile->fresh()->status);
    }

    private function CreatePendingIdentityAffiliate(): array
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'password' => Hash::make('secret')]);
        $user->assignRole('Affiliate-user');
        $profile = AffiliateProfile::create([
            'user_id' => $user->id, 'referral_code' => 'AFF-KYC-' . $user->id,
            'status' => AffiliateProfileStatus::PENDING_IDENTITY,
        ]);
        $identity = AffiliateIdentityProfile::create([
            'affiliate_profile_id' => $profile->id, 'status' => AffiliateIdentityStatus::DRAFT,
            'terms_version' => '2026-09-06', 'terms_accepted_at' => now(),
            'privacy_version' => '2026-09-06', 'privacy_acknowledged_at' => now(),
        ]);

        return [$user, $profile, $identity];
    }

    private function ValidIdentityPayload(): array
    {
        return [
            'honorific' => 'mr', 'legal_first_name' => 'สมชาย', 'legal_last_name' => 'ใจดี',
            'national_id' => self::NATIONAL_ID, 'card_expires_on' => now()->addYears(3)->format('Y-m-d'),
            'address_line1' => '99/1 ถนนสุขุมวิท', 'subdistrict' => 'คลองเตย', 'district' => 'คลองเตย',
            'province' => 'กรุงเทพมหานคร', 'postal_code' => '10110',
            'id_document' => UploadedFile::fake()->image('national-id.png', 800, 500),
            'identity_certified' => '1',
        ];
    }
}
