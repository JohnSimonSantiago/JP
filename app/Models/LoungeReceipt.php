<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoungeReceipt extends Model
{
    protected $fillable = [
        'year',
        'month',
        'number',
        'group_id',
    ];
}