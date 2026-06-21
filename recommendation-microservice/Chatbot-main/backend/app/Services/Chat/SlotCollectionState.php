<?php

namespace App\Services\Chat;

class SlotCollectionState
{
    private const REQUIRED_ORDER = ['propertyType', 'location', 'price'];
    private const OPTIONAL_ORDER = ['area', 'bedrooms', 'bathrooms', 'features'];

    public static function build(array $state): array
    {
        $slots = $state['slots'] ?? [];
        $resolution = $state['resolution'] ?? ResolutionData::emptyState();
        $requiredSlots = [];
        $optionalSlots = [];
        $missingRequiredSlots = [];

        foreach (self::REQUIRED_ORDER as $slotName) {
            $value = $slots[$slotName] ?? null;
            $slotValue = self::makeValue($slotName, $value);
            $requiredSlots[$slotName] = $slotValue->toArray();

            if ($slotValue->status !== 'complete') {
                $missingRequiredSlots[] = $slotName;
            }
        }

        foreach (self::OPTIONAL_ORDER as $slotName) {
            $optionalSlots[$slotName] = self::makeValue($slotName, $slots[$slotName] ?? null)->toArray();
        }

        $optionalStatus = (string) ($state['optional_collection_status'] ?? 'not_asked');
        if ($missingRequiredSlots !== []) {
            $nextQuestionSlot = $missingRequiredSlots[0];
        } elseif (! in_array($optionalStatus, ['answered', 'declined', 'skipped'], true)) {
            $nextQuestionSlot = 'optional_preferences';
        } else {
            $nextQuestionSlot = null;
        }

        $searchReady = $missingRequiredSlots === [] && in_array($optionalStatus, ['answered', 'declined', 'skipped'], true);
        if (($resolution['pending_clarification'] ?? null) !== null && (($resolution['pending_clarification']['preference_type'] ?? null) !== 'features')) {
            $searchReady = false;
        }

        foreach (['location', 'propertyType'] as $preferenceType) {
            $outcome = $resolution['outcomes'][$preferenceType] ?? null;
            if ((! is_array($outcome) || ($outcome['status'] ?? null) !== 'resolved') && ! empty($slots[$preferenceType])) {
                $searchReady = false;
            }
        }

        $budgetCurrency = null;
        if (($slots['price'] ?? null) !== null && ! is_array($slots['price']) && is_numeric($slots['price'])) {
            $budgetCurrency = 'EGP';
        } elseif (is_array($slots['price'] ?? null) && isset($slots['price']['currency'])) {
            $budgetCurrency = (string) $slots['price']['currency'];
        }

        return [
            'required_slots' => $requiredSlots,
            'optional_slots' => $optionalSlots,
            'missing_required_slots' => $missingRequiredSlots,
            'next_question_slot' => $resolution['pending_clarification'] !== null ? 'resolution_clarification' : $nextQuestionSlot,
            'optional_collection_status' => $optionalStatus,
            'search_ready' => $searchReady,
            'budget_currency' => $budgetCurrency,
            'resolution' => $resolution,
            'clarification' => isset($state['clarification']) && is_array($state['clarification'])
                ? (new ClarificationRequest(
                    (string) ($state['clarification']['slot_name'] ?? ''),
                    (string) ($state['clarification']['reason'] ?? 'unclear'),
                    isset($state['clarification']['raw_text']) ? (string) $state['clarification']['raw_text'] : null,
                    array_values(array_map('strval', $state['clarification']['candidate_values'] ?? [])),
                ))->toArray()
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function hydrate(array $state): array
    {
        $state['slot_collection'] = self::build($state);

        return $state;
    }

    /**
     * @return array<string, mixed>
     */
    private static function makeValue(string $slotName, mixed $value): SlotValue
    {
        if ($value === null || $value === '') {
            return SlotValue::missing();
        }

        if ($slotName === 'price' && is_numeric($value)) {
            return SlotValue::complete((int) $value, (string) $value, 'EGP');
        }

        if (is_array($value) && $value === []) {
            return SlotValue::missing();
        }

        return SlotValue::complete($value, is_scalar($value) ? (string) $value : null);
    }
}
