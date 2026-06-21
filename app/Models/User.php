<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_verified' => 'boolean',
        ];
    }

    /**
     * Get the properties owned by the user (seller).
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'seller_id');
    }

    /**
     * Get the appointments booked by the user (buyer).
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'buyer_id');
    }

    /**
     * Determine if the user can access the admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Option A: Use your role column (Recommended)
        // return $this->role === 'admin';
        
        // Option B: Hardcode the email you just created in Tinker
        return true;
    }
}