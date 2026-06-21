<?php

namespace Tests\Support;

class SlotCollectionStateFactory
{
    public static function state(array $overrides = []): array
    {
        return array_replace_recursive([
            'session_id' => '11111111-1111-4111-8111-111111111111',
            'slots' => [
                'propertyType' => null,
                'location' => null,
                'location_id' => null,
                'price' => null,
                'area' => null,
                'bedrooms' => null,
                'bathrooms' => null,
                'features' => [],
            ],
            'optional_collection_status' => 'not_asked',
            'clarification' => null,
            'search_ready' => false,
            'isComplaint' => false,
            'needsCheckIn' => false,
            'language' => 'en',
        ], $overrides);
    }
}
