<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\SearchCriteriaService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class SearchCriteriaServiceTest extends TestCase
{
    public function test_search_becomes_ready_only_after_required_resolution_and_budget(): void
    {
        $state = ChatTestFactory::sessionState([
            'slots' => [
                'propertyType' => 'Apartment',
                'location' => 'New Cairo',
                'location_id' => 9,
                'price' => 3000000,
                'area' => 150,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'features' => ['Security'],
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

        $service = new SearchCriteriaService();
        $criteria = $service->fromState($state);

        $this->assertNotNull($criteria);
        $this->assertTrue($service->searchReady($state));
        $this->assertSame(11, $criteria->propertyTypeId);
        $this->assertSame(3600000, $criteria->budgetWindowMax);
    }

    public function test_unresolved_required_resolution_blocks_search(): void
    {
        $state = ChatTestFactory::sessionState([
            'slots' => ['propertyType' => 'Apartment', 'location' => 'New Cairo', 'location_id' => null, 'price' => 3000000],
            'resolution' => [
                'outcomes' => [
                    'propertyType' => ['status' => 'resolved', 'canonical_id' => 11, 'canonical_name' => 'Apartment'],
                    'location' => ['status' => 'ambiguous', 'canonical_id' => null, 'canonical_name' => null],
                    'features' => [],
                ],
                'pending_clarification' => null,
                'review_item_ids' => [],
            ],
        ]);

        $service = new SearchCriteriaService();

        $this->assertFalse($service->searchReady($state));
        $this->assertNull($service->fromState($state));
    }
}
