<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\SessionOwnershipService;
use InvalidArgumentException;
use Tests\TestCase;

class SessionOwnershipTest extends TestCase
{
    public function test_malformed_uuid_is_rejected_before_session_lookup(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SessionOwnershipService())->verifyOrCreate('not-a-uuid', 1);
    }
}
