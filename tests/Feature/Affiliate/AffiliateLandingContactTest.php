<?php

namespace Tests\Feature\Affiliate;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateLandingContactTest extends TestCase
{
    use RefreshDatabase;

    private const PUBLIC_CONTACT_EMAIL = 'eimaths.thailand2023@gmail.com';

    private const OBSOLETE_CONTACT_EMAIL = 'info@eimaths-th.com';

    #[Test]
    public function ShowsThePublicWebsiteEmailAddressAsAMailLink(): void
    {
        $response = $this->get(route('affiliate.index'));

        $response
            ->assertOk()
            ->assertSee(self::PUBLIC_CONTACT_EMAIL)
            ->assertSee('href="mailto:'.self::PUBLIC_CONTACT_EMAIL.'"', false)
            ->assertDontSee(self::OBSOLETE_CONTACT_EMAIL);
    }
}
