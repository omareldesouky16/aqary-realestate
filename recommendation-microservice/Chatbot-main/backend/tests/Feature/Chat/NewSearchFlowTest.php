<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\SlotExtractor;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class NewSearchFlowTest extends TestCase
{
    public function test_location_change_after_results_starts_new_search(): void
    {
        $state = ChatTestFactory::sessionState([
            'shown_properties' => ChatTestFactory::shownProperties(),
            'slots' => ['propertyType' => 'apartment', 'location' => 'Cairo', 'features' => ['parking']],
        ]);

        $merged = (new SlotExtractor())->merge($state, [
            'slots' => ['location' => 'Sheikh Zayed'],
        ]);

        $this->assertSame('Sheikh Zayed', $merged['slots']['location']);
        $this->assertSame([], $merged['slots']['features']);
    }
}
