<?php

namespace App\Services\Chat;

class SlotValue
{
    public function __construct(
        public readonly mixed $value,
        public readonly ?string $rawText = null,
        public readonly ?string $currency = null,
        public readonly string $status = 'complete',
    ) {
    }

    public static function complete(mixed $value, ?string $rawText = null, ?string $currency = null): self
    {
        return new self($value, $rawText, $currency, 'complete');
    }

    public static function missing(): self
    {
        return new self(null, null, null, 'missing');
    }

    public static function unclear(?string $rawText = null): self
    {
        return new self(null, $rawText, null, 'unclear');
    }

    public static function ambiguous(?string $rawText = null): self
    {
        return new self(null, $rawText, null, 'ambiguous');
    }

    public static function declined(?string $rawText = null): self
    {
        return new self(null, $rawText, null, 'declined');
    }

    public function toArray(): array
    {
        $payload = [
            'value' => $this->value,
            'status' => $this->status,
        ];

        if ($this->rawText !== null) {
            $payload['raw_text'] = $this->rawText;
        }

        if ($this->currency !== null) {
            $payload['currency'] = $this->currency;
        }

        return $payload;
    }
}
