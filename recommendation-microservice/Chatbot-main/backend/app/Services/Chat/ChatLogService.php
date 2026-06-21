<?php

namespace App\Services\Chat;

use App\Models\ChatLog;
use Illuminate\Support\Collection;

class ChatLogService
{
    public function __construct(private readonly int $historyLimit = 10)
    {
    }

    public function recentTurns(string $sessionId): Collection
    {
        $limit = (int) config('chat.history_limit', $this->historyLimit);

        return ChatLog::query()
            ->where('session_id', $sessionId)
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    public function latestState(string $sessionId): array
    {
        $latest = ChatLog::query()
            ->where('session_id', $sessionId)
            ->whereNotNull('extracted_data')
            ->latest('created_at')
            ->first();

        $state = array_replace_recursive(SlotExtractor::emptyState($sessionId), $latest?->extracted_data ?? []);

        return SlotCollectionState::hydrate($state);
    }

    public function record(string $sessionId, string $role, string $message, ?string $intent, array $state): ChatLog
    {
        return ChatLog::query()->create([
            'session_id' => $sessionId,
            'role' => $role,
            'message' => $message,
            'intent_detected' => $intent,
            'extracted_data' => SlotCollectionState::hydrate($state),
        ]);
    }

    /**
     * @param array<string, mixed> $state
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    public function recordSearchEvent(array $state, array $event): array
    {
        $state['search_events'] = array_values(array_merge($state['search_events'] ?? [], [$event]));

        return $state;
    }

    /**
     * @param array<string, mixed> $state
     * @param array<string, mixed> $event
     * @return array<string, mixed>
     */
    public function recordDetailEvent(array $state, array $event): array
    {
        if (isset($event['seller_phone'])) {
            unset($event['seller_phone']);
        }

        $state['detail_events'] = array_values(array_merge($state['detail_events'] ?? [], [$event]));

        return $state;
    }

    public function recordComplaintEvent(array $state, array $event): array
    {
        if (isset($event['follow_up_phone_raw'])) {
            unset($event['follow_up_phone_raw']);
        }

        $state['complaint_events'] = array_values(array_merge($state['complaint_events'] ?? [], [$event]));

        return $state;
    }

    /**
     * @param array<string, mixed> $state
     * @param array<string, mixed> $criteria
     * @return array<string, mixed>
     */
    public function withSearchCriteria(array $state, array $criteria): array
    {
        $state['search']['criteria_snapshot'] = $criteria;
        $state['search']['criteria_digest'] = hash('sha256', json_encode($criteria, JSON_THROW_ON_ERROR));

        return $state;
    }

    public function promptHistory(string $sessionId): array
    {
        return $this->recentTurns($sessionId)
            ->map(fn (ChatLog $turn): array => [
                'role' => $turn->role,
                'message' => $turn->message,
                'intent' => $turn->intent_detected,
            ])
            ->all();
    }
}
