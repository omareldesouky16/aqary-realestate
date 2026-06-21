<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\ResolutionStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ResolutionStateServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatTestFactory::resetResolutionAliases();
    }

    public function test_state_preserves_unrelated_preferences_while_resolving_known_values(): void
    {
        ChatTestFactory::seedResolutionAliases();
        $service = new ResolutionStateService();
        $state = ChatTestFactory::sessionState([
            'slots' => [
                'propertyType' => 'flat',
                'location' => 'Tagamoa',
                'location_id' => null,
                'price' => 3000000,
                'area' => null,
                'bedrooms' => null,
                'bathrooms' => null,
                'features' => ['security'],
            ],
        ]);

        $merged = $service->apply($state, []);

        $this->assertSame('Apartment', $merged['slots']['propertyType']);
        $this->assertSame('New Cairo', $merged['slots']['location']);
        $this->assertContains('Security', $merged['slots']['features']);
        $this->assertArrayHasKey('resolution', $merged);
    }
}
