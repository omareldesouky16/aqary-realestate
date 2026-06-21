<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\SlotExtractor;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class SearchResetFlowTest extends TestCase
{
    public function test_position_reference_uses_current_visible_page(): void
    {
        $state = ChatTestFactory::sessionState(['shown_properties' => [
            ChatTestFactory::searchResultItem(['id' => 6, 'position' => 1]),
        ]]);

        $resolved = (new SlotExtractor())->resolvePropertyReference($state, ['user_reference' => 'the first one']);

        $this->assertSame(6, $resolved['id']);
        $this->assertSame('position', $resolved['resolved_by']);
    }
}
