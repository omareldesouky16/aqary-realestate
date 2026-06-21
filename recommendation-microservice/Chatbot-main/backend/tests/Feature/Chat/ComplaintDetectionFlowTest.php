<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\IntentDetectionService;
use App\Services\Chat\NluResultValidator;
use App\Services\Chat\OpenRouterService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ComplaintDetectionFlowTest extends TestCase
{
    public function test_complaint_reply_asks_for_issue_and_never_exposes_phone(): void
    {
        $service = new IntentDetectionService(new OpenRouterService(), new NluResultValidator());
        $reply = $service->replyFor(['intent' => 'complaint'], ChatTestFactory::sessionState(['complaint_case' => ChatTestFactory::complaintCase()]), []);

        $this->assertStringContainsString('what went wrong', strtolower($reply));
        $this->assertStringNotContainsString('010', $reply);
    }
}
