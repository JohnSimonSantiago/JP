<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsumablePurchase extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'minutes_added',
        'payment_method',
        'purchased_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purchasedBy()
    {
        return $this->belongsTo(User::class, 'purchased_by');
    }
}