<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ResolutionStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class LocationResolutionFlowTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatTestFactory::resetResolutionAliases();
    }

    public function test_location_alias_resolves_and_preserves_other_state(): void
    {
        ChatTestFactory::seedResolutionAliases();

        $state = ChatTestFactory::sessionState([
            'slots' => [
                'propertyType' => 'apartment',
                'location' => 'Tagamoa',
                'location_id' => null,
                'price' => 3000000,
                'area' => null,
                'bedrooms' => null,
                'bathrooms' => null,
                'features' => [],
            ],
        ]);

        $merged = (new ResolutionStateService())->apply($state, []);

        $this->assertSame('New Cairo', $merged['slots']['location']);
        $this->assertSame('Apartment', $merged['resolution']['outcomes']['propertyType']['canonical_name']);
        $this->assertFalse($merged['slot_collection']['search_ready']);
    }
}
