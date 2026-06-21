<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ComplaintStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ComplaintBlocksNormalFlowTest extends TestCase
{
    public function test_active_complaint_state_indicates_normal_flow_should_stop(): void
    {
        $state = (new ComplaintStateService())->apply(ChatTestFactory::sessionState(['isComplaint' => true]), ['intent' => 'complaint', 'flags' => []], 'I want to complain');

        $this->assertSame('awaiting_issue', $state['complaint_case']['stage']);
        $this->assertTrue(in_array($state['complaint_case']['stage'], ['awaiting_issue', 'awaiting_phone', 'invalid_phone_retry'], true));
    }
}
