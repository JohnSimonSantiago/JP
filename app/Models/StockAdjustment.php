<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = [
        'shop_id',
        'shop_item_id',
        'user_id',
        'quantity',
        'reason',
        'stock_after',
    ];

    public function shopItem()
    {
        return $this->belongsTo(ShopItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}