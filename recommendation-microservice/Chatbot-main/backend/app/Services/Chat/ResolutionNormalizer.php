<?php

namespace App\Services\Chat;

use Illuminate\Support\Str;

class ResolutionNormalizer
{
    public function normalize(string $value): string
    {
        $value = Str::of($value)->lower()->trim()->replaceMatches('/[^\pL\pN]+/u', ' ');
        $value = $value->replaceMatches('/\s+/u', ' ')->trim();

        return (string) $value;
    }

    public function phrases(string $value): array
    {
        $parts = preg_split('/[,\n;\/|]+|(?:\s+(?:and|or|و|او)\s+)/iu', $value) ?: [$value];

        return array_values(array_filter(array_map(fn (string $part): string => trim($part), $parts)));
    }
}
