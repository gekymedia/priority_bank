<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type',
        'category',
        'amount',
        'date',
        'description',
        'notes',
        'external_system_id',
        'external_transaction_id',
        'saving_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the external system associated with the transaction.
     */
    public function externalSystem()
    {
        return $this->belongsTo(SystemRegistry::class, 'external_system_id');
    }

    /**
     * Get the deposit (saving) this transaction was created from when admin approved a deposit.
     * Used to show the same notes from the Savings page in transaction "view more".
     */
    public function depositSaving()
    {
        return $this->belongsTo(Saving::class, 'saving_id');
    }

    /**
     * Income/expense rows created from an approved Saving deposit (saving_id set) are ledger mirrors;
     * the amount is already counted on the Saving record — exclude from sums that add savings + transactions.
     */
    public function scopeNotSavingsDepositMirror($query)
    {
        return $query->whereNull('saving_id');
    }

    /**
     * Scope a query to only include income transactions.
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope a query to only include expense transactions.
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Decode HTML entities and normalize line breaks for UI (legacy JSON imports often store &amp;, &#039;, \r\n).
     */
    public static function textForDisplay(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return str_replace(["\r\n", "\r"], "\n", $decoded);
    }
}
