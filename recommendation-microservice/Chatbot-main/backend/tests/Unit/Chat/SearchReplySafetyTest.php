<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\SearchReplyFormatter;
use PHPUnit\Framework\TestCase;

class SearchReplySafetyTest extends TestCase
{
    public function test_formatter_excludes_phone_and_omits_missing_fields(): void
    {
        $formatted = (new SearchReplyFormatter())->formatListing([
            'id' => 42,
            'title' => '<script>ignore</script> Apartment',
            'url' => '/properties/42',
            'location_name' => 'Maadi',
            'seller_phone' => '01000000000',
        ], ['matched_feature_names' => ['Security']]);

        $this->assertArrayNotHasKey('seller_phone', $formatted);
        $this->assertArrayNotHasKey('price', $formatted);
        $this->assertSame(['Security'], $formatted['matched_features']);
    }
}
