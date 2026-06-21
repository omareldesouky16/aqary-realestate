<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ComplaintStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ComplaintIssueClarificationFlowTest extends TestCase
{
    public function test_empty_issue_keeps_awaiting_issue_stage(): void
    {
        $state = ChatTestFactory::sessionState(['complaint_case' => ChatTestFactory::complaintCase(['stage' => 'awaiting_issue'])]);
        $state = (new ComplaintStateService())->apply($state, ['intent' => 'complaint', 'flags' => []], 'bad');

        $this->assertSame('awaiting_issue', $state['complaint_case']['stage']);
        $this->assertSame('issue_clarification', $state['complaint_case']['last_event_type']);
    }
}
