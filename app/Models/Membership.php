<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'type',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user that owns the membership
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if membership is active
     */
    public function isActive()
    {
        return $this->status === 'approved' && 
               $this->end_date > Carbon::now();
    }

    /**
     * Check if membership is expired
     */
    public function isExpired()
    {
        return $this->end_date <= Carbon::now();
    }

    /**
     * Get days remaining in membership
     */
    public function getDaysRemainingAttribute()
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return Carbon::now()->diffInDays($this->end_date, false);
    }

    /**
     * Get membership duration in days
     */
    public function getDurationAttribute()
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Scope for active memberships
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'approved')
                    ->where('end_date', '>', Carbon::now());
    }

    /**
     * Scope for expired memberships
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<=', Carbon::now());
    }

    /**
     * Auto-expire memberships when accessed
     */
    protected static function booted()
    {
        static::retrieved(function ($membership) {
            if ($membership->status === 'approved' && $membership->isExpired()) {
                $membership->update(['status' => 'expired']);
                $membership->user->update(['is_premium' => false]);
            }
        });
    }
}