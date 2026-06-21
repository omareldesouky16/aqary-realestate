<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResolutionReviewItem extends Model
{
    protected $fillable = [
        'session_id',
        'preference_type',
        'status',
        'raw_text',
        'candidates',
        'canonical_id',
        'canonical_name',
        'buyer_choice',
        'metadata',
    ];

    protected $casts = [
        'candidates' => 'array',
        'metadata' => 'array',
    ];
}
