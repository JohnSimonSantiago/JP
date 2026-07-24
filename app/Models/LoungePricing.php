<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoungePricing extends Model
{
    protected $table = 'lounge_pricing';

    protected $fillable = [
        'hourly_rate',
        'half_hour_rate',
        'bundle_rate',
        'bundle_hours',
        'day_rate',
    ];

    protected $casts = [
        'hourly_rate'    => 'decimal:2',
        'half_hour_rate' => 'decimal:2',
        'bundle_rate'    => 'decimal:2',
        'day_rate'    => 'decimal:2',
    ];
}