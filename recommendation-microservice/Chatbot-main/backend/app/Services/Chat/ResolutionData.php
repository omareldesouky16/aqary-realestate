<?php

namespace App\Services\Chat;

class ResolutionData
{
    public static function emptyState(): array
    {
        return [
            'outcomes' => [
                'location' => null,
                'propertyType' => null,
                'features' => [],
            ],
            'pending_clarification' => null,
            'review_item_ids' => [],
        ];
    }

    public static function candidate(array $row, string $preferenceType, string $matchReason, int $displayOrder): array
    {
        return [
            'canonical_id' => (int) $row['canonical_id'],
            'canonical_name' => (string) $row['canonical_name'],
            'preference_type' => $preferenceType,
            'match_reason' => $matchReason,
            'display_order' => $displayOrder,
        ];
    }

    public static function outcome(string $preferenceType, string $status, ?string $rawText, ?int $canonicalId = null, ?string $canonicalName = null, ?string $resolvedBy = null, array $candidates = [], bool $optionalBlocking = true): array
    {
        return [
            'preference_type' => $preferenceType,
            'status' => $status,
            'raw_text' => $rawText,
            'canonical_id' => $canonicalId,
            'canonical_name' => $canonicalName,
            'resolved_by' => $resolvedBy,
            'candidates' => array_slice(array_values($candidates), 0, 3),
            'optional_blocking' => $optionalBlocking,
        ];
    }

    public static function clarification(string $preferenceType, string $reason, ?string $rawText, array $candidates): array
    {
        return [
            'preference_type' => $preferenceType,
            'reason' => $reason,
            'raw_text' => $rawText,
            'candidates' => array_slice(array_values($candidates), 0, 3),
        ];
    }
}
