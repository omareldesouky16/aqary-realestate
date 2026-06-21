<?php

namespace App\Services\Chat;

class ResolutionReviewService
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $items = [];

    public function record(string $sessionId, string $preferenceType, array $outcome, ?string $buyerChoice = null): array
    {
        $item = [
            'id' => count(self::$items) + 1,
            'session_id' => $sessionId,
            'preference_type' => $preferenceType,
            'status' => (string) $outcome['status'],
            'raw_text' => $outcome['raw_text'] ?? null,
            'candidates' => $outcome['candidates'] ?? [],
            'canonical_id' => $outcome['canonical_id'] ?? null,
            'canonical_name' => $outcome['canonical_name'] ?? null,
            'buyer_choice' => $buyerChoice,
            'metadata' => [
                'resolved_by' => $outcome['resolved_by'] ?? null,
                'optional_blocking' => $outcome['optional_blocking'] ?? true,
            ],
        ];

        self::$items[] = $item;

        return $item;
    }

    public function all(): array
    {
        return self::$items;
    }

    public function reset(): void
    {
        self::$items = [];
    }
}
