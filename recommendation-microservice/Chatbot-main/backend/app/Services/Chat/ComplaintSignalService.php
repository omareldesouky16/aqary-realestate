<?php

namespace App\Services\Chat;

class ComplaintSignalService
{
    public function apply(array $state, array $flags): array
    {
        $explicit = (bool) ($flags['explicit_complaint'] ?? false);
        $frustrated = (bool) ($flags['frustration_detected'] ?? false);
        $failedSearches = (int) ($state['failed_searches'] ?? 0);
        $repeatCount = (int) ($state['repeat_count'] ?? 0);
        $contradictions = (int) ($state['slot_contradiction_count'] ?? 0);

        $accepted = (bool) ($flags['complaint_help_accepted'] ?? false);
        $isComplaint = $explicit || $frustrated || $accepted || $failedSearches >= 3;
        $needsCheckIn = ! $isComplaint && ($repeatCount >= 2 || $contradictions >= 3);

        $state['isComplaint'] = $isComplaint;
        $state['needsCheckIn'] = $needsCheckIn;
        $state['complaint_signals'] = [
            'explicit_complaint' => $explicit,
            'frustration_detected' => $frustrated,
            'failed_searches' => $failedSearches,
            'repeat_count' => $repeatCount,
            'slot_contradiction_count' => $contradictions,
            'complaint_help_accepted' => $accepted,
            'needs_check_in' => $needsCheckIn,
            'is_complaint' => $isComplaint,
        ];

        return $state;
    }
}
