<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\ComplaintStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ComplaintStateServiceTest extends TestCase
{
    public function test_hard_complaint_starts_awaiting_issue(): void
    {
        $state = ChatTestFactory::sessionState(['isComplaint' => true]);
        $state = (new ComplaintStateService())->apply($state, ['intent' => 'complaint', 'flags' => []], 'I want to complain');

        $this->assertSame('awaiting_issue', $state['complaint_case']['stage']);
        $this->assertSame('started', $state['complaint_case']['last_event_type']);
    }

    public function test_issue_capture_advances_to_phone_request(): void
    {
        $state = ChatTestFactory::sessionState(['complaint_case' => ChatTestFactory::complaintCase()]);
        $state = (new ComplaintStateService())->apply($state, ['intent' => 'complaint', 'flags' => []], 'The search keeps returning wrong locations');

        $this->assertSame('awaiting_phone', $state['complaint_case']['stage']);
        $this->assertSame('pending', $state['complaint_case']['phone_status']);
        $this->assertStringContainsString('wrong locations', $state['complaint_case']['issue_summary']);
    }

    public function test_phone_transitions_save_or_preserve_complaint(): void
    {
        $service = new ComplaintStateService();
        $state = ChatTestFactory::sessionState(['complaint_case' => ChatTestFactory::complaintCase(['stage' => 'awaiting_phone', 'issue_summary' => 'Bad search', 'phone_status' => 'pending'])]);

        $invalid = $service->apply($state, ['intent' => 'complaint', 'flags' => []], '123');
        $saved = $service->apply($state, ['intent' => 'complaint', 'flags' => []], '01001234567');
        $declined = $service->apply($state, ['intent' => 'complaint', 'flags' => []], 'no thanks');

        $this->assertSame('invalid_phone_retry', $invalid['complaint_case']['stage']);
        $this->assertSame('+201001234567', $saved['complaint_case']['follow_up_phone_normalized']);
        $this->assertSame('declined', $declined['complaint_case']['phone_status']);
    }
}
