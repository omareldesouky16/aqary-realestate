<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\NluResultValidator;
use Tests\TestCase;

class IntentRoutingTest extends TestCase
{
    public function test_search_chitchat_and_unclear_intents_are_valid(): void
    {
        $validator = new NluResultValidator();

        $this->assertSame('search_property', $validator->validate(['intent' => 'search_property'])['intent']);
        $this->assertSame('chitchat', $validator->validate(['intent' => 'chitchat'])['intent']);
        $this->assertSame('unclear', $validator->validate(['intent' => 'unclear'])['intent']);
    }
}
