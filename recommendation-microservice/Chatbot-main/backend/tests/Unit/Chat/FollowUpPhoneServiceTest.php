<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\FollowUpPhoneService;
use PHPUnit\Framework\TestCase;

class FollowUpPhoneServiceTest extends TestCase
{
    public function test_egyptian_phone_numbers_are_normalized(): void
    {
        $service = new FollowUpPhoneService();

        $this->assertSame('+201001234567', $service->validate('01001234567')['normalized']);
        $this->assertSame('+201001234567', $service->validate('+201001234567')['normalized']);
        $this->assertSame('+201001234567', $service->validate('00201001234567')['normalized']);
    }

    public function test_invalid_and_declined_phone_are_not_confirmed(): void
    {
        $service = new FollowUpPhoneService();

        $this->assertFalse($service->validate('12345')['valid']);
        $this->assertTrue($service->validate('no thanks')['declined']);
    }
}
