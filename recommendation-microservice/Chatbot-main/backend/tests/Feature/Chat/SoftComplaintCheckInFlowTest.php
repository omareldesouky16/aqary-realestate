<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ComplaintSignalService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class SoftComplaintCheckInFlowTest extends TestCase
{
    public function test_soft_check_in_does_not_request_phone(): void
    {
        $state = (new ComplaintSignalService())->apply(ChatTestFactory::sessionState(['repeat_count' => 2]), []);

        $this->assertTrue($state['needsCheckIn']);
        $this->assertFalse($state['isComplaint']);
    }
}
