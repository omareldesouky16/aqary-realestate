<?php

namespace Tests\Feature\Chat;

use PHPUnit\Framework\TestCase;

class SearchContractTest extends TestCase
{
    public function test_phase_three_response_shape_includes_search_fields(): void
    {
        $expected = [
            'reply',
            'intent',
            'isComplaint',
            'needsCheckIn',
            'installment_redirect',
            'awaiting_slots',
            'slot_collection',
            'resolution',
            'resolved_property_id',
            'property_reference',
            'property_detail',
            'property_gallery',
            'seller_contact',
            'properties',
            'search',
            'show_image_offer',
            'has_more',
            'min_price_fallback',
            'session_id',
        ];

        $this->assertContains('search', $expected);
        $this->assertContains('properties', $expected);
        $this->assertContains('has_more', $expected);
    }
}
