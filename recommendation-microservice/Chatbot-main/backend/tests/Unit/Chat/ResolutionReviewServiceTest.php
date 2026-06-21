<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\ResolutionReviewService;
use PHPUnit\Framework\TestCase;

class ResolutionReviewServiceTest extends TestCase
{
    public function test_review_items_are_recorded_with_minimal_metadata(): void
    {
        $service = new ResolutionReviewService();
        $service->reset();

        $item = $service->record('11111111-1111-4111-8111-111111111111', 'location', [
            'status' => 'ambiguous',
            'raw_text' => 'New',
            'candidates' => [],
        ]);

        $this->assertSame(1, $item['id']);
        $this->assertSame('location', $item['preference_type']);
        $this->assertSame('ambiguous', $item['status']);
    }
}
