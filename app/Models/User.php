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
        'password',
        'level',
        'points',
        'is_premium',
        'role',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_premium' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Existing relationships...
    public function sentTrades()
    {
        return $this->hasMany(Trade::class, 'sender_id');
    }

    public function receivedTrades()
    {
        return $this->hasMany(Trade::class, 'receiver_id');
    }

    public function createdBets()
    {
        return $this->hasMany(Bet::class, 'creator_id');
    }

    public function opponentBets()
    {
        return $this->hasMany(Bet::class, 'opponent_id');
    }

    public function refereeBets()
    {
        return $this->hasMany(Bet::class, 'referee_id');
    }

    public function wonBets()
    {
        return $this->hasMany(Bet::class, 'winner_id');
    }

    // New shop relationships
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function canAfford($amount)
    {
        return $this->points >= $amount;
    }

    public function spendPoints($amount)
    {
        if (!$this->canAfford($amount)) {
            throw new \Exception('Insufficient points');
        }
        
        $this->decrement('points', $amount);
        return $this;
    }

    public function earnPoints($amount)
    {
        $this->increment('points', $amount);
        return $this;
    }
}