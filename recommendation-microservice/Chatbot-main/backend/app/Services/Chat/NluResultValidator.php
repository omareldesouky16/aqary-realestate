<?php

namespace App\Services\Chat;

use InvalidArgumentException;

class NluResultValidator
{
    public const INTENTS = [
        'search_property',
        'show_more_results',
        'property_details',
        'show_property_photos',
        'seller_contact',
        'complaint',
        'installment_redirect',
        'chitchat',
        'unclear',
        'system_error',
    ];

    public function validate(array $payload): array
    {
        $intent = $payload['intent'] ?? 'unclear';

        if (! in_array($intent, self::INTENTS, true)) {
            throw new InvalidArgumentException('Unsupported NLU intent.');
        }

        return [
            'intent' => $intent,
            'slots' => $this->cleanSlots($payload['slots'] ?? []),
            'flags' => $this->cleanFlags($payload['flags'] ?? []),
            'resolved_property_id' => $payload['resolved_property_id'] ?? null,
            'resolved_by' => $payload['resolved_by'] ?? null,
            'user_reference' => $payload['user_reference'] ?? null,
            'language' => $payload['language'] ?? null,
            'new_search_requested' => (bool) ($payload['new_search_requested'] ?? false),
            'search' => $this->cleanSearch($payload['search'] ?? null),
            'optional_collection_status' => $payload['optional_collection_status'] ?? null,
            'clarification' => $this->cleanClarification($payload['clarification'] ?? null),
            'resolution' => $this->cleanResolution($payload['resolution'] ?? null),
        ];
    }

    private function cleanSlots(array $slots): array
    {
        $allowed = ['propertyType', 'location', 'location_id', 'price', 'area', 'bedrooms', 'bathrooms', 'features'];

        return array_filter(
            array_intersect_key($slots, array_flip($allowed)),
            fn ($value): bool => $value !== null && $value !== ''
        );
    }

    private function cleanFlags(array $flags): array
    {
        return [
            'explicit_complaint' => (bool) ($flags['explicit_complaint'] ?? false),
            'frustration_detected' => (bool) ($flags['frustration_detected'] ?? false),
            'show_more_requested' => (bool) ($flags['show_more_requested'] ?? false),
            'cash_accepted' => (bool) ($flags['cash_accepted'] ?? false),
            'cash_declined' => (bool) ($flags['cash_declined'] ?? false),
            'photo_requested' => (bool) ($flags['photo_requested'] ?? false),
            'contact_requested' => (bool) ($flags['contact_requested'] ?? false),
            'complaint_help_accepted' => (bool) ($flags['complaint_help_accepted'] ?? false),
            'contact_declined' => (bool) ($flags['contact_declined'] ?? false),
        ];
    }

    private function cleanClarification(mixed $clarification): ?array
    {
        if (! is_array($clarification)) {
            return null;
        }

        $slotName = (string) ($clarification['slot_name'] ?? '');
        $reason = (string) ($clarification['reason'] ?? '');

        if ($slotName === '' || $reason === '') {
            return null;
        }

        return [
            'slot_name' => $slotName,
            'reason' => $reason,
            'raw_text' => isset($clarification['raw_text']) ? (string) $clarification['raw_text'] : null,
            'candidate_values' => array_values(array_map('strval', $clarification['candidate_values'] ?? [])),
        ];
    }

    private function cleanResolution(mixed $resolution): ?array
    {
        if (! is_array($resolution)) {
            return null;
        }

        $outcomes = $resolution['outcomes'] ?? [];
        $pending = $resolution['pending_clarification'] ?? null;

        return [
            'outcomes' => is_array($outcomes) ? $outcomes : [],
            'pending_clarification' => is_array($pending) ? $this->cleanClarification($pending) : null,
            'review_item_ids' => array_values(array_map('intval', $resolution['review_item_ids'] ?? [])),
        ];
    }

    private function cleanSearch(mixed $search): ?array
    {
        if (! is_array($search)) {
            return null;
        }

        return [
            'show_more_requested' => (bool) ($search['show_more_requested'] ?? false),
            'core_change_requested' => (bool) ($search['core_change_requested'] ?? false),
            'refinement_requested' => (bool) ($search['refinement_requested'] ?? false),
            'reset_requested' => (bool) ($search['reset_requested'] ?? false),
            'page_request' => (string) ($search['page_request'] ?? ''),
        ];
    }
}
