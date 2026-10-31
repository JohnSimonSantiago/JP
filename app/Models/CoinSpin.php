<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinSpin extends Model
{
    protected $fillable = [
        'user_id',
        'member_name',
        'amount',
        'new_cash',
        'admin_id',
        'admin_name',
    ];

    protected $casts = [
        'amount'   => 'integer',
        'new_cash' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}