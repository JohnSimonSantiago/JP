<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes; // delete = soft delete; the receipt file is kept so it can be restored

    protected $fillable = [
        'expense_category_id',
        'expense_template_id', // set when logged from a template ("Log it"); null otherwise
        'amount',
        'description',
        'spent_at',     // stored in UTC, shown in Manila time
        'receipt_path', // PRIVATE disk path (storage/app), served by an admin-only route
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'spent_at' => 'datetime',
    ];

    // The private file path never goes out in JSON; the page gets has_receipt
    // and loads the photo through the admin-only receipt route instead.
    protected $hidden = [
        'receipt_path',
    ];

    protected $appends = [
        'has_receipt',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function template()
    {
        return $this->belongsTo(ExpenseTemplate::class, 'expense_template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getHasReceiptAttribute(): bool
    {
        return !empty($this->receipt_path);
    }
}
