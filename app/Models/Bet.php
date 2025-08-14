<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'opponent_id',
        'referee_id',
        'stars_amount',
        'status',
        'winner_id',
        'accepted_at',
        'completed_at'
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function opponent()
    {
        return $this->belongsTo(User::class, 'opponent_id');
    }

    public function referee()
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    // Scopes for different bet types
    public function scopeMyBets($query, $userId)
    {
        return $query->where('creator_id', $userId);
    }

    public function scopeIncomingBets($query, $userId)
    {
        return $query->where('opponent_id', $userId)->where('status', 'pending');
    }

    public function scopeRefereeBets($query, $userId)
    {
        return $query->where('referee_id', $userId)->where('status', 'accepted');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}