<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payout extends Model
{
    protected $fillable = [
        'shop_id',
        'amount',
        'order_count',
        'processed_by',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'order_count' => 'integer',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // The completed orders this payout covered
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}