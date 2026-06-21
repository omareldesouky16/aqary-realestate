<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChatbotListingFeature extends Model
{
    protected $table = 'chatbot_listing_features';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function listings(): BelongsToMany
    {
        return $this->belongsToMany(ChatbotListing::class, 'chatbot_listing_feature', 'feature_id', 'listing_id');
    }
}
