<?php

namespace Tests\Support;

use App\Services\Chat\ResolutionCandidateService;

class ChatTestFactory
{
    public static function sessionState(array $overrides = []): array
    {
        return array_replace_recursive([
            'session_id' => '11111111-1111-4111-8111-111111111111',
            'slots' => [
                'propertyType' => 'apartment',
                'location' => 'Cairo',
                'location_id' => null,
                'price' => null,
                'area' => null,
                'bedrooms' => null,
                'bathrooms' => null,
                'features' => [],
            ],
            'shown_properties' => [],
            'shown_properties_data' => [],
            'property_reference' => null,
            'property_detail' => null,
            'property_gallery' => null,
            'seller_contact' => null,
            'property_page_context' => null,
            'detail_events' => [],
            'complaint_case' => null,
            'complaint_events' => [],
            'ranked_result_ids' => [],
            'results_shown_count' => 0,
            'search' => [
                'status' => 'not_ready',
                'search_id' => null,
                'criteria_digest' => null,
                'criteria_snapshot' => null,
                'ranked_listing_ids' => [],
                'ranking_scores' => [],
                'result_items' => [],
                'visible_reference_map' => [],
                'result_count' => 0,
                'shown_count' => 0,
                'page_size' => 5,
                'has_more' => false,
                'min_price_fallback' => null,
                'last_shown_at' => null,
                'created_at' => null,
            ],
            'context_property_id' => null,
            'failed_searches' => 0,
            'repeat_count' => 0,
            'slot_contradiction_count' => 0,
            'isComplaint' => false,
            'needsCheckIn' => false,
            'optional_collection_status' => 'not_asked',
            'clarification' => null,
            'search_ready' => false,
            'resolution' => [
                'outcomes' => [
                    'location' => null,
                    'propertyType' => null,
                    'features' => [],
                ],
                'pending_clarification' => null,
                'review_item_ids' => [],
            ],
            'language' => 'en',
        ], $overrides);
    }

    public static function shownProperties(): array
    {
        return [
            ['position' => 1, 'id' => 42, 'rank_position' => 1, 'title' => 'Luxury Apartment in Maadi', 'url' => 'https://example.test/listings/42', 'price' => 3200000, 'area' => 180, 'bedrooms' => 3, 'bathrooms' => 2, 'furnished_status' => 'Furnished', 'location' => 'Maadi', 'has_cover_image' => false, 'matched_features' => []],
            ['position' => 2, 'id' => 17, 'rank_position' => 2, 'title' => 'Modern Apartment in Nasr City', 'url' => 'https://example.test/listings/17', 'price' => 2800000, 'area' => 160, 'bedrooms' => 3, 'bathrooms' => 2, 'furnished_status' => 'Semi furnished', 'location' => 'Nasr City', 'has_cover_image' => true, 'matched_features' => ['security']],
            ['position' => 3, 'id' => 88, 'rank_position' => 3, 'title' => 'Family Apartment in New Cairo', 'url' => 'https://example.test/listings/88', 'price' => 4100000, 'area' => 220, 'bedrooms' => 4, 'bathrooms' => 3, 'furnished_status' => 'Unfurnished', 'location' => 'New Cairo', 'has_cover_image' => true, 'matched_features' => ['parking']],
        ];
    }

