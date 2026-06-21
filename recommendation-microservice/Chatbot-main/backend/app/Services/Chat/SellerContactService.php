<?php

namespace App\Services\Chat;

use App\Models\ChatbotListing;
use Throwable;

class SellerContactService
{
    /**
     * @return array<string, mixed>
     */
    public function contact(array $property, bool $explicit): array
    {
        $propertyId = (int) ($property['id'] ?? 0);
        if (! $explicit) {
            return $this->withheld($propertyId, 'not_explicit');
        }

        try {
            $listing = ChatbotListing::query()->find($propertyId);
        } catch (Throwable) {
            $listing = null;
        }
        if ($listing instanceof ChatbotListing) {
            if ($listing->status !== 'active') {
                return $this->withheld($propertyId, 'inactive_property');
            }

            $phone = $listing->seller_phone;
        } else {
            $phone = $property['seller_phone'] ?? null;
        }

        if (! is_string($phone) || trim($phone) === '') {
            return $this->withheld($propertyId, 'missing_contact');
        }

        return [
            'property_id' => $propertyId,
            'requested_explicitly' => true,
            'contact_available' => true,
            'phone' => $phone,
            'withheld_reason' => null,
            'returned_at' => date(DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function withheld(int $propertyId, string $reason): array
    {
        return [
            'property_id' => $propertyId,
            'requested_explicitly' => false,
            'contact_available' => false,
            'phone' => null,
            'withheld_reason' => $reason,
            'returned_at' => date(DATE_ATOM),
        ];
    }
}
