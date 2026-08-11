<?php

namespace Tests\Feature\Affiliate;

use App\Domain\Affiliate\Enums\AffiliateProfileStatus;
use App\Domain\Affiliate\Models\AffiliateAttribution;
use App\Domain\Affiliate\Models\AffiliateClickEvent;
use App\Domain\Affiliate\Models\AffiliateProfile;
use App\Models\AffiliateContent;
use App\Models\Contact;
use App\Models\CoursePending;
use App\Models\Order;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class AffiliateAttributionPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_attribution_and_order_snapshot_relationships_persist(): void
    {
        [$user, $profile] = $this->createProfile();
        $contact = Contact::create(['name' => 'Student', 'centre' => 1]);
        $captured_at = CarbonImmutable::parse('2026-08-01 00:00:00', 'UTC');
        $expires_at = CarbonImmutable::parse('2027-01-28 00:00:00', 'UTC');

        $attribution = AffiliateAttribution::create([
            'affiliate_profile_id' => $profile->id,
            'contact_id' => $contact->id,
            'captured_at' => $captured_at,
            'expires_at' => $expires_at,
            'source_context' => 'course_referral',
        ]);
        $contact->forceFill(['current_affiliate_attribution_id' => $attribution->id])->save();

        $order = Order::create([
            'cid' => $contact->id,
            'status' => 0,
            'payment' => 0,
        ]);
        $order->forceFill([
            'affiliate_attribution_id' => $attribution->id,
            'affiliate_commission_rate_basis_points' => 2000,
        ])->save();

        $fresh_attribution = $attribution->fresh();
        $this->assertSame($profile->id, $fresh_attribution->AffiliateProfile->id);
        $this->assertSame($contact->id, $fresh_attribution->Contact->id);
        $this->assertInstanceOf(CarbonImmutable::class, $fresh_attribution->expires_at);
        $this->assertSame($attribution->id, $contact->fresh()->CurrentAffiliateAttribution->id);
        $this->assertSame($attribution->id, $order->fresh()->AffiliateAttribution->id);
        $this->assertSame(2000, $order->fresh()->affiliate_commission_rate_basis_points);
    }

    public function test_pending_booking_bridge_is_server_persisted_and_related_to_profile(): void
    {
        [, $profile] = $this->createProfile();

        $pending = CoursePending::create([
            'name' => 'Parent',
            'email' => 'parent@example.test',
            'course_id' => 1,
        ]);
        $pending->forceFill([
            'affiliate_profile_id' => $profile->id,
            'affiliate_captured_at' => '2026-08-01 00:00:00',
            'affiliate_expires_at' => '2027-01-28 00:00:00',
        ])->save();

        $fresh_pending = $pending->fresh();
        $this->assertSame($profile->id, $fresh_pending->AffiliateProfile->id);
        $this->assertInstanceOf(CarbonImmutable::class, $fresh_pending->affiliate_expires_at);
        $this->assertFalse($pending->isFillable('affiliate_profile_id'));
    }

    public function test_attribution_foreign_keys_restrict_profile_and_contact_deletion(): void
    {
        [$user, $profile] = $this->createProfile();
        $contact = Contact::create(['name' => 'Student', 'centre' => 1]);
        AffiliateAttribution::create([
            'affiliate_profile_id' => $profile->id,
            'contact_id' => $contact->id,
            'captured_at' => now(),
            'expires_at' => now()->addDays(180),
            'source_context' => 'course_referral',
        ]);

        try {
            $contact->delete();
            $this->fail('Contact deletion should be restricted while attribution evidence exists.');
        } catch (QueryException) {
            $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
        }

        $this->expectException(QueryException::class);
        $user->delete();
    }

    public function test_courses_pending_bridge_columns_are_present(): void
    {
        foreach ([
            'affiliate_profile_id',
            'affiliate_captured_at',
            'affiliate_expires_at',
            'affiliate_click_event_id',
            'affiliate_content_id',
            'affiliate_campaign_code',
            'affiliate_source_type',
            'affiliate_unique_window_key',
            'affiliate_captured_destination',
        ] as $column) {
            $this->assertTrue(Schema::hasColumn('courses_pennding', $column));
        }
    }

    public function test_click_and_content_foreign_keys_clear_without_losing_attribution_snapshots(): void
    {
        [, $profile] = $this->createProfile();
        $content = AffiliateContent::create([
            'title' => 'Snapshot media',
            'file_path' => 'uploads/affiliate/snapshot.png',
            'type' => 'image',
            'status' => 'enabled',
        ]);
        $event = AffiliateClickEvent::create([
            'affiliate_profile_id' => $profile->id,
            'affiliate_content_id' => $content->id,
            'campaign_code' => 'snapshot-campaign',
            'source_type' => 'media',
            'destination_target' => '/courses/math',
            'visitor_hash' => hash('sha256', 'snapshot'),
            'unique_window_key' => '01K1Z2Y3X4W5V6U7T8S9R0Q1P5',
            'is_unique' => true,
            'clicked_at' => now('UTC'),
        ]);
        $contact = Contact::create(['name' => 'Snapshot student', 'centre' => 1]);
        $attribution = new AffiliateAttribution([
            'affiliate_profile_id' => $profile->id,
            'contact_id' => $contact->id,
            'captured_at' => now('UTC'),
            'expires_at' => now('UTC')->addDays(180),
            'source_context' => 'campaign',
        ]);
        $attribution->forceFill([
            'affiliate_click_event_id' => $event->id,
            'affiliate_content_id' => $content->id,
            'affiliate_campaign_code' => 'snapshot-campaign',
            'affiliate_source_type' => 'media',
            'affiliate_unique_window_key' => '01K1Z2Y3X4W5V6U7T8S9R0Q1P5',
            'affiliate_captured_destination' => '/courses/math',
        ])->save();

        AffiliateClickEvent::query()->whereKey($event->id)->delete();
        DB::table('affiliate_contents')->where('id', $content->id)->delete();

        $fresh = $attribution->fresh();
        $this->assertNull($fresh->affiliate_click_event_id);
        $this->assertNull($fresh->affiliate_content_id);
        $this->assertSame('snapshot-campaign', $fresh->affiliate_campaign_code);
        $this->assertSame('media', $fresh->affiliate_source_type);
        $this->assertSame('01K1Z2Y3X4W5V6U7T8S9R0Q1P5', $fresh->affiliate_unique_window_key);
        $this->assertSame('/courses/math', $fresh->affiliate_captured_destination);
    }

    public function test_pending_bridge_persists_all_optional_context_without_mass_assignment(): void
    {
        [, $profile] = $this->createProfile();
        $pending = CoursePending::create(['name' => 'Snapshot parent', 'email' => 'snapshot@example.test', 'course_id' => 1]);
        $pending->forceFill([
            'affiliate_profile_id' => $profile->id,
            'affiliate_captured_at' => now('UTC'),
            'affiliate_expires_at' => now('UTC')->addDays(180),
            'affiliate_click_event_id' => 17,
            'affiliate_content_id' => 18,
            'affiliate_campaign_code' => 'bridge-campaign',
            'affiliate_source_type' => 'direct',
            'affiliate_unique_window_key' => '01K1Z2Y3X4W5V6U7T8S9R0Q1P6',
            'affiliate_captured_destination' => '/courses/math',
        ])->save();

        $fresh = $pending->fresh();
        $this->assertSame(17, $fresh->affiliate_click_event_id);
        $this->assertSame(18, $fresh->affiliate_content_id);
        $this->assertSame('bridge-campaign', $fresh->affiliate_campaign_code);
        $this->assertSame('direct', $fresh->affiliate_source_type);
        $this->assertSame('01K1Z2Y3X4W5V6U7T8S9R0Q1P6', $fresh->affiliate_unique_window_key);
        $this->assertSame('/courses/math', $fresh->affiliate_captured_destination);
        foreach (['affiliate_click_event_id', 'affiliate_campaign_code', 'affiliate_source_type'] as $column) {
            $this->assertFalse($fresh->isFillable($column));
        }
    }

    public function test_attribution_snapshot_fields_are_immutable_after_creation(): void
    {
        [, $profile] = $this->createProfile();
        $contact = Contact::create(['name' => 'Immutable student', 'centre' => 1]);
        $attribution = new AffiliateAttribution([
            'affiliate_profile_id' => $profile->id,
            'contact_id' => $contact->id,
            'captured_at' => now('UTC'),
            'expires_at' => now('UTC')->addDays(180),
            'source_context' => 'campaign',
        ]);
        $attribution->forceFill(['affiliate_campaign_code' => 'immutable-campaign'])->save();

        $this->expectException(LogicException::class);
        $attribution->affiliate_campaign_code = 'changed-campaign';
        $attribution->save();
    }

    private function createProfile(): array
    {
        $user = User::factory()->create(['password' => Hash::make('secret')]);
        $profile = AffiliateProfile::create([
            'user_id' => $user->id,
            'referral_code' => 'AFF-' . $user->id,
            'status' => AffiliateProfileStatus::ACTIVE,
        ]);

        return [$user, $profile];
    }
}
