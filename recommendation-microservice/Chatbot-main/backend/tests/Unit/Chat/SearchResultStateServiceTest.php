<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\SearchResultStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class SearchResultStateServiceTest extends TestCase
{
    public function test_store_persists_visible_results_and_honors_pagination(): void
    {
        $service = new SearchResultStateService();
        $state = ChatTestFactory::sessionState(['search' => ChatTestFactory::searchState()]);
        $criteria = new \App\Services\Chat\SearchData(
            '11111111-1111-4111-8111-111111111111',
            11,
            'Apartment',
            9,
            'New Cairo',
            3000000,
            3600000,
            150,
            3,
            2,
            [21],
            ['Security'],
            'en'
        );
        $ranked = [
            ['listing_id' => 42, 'listing' => ChatTestFactory::searchResultItem(['id' => 42, 'position' => 1, 'rank_position' => 1])],
            ['listing_id' => 17, 'listing' => ChatTestFactory::searchResultItem(['id' => 17, 'position' => 2, 'rank_position' => 2])],
            ['listing_id' => 88, 'listing' => ChatTestFactory::searchResultItem(['id' => 88, 'position' => 3, 'rank_position' => 3])],
        ];

        $stored = $service->store($state, $criteria, $ranked, array_column($ranked, 'listing'), 'results');

        $this->assertSame(3, $stored['search']['result_count']);
        $this->assertSame(3, $stored['search']['shown_count']);
        $this->assertCount(3, $stored['shown_properties']);
    }
}
