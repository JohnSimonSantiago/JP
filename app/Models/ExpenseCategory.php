<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'name',
        'color',     // hex like #22c55e
        'is_active', // false = archived (hidden from "add expense", old expenses keep it)
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function templates()
    {
        return $this->hasMany(ExpenseTemplate::class);
    }

    // Categories that can be picked for a new expense
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
