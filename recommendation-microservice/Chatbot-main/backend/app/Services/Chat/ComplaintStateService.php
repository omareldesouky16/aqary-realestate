<?php

namespace App\Services\Chat;

class ComplaintStateService
{
    public function __construct(private readonly FollowUpPhoneService $phones = new FollowUpPhoneService())
    {
    }

    public function apply(array $state, array $nlu, string $message): array
    {
        $existingCase = $state['complaint_case'] ?? null;
        $case = $existingCase ?? $this->emptyCase();
        $hardComplaint = (bool) ($state['isComplaint'] ?? false);
        $acceptedHelp = (bool) ($nlu['flags']['complaint_help_accepted'] ?? false);

        if (($nlu['fallback'] ?? false) && $existingCase !== null && $this->isActive($case)) {
            return $this->withEvent($state, $this->fallback($case), 'fallback', 'Complaint fallback preserved current stage.');
        }

        if (($state['needsCheckIn'] ?? false) && ! $hardComplaint && ($case['stage'] ?? null) !== 'check_in') {
            $state = $this->withEvent($state, array_replace($case, ['status' => 'active', 'stage' => 'check_in']), 'soft_check_in', 'Soft follow-up offered.');
            $state['complaint_case'] ??= $existingCase;
            return $state;
        }

        if (($hardComplaint || $acceptedHelp) && in_array($case['stage'] ?? null, ['check_in', null], true)) {
            return $this->withEvent($state, array_replace($case, [
                'status' => 'active',
                'stage' => 'awaiting_issue',
                'phone_status' => 'none',
                'updated_at' => date(DATE_ATOM),
            ]), 'started', 'Complaint handling started.');
        }

        if (($case['stage'] ?? null) === 'check_in' && $acceptedHelp) {
            $state = $this->withEvent($state, array_replace($case, ['stage' => 'awaiting_issue', 'updated_at' => date(DATE_ATOM)]), 'started', 'Complaint help accepted.');
            $state['complaint_case'] ??= $existingCase;
            return $state;
        }

        if (($case['stage'] ?? null) === 'awaiting_issue') {
            $summary = $this->summary($message);
            if ($summary === null) {
                return $this->withEvent($state, $case, 'issue_clarification', 'Complaint issue needs clarification.');
            }

            $case = array_replace($case, [
                'stage' => 'awaiting_phone',
                'issue_summary' => $summary,
                'issue_language' => $state['language'] ?? null,
                'phone_status' => 'pending',
                'updated_at' => date(DATE_ATOM),
            ]);
            $state = $this->withEvent($state, $case, 'issue_captured', 'Complaint issue captured.');

            return $this->withEvent($state, $state['complaint_case'], 'phone_requested', 'Follow-up phone requested.');
        }

        if (in_array(($case['stage'] ?? null), ['awaiting_phone', 'invalid_phone_retry'], true)) {
            $phone = $this->phones->validate($message);
            if ($phone['declined']) {
                return $this->withEvent($state, array_replace($case, [
                    'status' => 'declined',
                    'stage' => 'declined',
                    'phone_status' => 'declined',
                    'updated_at' => date(DATE_ATOM),
                ]), 'phone_declined', 'Complaint saved without phone.');
            }

            if ($phone['valid']) {
                $state = $this->withEvent($state, array_replace($case, [
                    'status' => 'saved',
                    'stage' => 'saved',
                    'follow_up_phone_raw' => $message,
                    'follow_up_phone_normalized' => $phone['normalized'],
                    'phone_status' => 'valid',
                    'updated_at' => date(DATE_ATOM),
                ]), 'phone_accepted', 'Follow-up phone accepted.');

                return $this->withEvent($state, $state['complaint_case'], 'saved', 'Complaint saved.');
            }

            $attempts = (int) ($case['follow_up_phone_attempts'] ?? 0) + 1;
            return $this->withEvent($state, array_replace($case, [
                'stage' => 'invalid_phone_retry',
                'phone_status' => 'invalid',
                'follow_up_phone_attempts' => $attempts,
                'updated_at' => date(DATE_ATOM),
            ]), 'phone_invalid', 'Invalid phone rejected.');
        }

        if ($existingCase !== null) {
            $state['complaint_case'] = $case;
        }

        return $state;
    }

    public function emptyCase(): array
    {
        return [
            'status' => 'active',
            'stage' => 'check_in',
            'issue_summary' => null,
            'issue_language' => null,
            'follow_up_phone_raw' => null,
            'follow_up_phone_normalized' => null,
            'phone_status' => 'none',
            'follow_up_phone_attempts' => 0,
            'last_event_type' => null,
            'reviewable' => true,
            'events' => [],
            'updated_at' => date(DATE_ATOM),
        ];
    }

    private function isActive(array $case): bool
    {
        return in_array($case['stage'] ?? null, ['check_in', 'awaiting_issue', 'awaiting_phone', 'invalid_phone_retry'], true);
    }

    private function fallback(array $case): array
    {
        return array_replace($case, ['status' => 'fallback_pending', 'updated_at' => date(DATE_ATOM)]);
    }

    private function summary(string $message): ?string
    {
        $clean = trim(preg_replace('/\s+/', ' ', strip_tags($message)) ?? '');
        if (mb_strlen($clean) < 6) {
            return null;
        }

        return mb_substr($clean, 0, 240);
    }

    private function withEvent(array $state, array $case, string $type, string $message): array
    {
        $event = ['type' => $type, 'stage' => $case['stage'] ?? 'check_in', 'message' => $message, 'created_at' => date(DATE_ATOM), 'metadata' => []];
        $case['last_event_type'] = $type;
        $case['events'] = array_values(array_merge($case['events'] ?? [], [$event]));
        $state['complaint_case'] = $case;
        $state['complaint_events'] = array_values(array_merge($state['complaint_events'] ?? [], [$event]));

        return $state;
    }
}
