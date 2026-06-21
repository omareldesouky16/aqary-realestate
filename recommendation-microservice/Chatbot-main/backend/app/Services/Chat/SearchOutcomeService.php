<?php

namespace App\Services\Chat;

class SearchOutcomeService
{
    /**
     * @param array<string, mixed> $criteria
     * @return array<string, mixed>
     */
    public function searchResults(array $criteria, int $candidateCount, int $returnedCount, int $retainedCount, ?int $latencyMs = null): array
    {
        return [
            'event_type' => 'search_results',
            'criteria_snapshot' => $criteria,
            'candidate_count' => $candidateCount,
            'returned_count' => $returnedCount,
            'retained_count' => $retainedCount,
            'latency_ms' => $latencyMs,
            'fallback' => false,
        ];
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array<string, mixed>
     */
    public function budgetFallback(array $criteria, int $candidateCount, int $minimumAvailablePrice, ?int $latencyMs = null): array
    {
        return [
            'event_type' => 'budget_fallback',
            'criteria_snapshot' => $criteria,
            'candidate_count' => $candidateCount,
            'returned_count' => 0,
            'retained_count' => 0,
            'minimum_available_price' => $minimumAvailablePrice,
            'latency_ms' => $latencyMs,
            'fallback' => true,
        ];
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array<string, mixed>
     */
    public function noResults(array $criteria, int $candidateCount, ?int $latencyMs = null): array
    {
        return [
            'event_type' => 'no_results',
            'criteria_snapshot' => $criteria,
            'candidate_count' => $candidateCount,
            'returned_count' => 0,
            'retained_count' => 0,
            'latency_ms' => $latencyMs,
            'fallback' => false,
        ];
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array<string, mixed>
     */
    public function showMore(array $criteria, int $returnedCount, int $retainedCount, bool $exhausted): array
    {
        return [
            'event_type' => $exhausted ? 'show_more_exhausted' : 'show_more',
            'criteria_snapshot' => $criteria,
            'candidate_count' => $retainedCount,
            'returned_count' => $returnedCount,
            'retained_count' => $retainedCount,
            'fallback' => false,
        ];
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array<string, mixed>
     */
    public function replyFallback(array $criteria): array
    {
        return [
            'event_type' => 'reply_fallback',
            'criteria_snapshot' => $criteria,
            'candidate_count' => 0,
            'returned_count' => 0,
            'retained_count' => 0,
            'fallback' => true,
        ];
    }
}
