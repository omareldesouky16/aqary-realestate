<?php

namespace App\Services\Chat;

class FeatureResolutionService
{
    public function __construct(
        private readonly ResolutionCandidateService $candidates = new ResolutionCandidateService(),
        private readonly ResolutionNormalizer $normalizer = new ResolutionNormalizer(),
    ) {
    }

    public function resolve(string|array $rawText): array
    {
        $phrases = is_array($rawText) ? $rawText : $this->normalizer->phrases($rawText);
        $resolved = [];
        $outcomes = [];
        $reviewable = [];

        foreach ($phrases as $phrase) {
            $outcome = $this->candidates->resolveOne('features', $phrase);
            $outcomes[] = $outcome;

            if ($outcome['status'] === 'resolved') {
                $resolved[] = $outcome['canonical_name'];
            } else {
                $reviewable[] = $outcome;
            }
        }

        return [
            'resolved' => array_values(array_unique($resolved)),
            'outcomes' => $outcomes,
            'reviewable' => $reviewable,
        ];
    }
}
