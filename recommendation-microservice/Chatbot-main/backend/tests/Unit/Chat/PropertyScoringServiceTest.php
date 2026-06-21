<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\PropertyScoringService;
use App\Services\Chat\SearchData;
use PHPUnit\Framework\TestCase;

class PropertyScoringServiceTest extends TestCase
{
    public function test_rank_prefers_buyer_fit_over_promotion_when_relevance_is_clear(): void
    {
        $criteria = new SearchData(
            sessionId: '11111111-1111-4111-8111-111111111111',
            propertyTypeId: 11,
            propertyTypeName: 'Apartment',
            locationId: 9,
            locationName: 'New Cairo',
            maxBudget: 3000000,
            budgetWindowMax: 3600000,
            area: 150,
            bedrooms: 3,
            bathrooms: 2,
            featureIds: [21],
            featureNames: ['Security'],
            language: 'en',
        );

        $scoring = new PropertyScoringService();
        $ranked = $scoring->rank([
            ['id' => 1, 'price' => 2950000, 'area' => 150, 'bedrooms' => 3, 'bathrooms' => 2, 'feature_names' => ['Security'], 'is_promoted' => false],
            ['id' => 2, 'price' => 3050000, 'area' => 120, 'bedrooms' => 2, 'bathrooms' => 1, 'feature_names' => [], 'is_promoted' => true],
        ], $criteria);

        $this->assertSame(1, $ranked[0]['listing_id']);
        $this->assertSame(2, $ranked[1]['listing_id']);
        $this->assertLessThanOrEqual(5, $ranked[1]['score']['promotion_boost']);
    }
}
