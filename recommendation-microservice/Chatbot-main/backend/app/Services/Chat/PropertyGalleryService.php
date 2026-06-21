<?php

namespace App\Services\Chat;

class PropertyGalleryService
{
    /**
     * @return array<string, mixed>
     */
    public function gallery(array $property): array
    {
        $images = [];
        foreach (array_values($property['gallery_images'] ?? []) as $index => $image) {
            $url = is_array($image) ? ($image['image_url'] ?? $image['url'] ?? null) : $image;
            if (! is_string($url) || $url === '') {
                continue;
            }

            $images[] = [
                'image_url' => $url,
                'display_order' => $index + 1,
                'alt_text' => is_array($image) ? ($image['alt_text'] ?? null) : null,
            ];
        }

        if ($images === [] && ! empty($property['cover_image_url'])) {
            $images[] = [
                'image_url' => (string) $property['cover_image_url'],
                'display_order' => 1,
                'alt_text' => $property['title'] ?? null,
            ];
        }

        return [
            'property_id' => (int) ($property['id'] ?? 0),
            'has_images' => $images !== [],
            'images' => $images,
        ];
    }
}
