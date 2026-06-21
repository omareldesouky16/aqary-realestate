<?php

namespace App\Services\Chat;

class ResolutionCandidateService
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $seededAliases = [];

    public function __construct(
        private readonly ResolutionNormalizer $normalizer = new ResolutionNormalizer(),
    ) {
    }

    public static function seedAliases(array $aliases): void
    {
        self::$seededAliases = $aliases;
    }

    public static function resetAliases(): void
    {
        self::$seededAliases = [];
    }

    public function resolveOne(string $preferenceType, string $rawText): array
    {
        $rawText = trim($rawText);
        $normalized = $this->normalizer->normalize($rawText);
        $candidates = $this->candidatesFor($preferenceType, $normalized);

        if ($candidates === []) {
            return ResolutionData::outcome($preferenceType, 'unresolved', $rawText, null, null, null, [], $preferenceType !== 'features');
        }

        usort($candidates, static fn (array $left, array $right): int => ($right['score'] <=> $left['score']) ?: ($left['display_order'] <=> $right['display_order']));
        $best = $candidates[0];

        if ($preferenceType === 'location' && mb_strlen($normalized) < 4 && $best['score'] < 100) {
            return ResolutionData::outcome($preferenceType, 'ambiguous', $rawText, null, null, null, array_slice($candidates, 0, 3), true);
        }

        if ($best['score'] < 60) {
            return ResolutionData::outcome($preferenceType, 'unresolved', $rawText, null, null, null, array_slice($candidates, 0, 3), $preferenceType !== 'features');
        }

        if (count($candidates) > 1 && $best['score'] === $candidates[1]['score']) {
            return ResolutionData::outcome($preferenceType, 'ambiguous', $rawText, null, null, null, array_slice($candidates, 0, 3), $preferenceType !== 'features');
        }

        return ResolutionData::outcome(
            $preferenceType,
            'resolved',
            $rawText,
            (int) $best['canonical_id'],
            (string) $best['canonical_name'],
            (string) $best['match_reason'],
            array_slice($candidates, 0, 3),
            $preferenceType !== 'features'
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function candidatesFor(string $preferenceType, string $normalizedPhrase): array
    {
        $rows = $this->recordsFor($preferenceType);
        $candidates = [];

        foreach ($rows as $row) {
            $aliasNormalized = $this->normalizer->normalize((string) $row['alias']);
            $canonicalNormalized = $this->normalizer->normalize((string) $row['canonical_name']);

            $score = 0;
            $reason = null;

            if ($normalizedPhrase === $aliasNormalized || $normalizedPhrase === $canonicalNormalized) {
                $score = 100;
                $reason = $normalizedPhrase === $canonicalNormalized ? 'exact' : 'alias';
            } elseif (mb_strlen($normalizedPhrase) >= 4 && (str_contains($aliasNormalized, $normalizedPhrase) || str_contains($normalizedPhrase, $aliasNormalized))) {
                $score = 85;
                $reason = 'synonym';
            } else {
                similar_text($normalizedPhrase, $aliasNormalized, $percentAlias);
                similar_text($normalizedPhrase, $canonicalNormalized, $percentCanonical);
                $score = (int) max($percentAlias, $percentCanonical);
                $reason = $score >= 70 ? 'similarity' : null;
            }

            if ($score > 0) {
                $candidates[] = ResolutionData::candidate([
                    'canonical_id' => $row['canonical_id'],
                    'canonical_name' => $row['canonical_name'],
                ], $preferenceType, $reason ?? 'similarity', (int) ($row['display_order'] ?? 1)) + ['score' => $score];
            }
        }

        $bestByCanonical = [];
        foreach ($candidates as $candidate) {
            $key = $this->normalizer->normalize((string) $candidate['canonical_name']);
            if (! isset($bestByCanonical[$key]) || $candidate['score'] > $bestByCanonical[$key]['score']) {
                $bestByCanonical[$key] = $candidate;
            }
        }

        $candidates = array_values($bestByCanonical);
        usort($candidates, static fn (array $left, array $right): int => ($right['score'] <=> $left['score']) ?: ($left['display_order'] <=> $right['display_order']));

        return array_slice(array_values($candidates), 0, 3);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recordsFor(string $preferenceType): array
    {
        $records = [];
        foreach ($this->baseRecords() as $row) {
            if ($row['preference_type'] === $preferenceType && ($row['active'] ?? true)) {
                $records[] = $row;
            }
        }

        foreach (self::$seededAliases as $row) {
            if (($row['preference_type'] ?? null) === $preferenceType && ($row['active'] ?? true)) {
                $records[] = $row;
            }
        }

        return $records;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function baseRecords(): array
    {
        $config = $this->loadConfig();
        $records = [];

        foreach (['locations', 'property_types', 'features'] as $group) {
            foreach (array_values($config[$group] ?? []) as $index => $row) {
                $preferenceType = $group === 'property_types' ? 'propertyType' : ($group === 'locations' ? 'location' : 'features');
                $aliases = array_values(array_unique(array_merge([(string) ($row['canonical_name'] ?? '')], $row['aliases'] ?? [$row['alias'] ?? ''])));

                foreach ($aliases as $alias) {
                    if ($alias === '') {
                        continue;
                    }

                    $records[] = [
                        'preference_type' => $preferenceType,
                        'canonical_id' => $row['canonical_id'],
                        'canonical_name' => $row['canonical_name'],
                        'alias' => $alias,
                        'active' => $row['active'] ?? true,
                        'display_order' => $row['display_order'] ?? $index + 1,
                    ];
                }
            }
        }

        return $records;
    }

    /**
     * @return array{locations: array<int, array<string, mixed>>, property_types: array<int, array<string, mixed>>, features: array<int, array<string, mixed>>}
     */
    private function loadConfig(): array
    {
        $path = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'resolution.php';
        if (is_file($path)) {
            $config = require $path;
            if (is_array($config)) {
                return $config + ['locations' => [], 'property_types' => [], 'features' => []];
            }
        }

        return ['locations' => [], 'property_types' => [], 'features' => []];
    }
}
