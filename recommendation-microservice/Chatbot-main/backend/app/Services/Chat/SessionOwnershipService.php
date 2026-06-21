<?php

namespace App\Services\Chat;

use App\Models\ChatSession;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SessionOwnershipService
{
    public function verifyOrCreate(string $sessionId, int $userId): ChatSession
    {
        if (! Str::isUuid($sessionId)) {
            throw new InvalidArgumentException('Malformed chat session identifier.');
        }

        $session = ChatSession::query()->whereKey($sessionId)->first();

        if ($session === null) {
            return ChatSession::query()->create([
                'session_id' => $sessionId,
                'user_id' => $userId,
            ]);
        }

        if ((int) $session->user_id !== $userId) {
            throw new AuthorizationException('This chat session is not available.');
        }

        return $session;
    }
}
