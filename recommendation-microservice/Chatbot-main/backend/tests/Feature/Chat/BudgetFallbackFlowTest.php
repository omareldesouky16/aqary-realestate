<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\SearchResultStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class BudgetFallbackFlowTest extends TestCase
{
    public function test_budget_fallback_state_preserves_criteria_for_adjustment(): void
    {
        $service = new SearchResultStateService();
        $state = ChatTestFactory::sessionState(['search' => ChatTestFactory::searchState()]);

        $cleared = $service->clear($state);
        $cleared['search']['status'] = 'budget_fallback';
        $cleared['search']['criteria_snapshot'] = $state['search']['criteria_snapshot'];
        $cleared['search']['min_price_fallback'] = 1800000;
        $cleared['search']['budget_fallback'] = ['minimum_available_price' => 1800000];

        $this->assertSame('budget_fallback', $cleared['search']['status']);
        $this->assertSame('Apartment', $cleared['search']['criteria_snapshot']['property_type_name']);
        $this->assertSame(1800000, $cleared['search']['budget_fallback']['minimum_available_price']);
    }
}
