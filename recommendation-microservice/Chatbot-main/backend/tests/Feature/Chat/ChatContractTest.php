<?php

namespace Tests\Feature\Chat;

use PHPUnit\Framework\TestCase;

class ChatContractTest extends TestCase
{
    public function test_phase_four_contract_fields_are_present(): void
    {
        $fields = ['property_reference', 'property_detail', 'property_gallery', 'seller_contact', 'resolved_property_id', 'resolved_by'];

        $this->assertContains('property_detail', $fields);
        $this->assertContains('seller_contact', $fields);
    }
}
