<?php

namespace App\Services\Chat;

class SlotExtractor
{
    public static function emptyState(string $sessionId): array
    {
        return [
            'session_id' => $sessionId,
            'slots' => [
                'propertyType' => null,
                'location' => null,
                'location_id' => null,
                'price' => null,
                'area' => null,
                'bedrooms' => null,
                'bathrooms' => null,
                'features' => [],
            ],
            'shown_properties' => [],
            'shown_properties_data' => [],
            'ranked_result_ids' => [],
            'results_shown_count' => 0,
            'search' => [
                'status' => 'not_ready',
                'search_id' => null,
                'criteria_digest' => null,
                'criteria_snapshot' => null,
                'ranked_listing_ids' => [],
                'ranking_scores' => [],
                'result_items' => [],
                'visible_reference_map' => [],
                'result_count' => 0,
                'shown_count' => 0,
                'page_size' => 5,
                'has_more' => false,
                'min_price_fallback' => null,
                'last_shown_at' => null,
                'created_at' => null,
            ],
            'context_property_id' => null,
            'failed_searches' => 0,
            'repeat_count' => 0,
            'slot_contradiction_count' => 0,
            'isComplaint' => false,
            'needsCheckIn' => false,
            'complaint_case' => null,
            'complaint_events' => [],
            'optional_collection_status' => 'not_asked',
            'clarification' => null,
            'search_ready' => false,
            'language' => null,
            'new_search_requested' => false,
        ];
    }

    public function merge(array $state, array $nlu): array
    {
        $state = array_replace_recursive(self::emptyState($state['session_id'] ?? ''), $state);

        if ($this->shouldResetSearch($state, $nlu)) {
            $state = $this->resetSearchSpecificState($state);
            $state['new_search_requested'] = true;
        }

        foreach (($nlu['slots'] ?? []) as $slot => $value) {
            if ($value === null || $value === '' || $this->isPaymentSlot($slot)) {
                continue;
            }

            if (array_key_exists($slot, $state['slots'])) {
                $state['slots'][$slot] = $value;
            }
        }

        if (isset($nlu['clarification']) && is_array($nlu['clarification'])) {
            $state['clarification'] = $nlu['clarification'];
        }

        if (isset($state['clarification']['slot_name'])) {
            $clarifiedSlot = (string) $state['clarification']['slot_name'];
            if (($nlu['slots'][$clarifiedSlot] ?? null) !== null && $nlu['slots'][$clarifiedSlot] !== '') {
                $state['clarification'] = null;
            }
        }

        if (isset($nlu['optional_collection_status'])) {
            $state['optional_collection_status'] = (string) $nlu['optional_collection_status'];
        }

        $state['search_ready'] = false;

        if (! empty($nlu['language'])) {
            $state['language'] = $nlu['language'];
        }

        return $state;
    }

    public function awaitingSlots(array $state): array
    {
        $collection = SlotCollectionState::build($state);
        $missing = $collection['missing_required_slots'];

        if ($missing !== []) {
            return [$missing[0]];
        }

        if ($collection['next_question_slot'] === 'optional_preferences') {
            return ['optional_preferences'];
        }

        return [];
    }

    public function shownProperties(array $state): array
    {
        return array_values($state['shown_properties'] ?? []);
    }

    public function resolvePropertyReference(array $state, array $nlu): array
    {
        $shown = $this->shownProperties($state);
        $id = $nlu['resolved_property_id'] ?? null;

        if ($id !== null) {
            foreach ($shown as $property) {
                if ((int) ($property['id'] ?? 0) === (int) $id) {
                    return ['id' => (int) $id, 'resolved_by' => $nlu['resolved_by'] ?? 'id_explicit'];
                }
            }
        }

        $reference = strtolower((string) ($nlu['user_reference'] ?? ''));
        $position = $this->positionFromReference($reference);
        if ($position !== null) {
            foreach ($shown as $property) {
                if ((int) ($property['position'] ?? 0) === $position) {
                    return ['id' => (int) $property['id'], 'resolved_by' => 'position'];
                }
            }
        }

        foreach ($shown as $property) {
            $title = strtolower((string) ($property['title'] ?? ''));
            if ($reference !== '' && $title !== '' && str_contains($title, $reference)) {
                return ['id' => (int) $property['id'], 'resolved_by' => 'title_match'];
            }
        }

        return ['id' => null, 'resolved_by' => null];
    }

    private function shouldResetSearch(array $state, array $nlu): bool
    {
        if ((bool) ($nlu['new_search_requested'] ?? false)) {
            return true;
        }

        if (empty($state['shown_properties'])) {
            return false;
        }

        $slots = $nlu['slots'] ?? [];
        $currentType = $state['slots']['propertyType'] ?? null;
        $currentLocation = $state['slots']['location_id'] ?? ($state['slots']['location'] ?? null);

        return (! empty($slots['propertyType']) && $currentType && $slots['propertyType'] !== $currentType)
            || (! empty($slots['location_id']) && $currentLocation && $slots['location_id'] !== $currentLocation)
            || (! empty($slots['location']) && $currentLocation && $slots['location'] !== $currentLocation);
    }

    private function resetSearchSpecificState(array $state): array
    {
        $sessionCounters = [
            'failed_searches' => $state['failed_searches'] ?? 0,
            'repeat_count' => $state['repeat_count'] ?? 0,
            'slot_contradiction_count' => $state['slot_contradiction_count'] ?? 0,
            'isComplaint' => $state['isComplaint'] ?? false,
            'needsCheckIn' => $state['needsCheckIn'] ?? false,
            'language' => $state['language'] ?? null,
        ];

        $reset = array_replace(self::emptyState((string) $state['session_id']), $sessionCounters);
        $reset['search']['status'] = 'not_ready';

        return $reset;
    }

    private function isPaymentSlot(string $slot): bool
    {
        return in_array($slot, ['paymentMethod', 'installment', 'downPayment', 'monthlyPayment'], true);
    }

    private function positionFromReference(string $reference): ?int
    {
        return match (true) {
            preg_match('/\b(2|second|two)\b/', $reference) === 1 => 2,
            preg_match('/\b(3|third|three)\b/', $reference) === 1 => 3,
            preg_match('/\b(4|fourth|four)\b/', $reference) === 1 => 4,
            preg_match('/\b(5|fifth|five)\b/', $reference) === 1 => 5,
            preg_match('/\b(1|first|one)\b/', $reference) === 1 => 1,
            default => null,
        };
    }
}
