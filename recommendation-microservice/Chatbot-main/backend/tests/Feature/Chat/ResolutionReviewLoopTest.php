<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\LocationResolutionService;
use App\Services\Chat\ResolutionCandidateService;
use PHPUnit\Framework\TestCase;

class ResolutionReviewLoopTest extends TestCase
{
    protected function tearDown(): void
    {
        ResolutionCandidateService::resetAliases();
    }

    public function test_added_alias_improves_future_resolution_without_ui(): void
    {
        $service = new LocationResolutionService();
        $unresolved = $service->resolve('Future District');
        $this->assertSame('unresolved', $unresolved['status']);

        ResolutionCandidateService::seedAliases([
            ['preference_type' => 'location', 'canonical_id' => 55, 'canonical_name' => 'Future District', 'alias' => 'Future District', 'active' => true, 'display_order' => 1],
        ]);

        $resolved = $service->resolve('Future District');
        $this->assertSame('resolved', $resolved['status']);
        $this->assertSame('Future District', $resolved['canonical_name']);
    }
}
