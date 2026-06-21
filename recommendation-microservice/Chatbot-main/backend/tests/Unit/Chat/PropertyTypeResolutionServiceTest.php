<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\PropertyTypeResolutionService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class PropertyTypeResolutionServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatTestFactory::resetResolutionAliases();
    }

    public function test_property_type_synonyms_resolve_to_supported_categories(): void
    {
        ChatTestFactory::seedResolutionAliases();
        $service = new PropertyTypeResolutionService();

        $resolved = $service->resolve('flat');
        $this->assertSame('resolved', $resolved['status']);
        $this->assertSame('Apartment', $resolved['canonical_name']);
    }

    public function test_unsupported_property_type_stays_unresolved(): void
    {
        $service = new PropertyTypeResolutionService();
        $unresolved = $service->resolve('castle');

        $this->assertSame('unresolved', $unresolved['status']);
    }
}
