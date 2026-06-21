<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\IntentDetectionService;
use App\Services\Chat\NluResultValidator;
use App\Services\Chat\OpenRouterService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class BudgetFallbackServiceTest extends TestCase
{
    public function test_budget_fallback_reply_includes_minimum_price_and_adjustment_prompt(): void
    {
        $service = new IntentDetectionService(new OpenRouterService(), new NluResultValidator());
        $state = ChatTestFactory::sessionState([
            'search' => ChatTestFactory::searchState([
                'status' => 'budget_fallback',
                'min_price_fallback' => 1800000,
                'budget_fallback' => [
                    'minimum_available_price' => 1800000,
                    'scope_location' => 'New Cairo',
                    'scope_property_type' => 'Apartment',
                    'stated_max_budget' => 1000000,
                    'available_listing_count_in_scope' => 3,
                ],
                'result_items' => [],
                'has_more' => false,
            ]),
        ]);

        $reply = $service->replyFor(['intent' => 'search_property'], $state, []);

        $this->assertStringContainsString('EGP 1800000', $reply);
        $this->assertStringContainsString('increase the budget', strtolower($reply));
    }
}
