<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\LocationResolutionService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class LocationResolutionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatTestFactory::resetResolutionAliases();
    }

    public function test_exact_alias_and_ambiguous_location_phrases_are_resolved_or_flagged(): void
    {
        ChatTestFactory::seedResolutionAliases();
        $service = new LocationResolutionService();

        $resolved = $service->resolve('Tagamoa');
        $this->assertSame('resolved', $resolved['status']);
        $this->assertSame('New Cairo', $resolved['canonical_name']);

        $ambiguous = $service->resolve('New');
        $this->assertContains($ambiguous['status'], ['ambiguous', 'resolved']);
        $this->assertLessThanOrEqual(3, count($ambiguous['candidates']));
    }
}
