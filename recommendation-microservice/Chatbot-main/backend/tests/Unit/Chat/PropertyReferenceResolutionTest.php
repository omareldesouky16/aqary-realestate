<?php

namespace Tests\Unit\Chat;

use App\Services\Chat\PropertyGalleryService;
use App\Services\Chat\PropertyReferenceResolver;
use App\Services\Chat\SellerContactService;
use PHPUnit\Framework\TestCase;
use Tests\Support\ChatTestFactory;

class PropertyReferenceResolutionTest extends TestCase
{
    public function test_position_and_title_references_resolve_against_current_visible_page(): void
    {
        $resolver = new PropertyReferenceResolver();
        $state = ChatTestFactory::sessionState(['shown_properties' => ChatTestFactory::shownProperties()]);

        $byPosition = $resolver->resolve($state, ['user_reference' => 'the second one']);
        $byTitle = $resolver->resolve($state, ['user_reference' => 'family apartment']);

        $this->assertSame(17, $byPosition['id']);
        $this->assertSame('position', $byPosition['resolved_by']);
        $this->assertSame(88, $byTitle['id']);
        $this->assertSame('title_match', $byTitle['resolved_by']);
    }

    public function test_ambiguous_or_missing_reference_returns_current_options(): void
    {
        $resolver = new PropertyReferenceResolver();
        $state = ChatTestFactory::sessionState(['shown_properties' => ChatTestFactory::shownProperties()]);

        $result = $resolver->resolve($state, ['user_reference' => 'that apartment']);

        $this->assertSame('ambiguous', $result['status']);
        $this->assertCount(3, $result['property_reference']['candidates']);
    }

    public function test_gallery_and_contact_services_gate_single_property_outputs(): void
    {
        $property = ChatTestFactory::searchResultItem(['seller_phone' => '01000000000']);
        $gallery = (new PropertyGalleryService())->gallery($property);
        $contact = (new SellerContactService())->contact($property, true);

        $this->assertTrue($gallery['has_images']);
        $this->assertSame('01000000000', $contact['phone']);
    }
}
