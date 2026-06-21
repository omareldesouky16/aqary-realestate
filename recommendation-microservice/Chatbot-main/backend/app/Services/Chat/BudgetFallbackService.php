<?php

namespace App\Services\Chat;

use App\Models\ChatbotListing;

class BudgetFallbackService
{
    /**
     * @return array<string, mixed>|null
     */
    public function sameScopeMinimum(SearchData $criteria): ?array
    {
        $query = ChatbotListing::query()
            ->where('status', 'active')
            ->where('payment_type', 'cash')
            ->where('location_id', $criteria->locationId)
            ->where('property_type_id', $criteria->propertyTypeId);

        $count = (clone $query)->count();
        if ($count === 0) {
            return null;
        }

        return [
            'minimum_available_price' => (int) (clone $query)->min('price'),
            'scope_location' => $criteria->locationName,
            'scope_property_type' => $criteria->propertyTypeName,
            'stated_max_budget' => $criteria->maxBudget,
            'budget_window_max' => $criteria->budgetWindowMax,
            'available_listing_count_in_scope' => $count,
            'prompted_for_budget_adjustment' => true,
        ];
    }
}
