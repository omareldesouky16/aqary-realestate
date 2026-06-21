<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ResolutionStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ResolutionStatePreservationFlowTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatTestFactory::resetResolutionAliases();
    }

    public function test_unrelated_preferences_remain_intact_when_one_value_needs_clarification(): void
    {
        ChatTestFactory::seedResolutionAliases();
        $service = new ResolutionStateService();

        $state = ChatTestFactory::sessionState([
            'slots' => [
                'propertyType' => 'flat',
                'location' => 'New',
                'location_id' => null,
                'price' => 3500000,
                'area' => null,
                'bedrooms' => null,
                'bathrooms' => null,
                'features' => ['security'],
            ],
        ]);

        $merged = $service->apply($state, []);

        $this->assertSame('Apartment', $merged['slots']['propertyType']);
        $this->assertSame(3500000, $merged['slots']['price']);
        $this->assertContains('Security', $merged['slots']['features']);
        $this->assertNotNull($merged['resolution']['pending_clarification']);
    }
}
