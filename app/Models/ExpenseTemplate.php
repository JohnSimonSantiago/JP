<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// A saved preset for a monthly bill (Rent, Electricity, ...). It never creates
// expenses by itself; "Log it" only pre-fills the Add Expense form.
class ExpenseTemplate extends Model
{
    protected $fillable = [
        'expense_category_id',
        'description',
        'default_amount', // null = the amount changes every month (e.g. electricity)
        'due_day',        // 1–31; bigger than the month's days = the last day
        'is_active',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'due_day'        => 'integer',
        'is_active'      => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
