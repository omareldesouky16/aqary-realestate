<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\SlotCollectionState;
use PHPUnit\Framework\TestCase;
use Tests\Support\SlotCollectionStateFactory;

class SlotCollectionStateTest extends TestCase
{
    public function test_required_slots_are_reported_in_order_and_budget_defaults_to_egp(): void
    {
        $state = SlotCollectionStateFactory::state([
            'slots' => [
                'propertyType' => 'apartment',
                'location' => 'Cairo',
                'price' => 3000000,
                'area' => null,
                'bedrooms' => null,
                'bathrooms' => null,
                'features' => [],
            ],
        ]);

        $collection = SlotCollectionState::build($state);

        $this->assertSame([], $collection['missing_required_slots']);
        $this->assertSame('optional_preferences', $collection['next_question_slot']);
        $this->assertSame('EGP', $collection['budget_currency']);
        $this->assertFalse($collection['search_ready']);
    }
}
