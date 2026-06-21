<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\IntentDetectionService;
use App\Services\Chat\NluResultValidator;
use App\Services\Chat\OpenRouterService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ShowMoreWithoutSearchFlowTest extends TestCase
{
    public function test_show_more_without_prior_results_asks_for_search_preferences(): void
    {
        $service = new IntentDetectionService(new OpenRouterService(), new NluResultValidator());
        $reply = $service->replyFor(['intent' => 'show_more_results'], ChatTestFactory::sessionState(), []);

        $this->assertStringContainsString('property type, location, and budget', strtolower($reply));
    }
}
