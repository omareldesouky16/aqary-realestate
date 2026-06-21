<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'property_type',
        'city',
        'region',
        'price',
        'area_sqm',
        'payment_type',
        'bedrooms',
        'bathrooms',
        'is_furnished',
        'features',
        'images',
        'timeslots',
        'views_count',
        'favorites_count',
        'seller_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'area_sqm' => 'integer',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'is_furnished' => 'boolean',
            'features' => 'array',
            'images' => 'array',
            'timeslots' => 'array',
            'views_count' => 'integer',
            'favorites_count' => 'integer',
            'seller_id' => 'integer',
        ];
    }

    /**
     * Get the seller (User) that owns the property.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the appointments associated with the property.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'property_id');
    }
}
