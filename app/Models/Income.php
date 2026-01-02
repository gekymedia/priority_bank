<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\IncomeCategory;
use App\Models\Account;

class Income extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'income_category_id',
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
     * Cast attributes to appropriate types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    /**
     * The user that owns this income record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Income category (source).
     */
    public function category()
    {
        return $this->belongsTo(IncomeCategory::class, 'income_category_id');
    }

    /**
     * Account into which this income was received.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * External system that originated this income.
     */
    public function externalSystem()
    {
        return $this->belongsTo(SystemRegistry::class, 'external_system_id');
    }
}
