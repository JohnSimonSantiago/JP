<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoungeSession extends Model
{
    protected $fillable = [
        'customer_name',
        'school',
        'user_id',
        'customer_type',
        'user_level',
        'checked_in_at',
        'checked_out_at',
        'status',
        'total_bill',
        'is_free',
        'checked_in_by',
        'group_id',
        'billing_mode',
        'notified_thresholds',
        'receipt_number',
    ];

    protected $casts = [
        'checked_in_at'        => 'datetime',
        'checked_out_at'       => 'datetime',
        'is_free'              => 'boolean',
        'total_bill'           => 'decimal:2',
        'notified_thresholds'  => 'array',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}