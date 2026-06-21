<?php

namespace App\Services\Chat;

class PropertyScoringService
{
    /**
     * @param array<string, mixed> $listing
     * @return array<string, mixed>
     */
    public function score(array $listing, SearchData $criteria): array
    {
        $price = (int) ($listing['price'] ?? 0);
        $budgetWindow = max(1, $criteria->budgetWindowMax);
        $priceGap = abs($criteria->maxBudget - $price);
        $priceScore = max(0, 60 - (int) round(($priceGap / $budgetWindow) * 60));

        $areaScore = $this->compareNumeric((int) ($listing['area'] ?? 0), $criteria->area, 15);
        $bedroomScore = $this->compareNumeric((int) ($listing['bedrooms'] ?? 0), $criteria->bedrooms, 10);
        $bathroomScore = $this->compareNumeric((int) ($listing['bathrooms'] ?? 0), $criteria->bathrooms, 10);

        $matchedFeatures = array_values(array_intersect($listing['feature_names'] ?? [], $criteria->featureNames));
        $featureScore = min(count($matchedFeatures) * 8, 20);
        $promotionBoost = ! empty($listing['is_promoted']) ? 5 : 0;

        $totalScore = $priceScore + $areaScore + $bedroomScore + $bathroomScore + $featureScore + $promotionBoost;

        return [
            'listing_id' => (int) ($listing['id'] ?? 0),
            'total_score' => $totalScore,
            'price_score' => $priceScore,
            'area_score' => $areaScore,
            'bedroom_score' => $bedroomScore,
            'bathroom_score' => $bathroomScore,
            'feature_score' => $featureScore,
            'promotion_boost' => $promotionBoost,
            'matched_feature_names' => $matchedFeatures,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $listings
     * @return array<int, array<string, mixed>>
     */
    public function rank(array $listings, SearchData $criteria): array
    {
        $scored = array_map(fn (array $listing): array => [
            'listing_id' => (int) ($listing['id'] ?? 0),
            'listing' => $listing,
            'score' => $this->score($listing, $criteria),
        ], $listings);

        usort($scored, function (array $left, array $right): int {
            $scoreComparison = $right['score']['total_score'] <=> $left['score']['total_score'];
            if ($scoreComparison !== 0) {
                return $scoreComparison;
            }

            $priceComparison = ($left['listing']['price'] ?? PHP_INT_MAX) <=> ($right['listing']['price'] ?? PHP_INT_MAX);
            if ($priceComparison !== 0) {
                return $priceComparison;
            }

            return ($left['listing']['id'] ?? 0) <=> ($right['listing']['id'] ?? 0);
        });

        return $scored;
    }

    private function compareNumeric(int $listingValue, ?int $criteriaValue, int $maxScore): int
    {
        if ($criteriaValue === null || $criteriaValue <= 0 || $listingValue <= 0) {
            return 0;
        }

        $gap = abs($listingValue - $criteriaValue);
        return max(0, $maxScore - min($gap * 2, $maxScore));
    }
}
