<?php

namespace Tests\Feature\Chat;

use App\Services\Chat\ChatLogService;
use App\Services\Chat\IntentDetectionService;
use App\Services\Chat\NluResultValidator;
use App\Services\Chat\OpenRouterService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class PropertyReferenceFlowTest extends TestCase
{
    public function test_detail_reply_uses_resolved_property_payload(): void
    {
        $service = new IntentDetectionService(new OpenRouterService(), new NluResultValidator());
        $state = ChatTestFactory::sessionState(['property_detail' => ChatTestFactory::propertyDetail()]);

        $reply = $service->replyFor(['intent' => 'property_details'], $state, [], ['id' => 42]);

        $this->assertStringContainsString('Luxury Apartment', $reply);
        $this->assertStringNotContainsString('010', $reply);
    }

    public function test_ambiguous_reference_reply_asks_for_clarification(): void
    {
        $service = new IntentDetectionService(new OpenRouterService(), new NluResultValidator());
        $state = ChatTestFactory::sessionState([
            'property_reference' => [
                'status' => 'ambiguous',
                'candidates' => [],
                'clarification_prompt' => 'Which property do you mean?',
            ],
        ]);

        $reply = $service->replyFor(['intent' => 'property_details'], $state, ['property_reference_clarification']);

        $this->assertStringContainsString('Which property', $reply);
    }

    public function test_detail_events_do_not_store_seller_phone(): void
    {
        $state = (new ChatLogService())->recordDetailEvent(ChatTestFactory::sessionState(), [
            'event_type' => 'contact_returned',
            'property_id' => 42,
            'seller_phone' => '01000000000',
            'contact_returned' => true,
        ]);

        $this->assertArrayNotHasKey('seller_phone', $state['detail_events'][0]);
    }
}
