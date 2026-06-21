<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\SlotExtractor;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class InstallmentSlotExclusionTest extends TestCase
{
    public function test_payment_slots_are_not_merged_into_search_preferences(): void
    {
        $merged = (new SlotExtractor())->merge(ChatTestFactory::sessionState(), [
            'slots' => [
                'paymentMethod' => 'installment',
                'downPayment' => 10000,
                'price' => 900000,
            ],
        ]);

        $this->assertArrayNotHasKey('paymentMethod', $merged['slots']);
        $this->assertSame(900000, $merged['slots']['price']);
    }
}
