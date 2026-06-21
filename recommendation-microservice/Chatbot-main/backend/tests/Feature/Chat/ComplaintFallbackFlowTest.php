<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ComplaintStateService;
use App\Services\Chat\IntentDetectionService;
use App\Services\Chat\NluResultValidator;
use App\Services\Chat\OpenRouterService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ComplaintFallbackFlowTest extends TestCase
{
    public function test_fallback_preserves_complaint_stage(): void
    {
        $state = ChatTestFactory::sessionState(['complaint_case' => ChatTestFactory::complaintCase(['stage' => 'awaiting_phone', 'issue_summary' => 'Bad results'])]);
        $state = (new ComplaintStateService())->apply($state, ['intent' => 'system_error', 'fallback' => true, 'flags' => []], 'anything');
        $reply = (new IntentDetectionService(new OpenRouterService(), new NluResultValidator()))->replyFor(['intent' => 'system_error', 'fallback' => true], $state, []);

        $this->assertSame('awaiting_phone', $state['complaint_case']['stage']);
        $this->assertStringContainsString('complaint progress', $reply);
    }
}