    public static function searchState(array $overrides = []): array
    {
        return array_replace_recursive([
            'status' => 'results',
            'search_id' => 'search-1',
            'criteria_digest' => 'digest-1',
            'criteria_snapshot' => [
                'session_id' => '11111111-1111-4111-8111-111111111111',
                'property_type_id' => 11,
                'property_type_name' => 'Apartment',
                'location_id' => 9,
                'location_name' => 'New Cairo',
                'max_budget' => 3000000,
                'budget_window_max' => 3600000,
                'area' => 150,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'feature_ids' => [21],
                'feature_names' => ['Security'],
                'language' => 'en',
                'search_ready' => true,
            ],
            'ranked_listing_ids' => [42, 17, 88],
            'ranking_scores' => [
                42 => ['total_score' => 93, 'price_score' => 55, 'area_score' => 12, 'bedroom_score' => 10, 'bathroom_score' => 8, 'feature_score' => 0, 'promotion_boost' => 0],
                17 => ['total_score' => 98, 'price_score' => 58, 'area_score' => 15, 'bedroom_score' => 10, 'bathroom_score' => 10, 'feature_score' => 5, 'promotion_boost' => 0],
            ],
            'result_items' => self::shownProperties(),
            'visible_reference_map' => [
                ['position' => 1, 'listing_id' => 42],
                ['position' => 2, 'listing_id' => 17],
                ['position' => 3, 'listing_id' => 88],
            ],
            'result_count' => 3,
            'shown_count' => 3,
            'page_size' => 5,
            'has_more' => false,
            'min_price_fallback' => null,
            'last_shown_at' => '2026-06-20T00:00:00+02:00',
            'created_at' => '2026-06-20T00:00:00+02:00',
        ], $overrides);
    }

    public static function searchResultItem(array $overrides = []): array
    {
        return array_replace_recursive([
            'id' => 42,
            'position' => 1,
            'rank_position' => 1,
            'title' => 'Luxury Apartment in Maadi',
            'url' => 'https://example.test/listings/42',
            'price' => 3200000,
            'area' => 180,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'furnished_status' => 'Furnished',
            'location' => 'Maadi',
            'cover_image_url' => 'https://example.test/covers/42.jpg',
            'has_cover_image' => true,
            'matched_features' => ['security'],
            'score' => ['total_score' => 93, 'price_score' => 55, 'feature_score' => 5, 'promotion_boost' => 0],
        ], $overrides);
    }

    public static function propertyDetail(array $overrides = []): array
    {
        return array_replace_recursive([
            'id' => 42,
            'title' => 'Luxury Apartment in Maadi',
            'url' => 'https://example.test/listings/42',
            'price' => 3200000,
            'area' => 180,
            'bedrooms' => 3,
            'bathrooms' => 2,
            'furnished_status' => 'Furnished',
            'location' => 'Maadi',
            'map_available' => false,
            'features' => ['security'],
            'missing_fields' => [],
        ], $overrides);
    }

    public static function propertyGallery(array $overrides = []): array
    {
        return array_replace_recursive([
            'property_id' => 42,
            'has_images' => true,
            'images' => [
                ['image_url' => 'https://example.test/covers/42.jpg', 'display_order' => 1, 'alt_text' => 'Luxury Apartment in Maadi'],
            ],
        ], $overrides);
    }

    public static function complaintCase(array $overrides = []): array
    {
        return array_replace_recursive([
            'status' => 'active',
            'stage' => 'awaiting_issue',
            'issue_summary' => null,
            'issue_language' => 'en',
            'follow_up_phone_raw' => null,
            'follow_up_phone_normalized' => null,
            'phone_status' => 'none',
            'follow_up_phone_attempts' => 0,
            'last_event_type' => 'started',
            'reviewable' => true,
            'events' => [],
            'updated_at' => '2026-06-20T00:00:00+02:00',
        ], $overrides);
    }

    public static function seedResolutionAliases(): void
    {
        ResolutionCandidateService::seedAliases([
            ['preference_type' => 'location', 'canonical_id' => 9, 'canonical_name' => 'New Cairo', 'alias' => 'Tagamoa', 'active' => true, 'display_order' => 1],
            ['preference_type' => 'propertyType', 'canonical_id' => 11, 'canonical_name' => 'Apartment', 'alias' => 'Flat', 'active' => true, 'display_order' => 1],
            ['preference_type' => 'features', 'canonical_id' => 21, 'canonical_name' => 'Security', 'alias' => 'Secure', 'active' => true, 'display_order' => 1],
        ]);
    }

    public static function resetResolutionAliases(): void
    {
        ResolutionCandidateService::resetAliases();
    }
}
