<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\IntentDetectionService;
use App\Services\Chat\NluResultValidator;
use App\Services\Chat\OpenRouterService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class InstallmentRedirectTest extends TestCase
{
    public function test_installment_intent_returns_cash_only_redirect_reply(): void
    {
        $service = new IntentDetectionService(new OpenRouterService(), new NluResultValidator());
        $reply = $service->replyFor(
            ['intent' => 'installment_redirect'],
            ChatTestFactory::sessionState(),
            []
        );

        $this->assertStringContainsString('cash', strtolower($reply));
    }
}
