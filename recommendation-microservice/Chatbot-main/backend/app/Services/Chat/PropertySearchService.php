<?php

namespace App\Services\Chat;

use App\Models\ChatbotListing;

class PropertySearchService
{
    public function __construct(
        private readonly PropertyScoringService $scoring = new PropertyScoringService(),
        private readonly SearchCriteriaService $criteria = new SearchCriteriaService(),
        private readonly SearchResultStateService $stateService = new SearchResultStateService(),
        private readonly SearchOutcomeService $outcomes = new SearchOutcomeService(),
        private readonly BudgetFallbackService $fallbacks = new BudgetFallbackService(),
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function search(array $state, array $nlu = []): array
    {
        $criteria = $this->criteria->fromState($state);
        if (! $criteria) {
            return [
                'state' => $this->stateService->clear($state),
                'properties' => [],
                'has_more' => false,
                'min_price_fallback' => null,
                'event' => $this->outcomes->replyFallback([]),
            ];
        }

        if (($nlu['flags']['show_more_requested'] ?? false) === true || ($nlu['intent'] ?? null) === 'show_more_results') {
            return $this->showMore($state, $criteria);
        }

        $listings = $this->fetchActiveCashListings($criteria);
        $ranked = $this->scoring->rank($listings, $criteria);
        $retained = array_slice($ranked, 0, 20);
        $visible = array_slice($retained, 0, 5);

        if ($ranked !== []) {
            $searchState = $this->stateService->store($state, $criteria, $retained, $visible, 'results');

            return [
                'state' => $searchState,
                'properties' => $searchState['shown_properties'],
                'has_more' => $searchState['search']['has_more'],
                'min_price_fallback' => null,
                'event' => $this->outcomes->searchResults($criteria->toArray(), count($listings), count($visible), count($retained)),
                'search_id' => $searchState['search']['search_id'],
            ];
        }

        $fallback = $this->fallbacks->sameScopeMinimum($criteria);
        if ($fallback === null) {
            $cleared = $this->stateService->clear($state);
            $cleared['search']['status'] = 'no_results';

            return [
                'state' => $cleared,
                'properties' => [],
                'has_more' => false,
                'min_price_fallback' => null,
                'event' => $this->outcomes->noResults($criteria->toArray(), 0),
                'search_id' => null,
            ];
        }

        $cleared = $this->stateService->clear($state);
        $cleared['search']['status'] = 'budget_fallback';
        $cleared['search']['min_price_fallback'] = $fallback['minimum_available_price'];
        $cleared['search']['budget_fallback'] = $fallback;
        $cleared['search']['criteria_snapshot'] = $criteria->toArray();
        $cleared['search']['criteria_digest'] = $criteria->digest();
        $cleared['search']['search_id'] = $criteria->digest();

        return [
            'state' => $cleared,
            'properties' => [],
            'has_more' => false,
            'min_price_fallback' => $fallback['minimum_available_price'],
            'event' => $this->outcomes->budgetFallback($criteria->toArray(), (int) $fallback['available_listing_count_in_scope'], (int) $fallback['minimum_available_price']),
            'search_id' => $criteria->digest(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function showMore(array $state, SearchData $criteria): array
    {
        $search = $state['search'] ?? [];
        $rankedIds = $search['ranked_listing_ids'] ?? [];
        if ($rankedIds === []) {
            $cleared = $this->stateService->clear($state);
            $cleared['search']['status'] = 'no_results';

            return [
                'state' => $cleared,
                'properties' => [],
                'has_more' => false,
                'min_price_fallback' => null,
                'event' => $this->outcomes->noResults($criteria->toArray(), 0),
                'search_id' => null,
            ];
        }

        $listings = ChatbotListing::query()
            ->with('features')
            ->whereIn('id', $rankedIds)
            ->get()
            ->keyBy('id');

        $scores = $search['ranking_scores'] ?? [];
        $ranked = [];
        foreach ($rankedIds as $listingId) {
            $listing = $listings->get($listingId);
            if (! $listing instanceof ChatbotListing) {
                continue;
            }

            $ranked[] = [
                'listing_id' => (int) $listingId,
                'listing' => $this->listingToArray($listing),
                'score' => $scores[$listingId] ?? $scores[(string) $listingId] ?? null,
            ];
        }

        $page = $this->stateService->nextPage($state, $ranked);

        return [
            'state' => $page['state'],
            'properties' => $page['items'],
            'has_more' => ! $page['exhausted'],
            'min_price_fallback' => null,
            'event' => $this->outcomes->showMore($criteria->toArray(), count($page['items']), count($ranked), $page['exhausted']),
            'search_id' => $page['state']['search']['search_id'] ?? null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchActiveCashListings(SearchData $criteria): array
    {
        return ChatbotListing::query()
            ->with('features')
            ->where('status', 'active')
            ->where('payment_type', 'cash')
            ->where('location_id', $criteria->locationId)
            ->where('property_type_id', $criteria->propertyTypeId)
            ->where('price', '<=', $criteria->budgetWindowMax)
            ->get()
            ->map(fn (ChatbotListing $listing): array => $this->listingToArray($listing))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function listingToArray(ChatbotListing $listing): array
    {
        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'url' => $listing->url,
            'price' => $listing->price,
            'area' => $listing->area,
            'bedrooms' => $listing->bedrooms,
            'bathrooms' => $listing->bathrooms,
            'furnished_status' => $listing->furnished_status,
            'location_id' => $listing->location_id,
            'location_name' => $listing->location_name,
            'property_type_id' => $listing->property_type_id,
            'feature_ids' => $listing->features->pluck('id')->values()->all(),
            'feature_names' => $listing->features->pluck('name')->values()->all(),
            'cover_image_url' => $listing->cover_image_url,
            'is_promoted' => (bool) $listing->is_promoted,
            'status' => $listing->status,
            'payment_type' => $listing->payment_type,
        ];
    }
}
