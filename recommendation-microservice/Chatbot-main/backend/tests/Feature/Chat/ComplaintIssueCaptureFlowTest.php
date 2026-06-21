<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ComplaintStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ComplaintIssueCaptureFlowTest extends TestCase
{
    public function test_issue_capture_moves_to_phone_request(): void
    {
        $state = ChatTestFactory::sessionState(['complaint_case' => ChatTestFactory::complaintCase(['stage' => 'awaiting_issue'])]);
        $state = (new ComplaintStateService())->apply($state, ['intent' => 'complaint', 'flags' => []], 'The chatbot keeps showing properties in the wrong area');

        $this->assertSame('awaiting_phone', $state['complaint_case']['stage']);
        $this->assertSame('phone_requested', $state['complaint_case']['last_event_type']);
    }
}
