<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\ComplaintSignalService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class ComplaintSignalStateTest extends TestCase
{
    public function test_soft_signals_set_check_in_without_full_complaint(): void
    {
        $state = ChatTestFactory::sessionState(['repeat_count' => 2]);

        $result = (new ComplaintSignalService())->apply($state, []);

        $this->assertFalse($result['isComplaint']);
        $this->assertTrue($result['needsCheckIn']);
    }

    public function test_hard_signals_set_complaint(): void
    {
        $state = ChatTestFactory::sessionState();

        $result = (new ComplaintSignalService())->apply($state, ['explicit_complaint' => true]);

        $this->assertTrue($result['isComplaint']);
    }
}
