<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagedAlias extends Model
{
    protected $fillable = [
        'preference_type',
        'canonical_id',
        'canonical_name',
        'alias',
        'active',
    ];

    protected $casts = [
        'active' => 'bool',
    ];
}
