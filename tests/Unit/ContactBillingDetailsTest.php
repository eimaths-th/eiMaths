<?php

namespace Tests\Unit;

use App\Models\Contact;
use PHPUnit\Framework\TestCase;

class ContactBillingDetailsTest extends TestCase
{
    public function test_father_details_are_used_when_available(): void
    {
        $contact = new Contact([
            'father_name' => 'Father Name',
            'father_mobile' => '080-000-0000',
            'mother_name' => 'Mother Name',
            'mother_mobile' => '090-000-0000',
        ]);

        $this->assertSame(
            'คุณ Father Name / 080-000-0000',
            $contact->defaultBillingDetails()
        );
    }

    public function test_mother_details_are_used_when_father_is_empty(): void
    {
        $contact = new Contact([
            'mother_name' => 'Mother Name',
            'mother_mobile' => '090-000-0000',
        ]);

        $this->assertSame(
            'คุณ Mother Name / 090-000-0000',
            $contact->defaultBillingDetails()
        );
    }

    public function test_empty_parent_details_return_an_empty_string(): void
    {
        $this->assertSame('', (new Contact())->defaultBillingDetails());
    }
}
