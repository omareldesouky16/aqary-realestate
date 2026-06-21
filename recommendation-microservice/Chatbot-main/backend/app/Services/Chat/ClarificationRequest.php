<?php

namespace App\Services\Chat;

class ClarificationRequest
{
    /**
     * @param  array<int, string>  $candidateValues
     */
    public function __construct(
        public readonly string $slotName,
        public readonly string $reason,
        public readonly ?string $rawText = null,
        public readonly array $candidateValues = [],
    ) {
    }

    public function toArray(): array
    {
        $payload = [
            'slot_name' => $this->slotName,
            'reason' => $this->reason,
        ];

        if ($this->rawText !== null) {
            $payload['raw_text'] = $this->rawText;
        }

        if ($this->candidateValues !== []) {
            $payload['candidate_values'] = array_values($this->candidateValues);
        }

        return $payload;
    }
}
