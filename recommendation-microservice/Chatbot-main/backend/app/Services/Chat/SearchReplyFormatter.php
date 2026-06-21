<?php

namespace App\Services\Chat;

class SearchReplyFormatter
{
    /**
     * @param array<string, mixed>|null $score
     * @return array<string, mixed>
     */
    public function formatListing(array $listing, ?array $score = null): array
    {
        $item = [
            'id' => (int) ($listing['id'] ?? 0),
            'title' => (string) ($listing['title'] ?? ''),
            'url' => (string) ($listing['url'] ?? ''),
            'price' => $this->optionalInt($listing['price'] ?? null),
            'area' => $this->optionalInt($listing['area'] ?? null),
            'bedrooms' => $this->optionalInt($listing['bedrooms'] ?? null),
            'bathrooms' => $this->optionalInt($listing['bathrooms'] ?? null),
            'furnished_status' => $listing['furnished_status'] ?? null,
            'location' => $listing['location'] ?? ($listing['location_name'] ?? null),
            'cover_image_url' => $listing['cover_image_url'] ?? null,
            'has_cover_image' => ! empty($listing['cover_image_url']),
            'matched_features' => array_values($score['matched_feature_names'] ?? $listing['matched_features'] ?? []),
        ];

        if ($score !== null) {
            $item['score'] = $score;
        }

        return array_filter($item, static fn ($value): bool => $value !== null);
    }

    private function optionalInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
