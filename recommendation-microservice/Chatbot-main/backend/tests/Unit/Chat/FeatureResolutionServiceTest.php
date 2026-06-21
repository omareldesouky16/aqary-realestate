<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\FeatureResolutionService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class FeatureResolutionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatTestFactory::resetResolutionAliases();
    }

    public function test_multiple_clear_features_are_retained_and_unclear_ones_do_not_block(): void
    {
        ChatTestFactory::seedResolutionAliases();
        $service = new FeatureResolutionService();

        $result = $service->resolve('security, parking, unknown feature');

        $this->assertContains('Security', $result['resolved']);
        $this->assertContains('Parking', $result['resolved']);
        $this->assertGreaterThanOrEqual(1, count($result['outcomes']));
    }
}
