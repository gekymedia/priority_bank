<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ExpenseCategory;
use App\Models\Account;

class Expense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'expense_category_id',
        'account_id',
        'amount',
        'date',
        'channel',
        'notes',
        'external_system_id',
        'external_transaction_id',
        'idempotency_key',
        'sync_status',
        'synced_at',
        'sync_error',
    ];

    /**
     * Attribute casting.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    /**
     * Get the user that owns the expense.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the expense category.
     */
    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Get the account from which this expense was paid.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * External system that originated this expense.
     */
    public function externalSystem()
    {
        return $this->belongsTo(SystemRegistry::class, 'external_system_id');
    }
}
