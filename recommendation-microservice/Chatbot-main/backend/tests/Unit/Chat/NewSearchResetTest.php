<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\SlotExtractor;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class NewSearchResetTest extends TestCase
{
    public function test_explicit_new_search_resets_search_specific_state_and_keeps_counters(): void
    {
        $state = ChatTestFactory::sessionState([
            'shown_properties' => ChatTestFactory::shownProperties(),
            'failed_searches' => 2,
        ]);

        $merged = (new SlotExtractor())->merge($state, [
            'new_search_requested' => true,
            'slots' => ['propertyType' => 'villa'],
        ]);

        $this->assertSame('villa', $merged['slots']['propertyType']);
        $this->assertSame([], $merged['shown_properties']);
        $this->assertSame(2, $merged['failed_searches']);
    }
}
