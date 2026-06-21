<?php

namespace App\Services\Chat;

class FollowUpPhoneService
{
    /**
     * @return array{valid: bool, normalized: string|null, declined: bool}
     */
    public function validate(string $input): array
    {
        $normalizedInput = strtolower(trim($input));
        if (preg_match('/\b(no|no thanks|later|decline|skip|مش|لا)\b/u', $normalizedInput) === 1) {
            return ['valid' => false, 'normalized' => null, 'declined' => true];
        }

        $digits = preg_replace('/\D+/', '', $input) ?? '';
        if (str_starts_with($digits, '0020')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '20')) {
            $local = '0' . substr($digits, 2);
        } else {
            $local = $digits;
        }

        if (preg_match('/^01[0125]\d{8}$/', $local) !== 1) {
            return ['valid' => false, 'normalized' => null, 'declined' => false];
        }

        return ['valid' => true, 'normalized' => '+20' . substr($local, 1), 'declined' => false];
    }
}
