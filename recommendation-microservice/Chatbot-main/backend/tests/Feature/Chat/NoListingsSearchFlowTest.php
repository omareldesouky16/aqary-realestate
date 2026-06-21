<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\IntentDetectionService;
use App\Services\Chat\NluResultValidator;
use App\Services\Chat\OpenRouterService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class NoListingsSearchFlowTest extends TestCase
{
    public function test_no_listings_reply_offers_scope_adjustment_without_properties(): void
    {
        $service = new IntentDetectionService(new OpenRouterService(), new NluResultValidator());
        $state = ChatTestFactory::sessionState([
            'search' => ChatTestFactory::searchState(['status' => 'no_results', 'result_items' => [], 'has_more' => false]),
            'shown_properties' => [],
        ]);

        $reply = $service->replyFor(['intent' => 'search_property'], $state, []);

        $this->assertStringContainsString('could not find active cash listings', strtolower($reply));
        $this->assertStringContainsString('location or property type', strtolower($reply));
    }
}
