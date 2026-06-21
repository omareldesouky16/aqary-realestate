<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChatbotListing extends Model
{
    protected $table = 'chatbot_listings';

    protected $fillable = [
        'title',
        'url',
        'price',
        'area',
        'bedrooms',
        'bathrooms',
        'furnished_status',
        'location_id',
        'location_name',
        'property_type_id',
        'cover_image_url',
        'is_promoted',
        'status',
        'payment_type',
        'seller_phone',
    ];

    protected $casts = [
        'price' => 'integer',
        'area' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'location_id' => 'integer',
        'property_type_id' => 'integer',
        'is_promoted' => 'boolean',
    ];

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(ChatbotListingFeature::class, 'chatbot_listing_feature', 'listing_id', 'feature_id');
    }
}
