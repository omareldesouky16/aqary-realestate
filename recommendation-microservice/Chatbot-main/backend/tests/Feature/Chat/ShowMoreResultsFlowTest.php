<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\SearchResultStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ShowMoreResultsFlowTest extends TestCase
{
    public function test_next_page_replaces_visible_references_without_repeating_previous_page(): void
    {
        $service = new SearchResultStateService();
        $state = ChatTestFactory::sessionState([
            'search' => ChatTestFactory::searchState([
                'ranked_listing_ids' => [1, 2, 3, 4, 5, 6],
                'shown_count' => 5,
                'has_more' => true,
            ]),
        ]);
        $ranked = [];
        foreach ([1, 2, 3, 4, 5, 6] as $id) {
            $ranked[] = ['listing_id' => $id, 'listing' => ChatTestFactory::searchResultItem(['id' => $id, 'title' => 'Listing ' . $id])];
        }

        $page = $service->nextPage($state, $ranked);

        $this->assertSame([6], array_column($page['items'], 'id'));
        $this->assertSame([['position' => 1, 'listing_id' => 6]], $page['state']['search']['visible_reference_map']);
        $this->assertTrue($page['exhausted']);
    }
}
