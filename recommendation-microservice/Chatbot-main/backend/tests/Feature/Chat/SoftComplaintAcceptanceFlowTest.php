<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ComplaintStateService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class SoftComplaintAcceptanceFlowTest extends TestCase
{
    public function test_accepting_soft_offer_enters_full_complaint_flow(): void
    {
        $state = ChatTestFactory::sessionState(['complaint_case' => ChatTestFactory::complaintCase(['stage' => 'check_in'])]);
        $state = (new ComplaintStateService())->apply($state, ['intent' => 'complaint', 'flags' => ['complaint_help_accepted' => true]], 'yes please help with this issue');

        $this->assertSame('awaiting_issue', $state['complaint_case']['stage']);
    }
}
