<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\SearchCriteriaService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class PropertySearchServiceTest extends TestCase
{
    public function test_search_readiness_requires_resolved_required_slots_and_budget_window(): void
    {
        $state = ChatTestFactory::sessionState([
            'slots' => [
                'propertyType' => 'Apartment',
                'location' => 'New Cairo',
                'location_id' => 9,
                'price' => 2500000,
            ],
            'resolution' => [
                'outcomes' => [
                    'propertyType' => ['status' => 'resolved', 'canonical_id' => 11, 'canonical_name' => 'Apartment'],
                    'location' => ['status' => 'resolved', 'canonical_id' => 9, 'canonical_name' => 'New Cairo'],
                    'features' => [],
                ],
                'pending_clarification' => null,
                'review_item_ids' => [],
            ],
        ]);

        $criteria = (new SearchCriteriaService())->fromState($state);

        $this->assertNotNull($criteria);
        $this->assertSame(3000000, $criteria->budgetWindowMax);
        $this->assertTrue($criteria->isReady());
    }
}
