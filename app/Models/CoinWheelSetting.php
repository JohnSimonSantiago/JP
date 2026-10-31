<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinWheelSetting extends Model
{
    protected $fillable = ['is_enabled', 'prizes'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'prizes'     => 'array',
    ];

    /** The one and only settings row. */
    public static function current(): self
    {
        return static::query()->firstOrFail();
    }
}