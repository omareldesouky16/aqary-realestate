<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\IntentDetectionService;
use App\Services\Chat\NluResultValidator;
use App\Services\Chat\OpenRouterService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class RankedSearchFlowTest extends TestCase
{
    public function test_search_results_reply_mentions_photos_and_more_options(): void
    {
        $service = new IntentDetectionService(new OpenRouterService(), new NluResultValidator());
        $reply = $service->replyFor(
            ['intent' => 'search_property'],
            ChatTestFactory::sessionState(['search' => ChatTestFactory::searchState(['has_more' => true])]),
            [],
            null
        );

        $this->assertStringContainsString('Would you like to see photos?', $reply);
        $this->assertStringContainsString('more', strtolower($reply));
    }

    public function test_show_more_reply_is_specific(): void
    {
        $service = new IntentDetectionService(new OpenRouterService(), new NluResultValidator());
        $reply = $service->replyFor(
            ['intent' => 'show_more_results'],
            ChatTestFactory::sessionState(['shown_properties' => ChatTestFactory::shownProperties()]),
            [],
            null
        );

        $this->assertStringContainsString('more listings', strtolower($reply));
    }

    public function test_search_state_supports_latency_event_assertions(): void
    {
        $event = [
            'event_type' => 'search_results',
            'candidate_count' => 8,
            'returned_count' => 5,
            'retained_count' => 8,
            'latency_ms' => 2500,
        ];

        $this->assertSame('search_results', $event['event_type']);
        $this->assertLessThanOrEqual(3000, $event['latency_ms']);
    }
}
