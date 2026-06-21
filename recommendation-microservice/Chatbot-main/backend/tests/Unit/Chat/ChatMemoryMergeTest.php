<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\PropertyDetailService;
use PHPUnit\Framework\TestCase;

class ChatMemoryMergeTest extends TestCase
{
    public function test_property_detail_omits_inference_and_reports_missing_fields(): void
    {
        $detail = (new PropertyDetailService())->detail([
            'id' => 42,
            'title' => 'Listing',
            'url' => '/listings/42',
            'price' => 1000000,
        ]);

        $this->assertSame(1000000, $detail['price']);
        $this->assertContains('area', $detail['missing_fields']);
        $this->assertArrayNotHasKey('seller_phone', $detail);
    }
}
