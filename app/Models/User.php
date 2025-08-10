<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'level',
        'points',
        'is_premium',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_premium' => 'boolean',
        'level' => 'integer',
        'points' => 'integer',
    ];

    /**
     * Get all memberships for the user
     */
    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    /**
     * Get the user's active membership
     */
    public function activeMembership()
    {
        return $this->hasOne(Membership::class)
            ->where('status', 'approved')
            ->where('end_date', '>', now())
            ->latest();
    }

    /**
     * Check if user has premium access
     */
    public function isPremium()
    {
        return $this->is_premium && $this->activeMembership()->exists();
    }

    /**
     * Get the profile image URL
     */
    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return asset('storage/profiles/' . $this->profile_image);
        }
        return null;
    }
}
