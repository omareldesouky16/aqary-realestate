<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ComplaintStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ComplaintPhoneRetryFlowTest extends TestCase
{
    public function test_invalid_phone_remains_unconfirmed_and_decline_preserves_issue(): void
    {
        $state = ChatTestFactory::sessionState(['complaint_case' => ChatTestFactory::complaintCase(['stage' => 'awaiting_phone', 'issue_summary' => 'Bad results', 'phone_status' => 'pending'])]);

        $invalid = (new ComplaintStateService())->apply($state, ['intent' => 'complaint', 'flags' => []], '123');
        $declined = (new ComplaintStateService())->apply($state, ['intent' => 'complaint', 'flags' => []], 'no thanks');

        $this->assertNull($invalid['complaint_case']['follow_up_phone_normalized']);
        $this->assertSame('Bad results', $declined['complaint_case']['issue_summary']);
    }
}
