<?php

namespace App\Services\Chat;

class LocationResolutionService
{
    public function __construct(private readonly ResolutionCandidateService $candidates = new ResolutionCandidateService())
    {
    }

    public function resolve(string $rawText): array
    {
        return $this->candidates->resolveOne('location', $rawText);
    }
}
