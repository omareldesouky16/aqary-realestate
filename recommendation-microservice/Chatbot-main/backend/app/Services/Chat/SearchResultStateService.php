<?php

namespace App\Services\Chat;

class SearchResultStateService
{
    public function __construct(private readonly SearchReplyFormatter $formatter = new SearchReplyFormatter())
    {
    }

    /**
     * @param array<int, array<string, mixed>> $ranked
     * @param array<int, array<string, mixed>> $visible
     */
    public function store(array $state, SearchData $criteria, array $ranked, array $visible, string $status = 'results', ?array $fallback = null): array
    {
        $now = date(DATE_ATOM);
        $searchId = $criteria->digest();
        $scored = [];
        $rankedIds = [];

        foreach ($ranked as $index => $item) {
            $listing = $item['listing'] ?? $item;
            $score = $item['score'] ?? [];
            $listingId = (int) ($listing['id'] ?? 0);
            if ($listingId <= 0) {
                continue;
            }

            $rankedIds[] = $listingId;
            $score['rank_position'] = $index + 1;
            $scored[$listingId] = $score;
        }

        $visibleListings = array_map(static fn (array $item): array => $item['listing'] ?? $item, $visible);
        $page = $this->visiblePageItems($visibleListings, $rankedIds, $scored);

        $state['search'] = [
            'status' => $status,
            'search_id' => $searchId,
            'criteria_digest' => $criteria->digest(),
            'criteria_snapshot' => $criteria->toArray(),
            'ranked_listing_ids' => array_slice($rankedIds, 0, 20),
            'ranking_scores' => $scored,
            'result_items' => $page['items'],
            'visible_reference_map' => $page['visible_reference_map'],
            'result_count' => count($rankedIds),
            'shown_count' => count($page['items']),
            'page_size' => 5,
            'has_more' => count($rankedIds) > count($page['items']),
            'min_price_fallback' => $fallback,
            'budget_fallback' => $fallback,
            'last_shown_at' => $now,
            'created_at' => $now,
        ];
        $state['shown_properties'] = $page['items'];
        $state['shown_properties_data'] = $page['items'];
        $state['ranked_result_ids'] = array_slice($rankedIds, 0, 20);
        $state['results_shown_count'] = count($page['items']);
        $state['search_ready'] = $status === 'results';

        return $state;
    }

    /**
     * @param array<int, array<string, mixed>> $visible
     * @param array<int, int> $rankedIds
     * @param array<int, array<string, mixed>> $scores
     * @return array{items: array<int, array<string, mixed>>, visible_reference_map: array<int, array<string, int>>}
     */
    public function visiblePageItems(array $visible, array $rankedIds, array $scores): array
    {
        $items = [];
        $map = [];

        foreach (array_slice($visible, 0, 5) as $index => $item) {
            $listingId = (int) ($item['id'] ?? 0);
            if ($listingId <= 0) {
                continue;
            }

            $rankPosition = array_search($listingId, $rankedIds, true);
            $score = $scores[$listingId] ?? null;
            $items[] = array_replace($this->formatter->formatListing($item, $score), [
                'position' => $index + 1,
                'rank_position' => $rankPosition === false ? null : $rankPosition + 1,
            ]);
            $map[] = [
                'position' => $index + 1,
                'listing_id' => $listingId,
            ];
        }

        return ['items' => $items, 'visible_reference_map' => $map];
    }

    /**
     * @param array<int, array<string, mixed>> $allRanked
     * @return array{state: array<string, mixed>, items: array<int, array<string, mixed>>, exhausted: bool}
     */
    public function nextPage(array $state, array $allRanked): array
    {
        $search = $state['search'] ?? [];
        $pageSize = (int) ($search['page_size'] ?? 5);
        $shown = (int) ($search['shown_count'] ?? 0);
        $slice = array_slice($allRanked, $shown, $pageSize);
        $visible = array_map(fn (array $item): array => $item['listing'] ?? $item, $slice);
        $rankedIds = array_map(static fn (array $item): int => (int) ($item['listing_id'] ?? $item['listing']['id'] ?? 0), $allRanked);
        $scores = array_column($allRanked, 'score', 'listing_id');
        $page = $this->visiblePageItems($visible, $rankedIds, $scores);

        $search['result_items'] = $page['items'];
        $search['visible_reference_map'] = $page['visible_reference_map'];
        $search['shown_count'] = $shown + count($page['items']);
        $search['has_more'] = $search['shown_count'] < count($allRanked);
        $search['status'] = $search['has_more'] ? 'results' : 'exhausted';
        $search['last_shown_at'] = date(DATE_ATOM);
        $state['search'] = $search;
        $state['shown_properties'] = $page['items'];
        $state['shown_properties_data'] = $page['items'];
        $state['results_shown_count'] = $search['shown_count'];

        return [
            'state' => $state,
            'items' => $page['items'],
            'exhausted' => ! $search['has_more'],
        ];
    }

    public function clear(array $state): array
    {
        $state['search'] = [
            'status' => 'not_ready',
            'search_id' => null,
            'criteria_digest' => null,
            'criteria_snapshot' => null,
            'ranked_listing_ids' => [],
            'ranking_scores' => [],
            'result_items' => [],
            'visible_reference_map' => [],
            'result_count' => 0,
            'shown_count' => 0,
            'page_size' => 5,
            'has_more' => false,
            'min_price_fallback' => null,
            'budget_fallback' => null,
            'last_shown_at' => null,
            'created_at' => null,
        ];
        $state['shown_properties'] = [];
        $state['shown_properties_data'] = [];
        $state['ranked_result_ids'] = [];
        $state['results_shown_count'] = 0;

        return $state;
    }

    /**
     * @return array<string, mixed>
     */
    public function supersedePageContext(array $state): array
    {
        if (isset($state['property_page_context']) && is_array($state['property_page_context'])) {
            $state['property_page_context']['status'] = 'superseded';
            $state['property_page_context']['applies_to_turn'] = false;
        }

        return $state;
    }
}
