<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\SearchCriteriaService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class SearchContextResetTest extends TestCase
{
    public function test_core_changes_and_refinements_are_distinguished(): void
    {
        $service = new SearchCriteriaService();
        $state = ChatTestFactory::sessionState([
            'slots' => ['propertyType' => 'Apartment', 'location' => 'New Cairo', 'location_id' => 9, 'price' => 3000000],
            'resolution' => ['outcomes' => [
                'propertyType' => ['status' => 'resolved', 'canonical_id' => 11, 'canonical_name' => 'Apartment'],
                'location' => ['status' => 'resolved', 'canonical_id' => 9, 'canonical_name' => 'New Cairo'],
                'features' => [],
            ]],
        ]);

        $this->assertTrue($service->isCoreChange($state, ['slots' => ['location_id' => 10]]));
        $this->assertTrue($service->isRefinement(['slots' => ['price' => 3500000]]));
    }
}
