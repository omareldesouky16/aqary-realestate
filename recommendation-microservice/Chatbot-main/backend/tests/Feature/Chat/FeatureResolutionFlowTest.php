<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ResolutionStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class FeatureResolutionFlowTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatTestFactory::resetResolutionAliases();
    }

    public function test_clear_features_are_retained_while_unclear_ones_log_review_state(): void
    {
        ChatTestFactory::seedResolutionAliases();

        $state = ChatTestFactory::sessionState([
            'slots' => [
                'propertyType' => 'apartment',
                'location' => 'Maadi',
                'location_id' => null,
                'price' => 2500000,
                'area' => null,
                'bedrooms' => null,
                'bathrooms' => null,
                'features' => ['security', 'unknown feature'],
            ],
        ]);

        $merged = (new ResolutionStateService())->apply($state, []);

        $this->assertContains('Security', $merged['slots']['features']);
        $this->assertNotEmpty($merged['resolution']['review_item_ids']);
    }
}
