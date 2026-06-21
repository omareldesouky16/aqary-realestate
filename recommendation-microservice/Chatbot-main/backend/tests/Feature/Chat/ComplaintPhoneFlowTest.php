<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ComplaintStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ComplaintPhoneFlowTest extends TestCase
{
    public function test_valid_phone_saves_complaint(): void
    {
        $state = ChatTestFactory::sessionState(['complaint_case' => ChatTestFactory::complaintCase(['stage' => 'awaiting_phone', 'issue_summary' => 'Bad results', 'phone_status' => 'pending'])]);
        $state = (new ComplaintStateService())->apply($state, ['intent' => 'complaint', 'flags' => []], '+201001234567');

        $this->assertSame('saved', $state['complaint_case']['stage']);
        $this->assertSame('+201001234567', $state['complaint_case']['follow_up_phone_normalized']);
    }
}
