<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\ComplaintSignalService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ComplaintSignalServiceTest extends TestCase
{
    public function test_hard_and_soft_signals_are_distinguished(): void
    {
        $service = new ComplaintSignalService();

        $hard = $service->apply(ChatTestFactory::sessionState(), ['explicit_complaint' => true]);
        $failed = $service->apply(ChatTestFactory::sessionState(['failed_searches' => 3]), []);
        $soft = $service->apply(ChatTestFactory::sessionState(['repeat_count' => 2]), []);

        $this->assertTrue($hard['isComplaint']);
        $this->assertTrue($failed['isComplaint']);
        $this->assertFalse($soft['isComplaint']);
        $this->assertTrue($soft['needsCheckIn']);
    }
}
