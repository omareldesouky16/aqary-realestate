<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ResolutionStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class PropertyTypeResolutionFlowTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatTestFactory::resetResolutionAliases();
    }

    public function test_property_type_synonym_completes_required_resolution(): void
    {
        ChatTestFactory::seedResolutionAliases();

        $state = ChatTestFactory::sessionState([
            'slots' => [
                'propertyType' => 'flat',
                'location' => 'Maadi',
                'location_id' => null,
                'price' => 2500000,
                'area' => null,
                'bedrooms' => null,
                'bathrooms' => null,
                'features' => [],
            ],
        ]);

        $merged = (new ResolutionStateService())->apply($state, []);

        $this->assertSame('Apartment', $merged['slots']['propertyType']);
        $this->assertSame('Maadi', $merged['slots']['location']);
        $this->assertArrayHasKey('resolution', $merged);
    }
}
