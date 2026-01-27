<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_fund_type',
        'from_system_id',
        'to_fund_type',
        'to_system_id',
        'amount',
        'notes',
        'expense_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user who created the transfer.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the expense record associated with this transfer.
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    /**
     * Get the source system (if API fund).
     */
    public function fromSystem()
    {
        return $this->belongsTo(SystemRegistry::class, 'from_system_id');
    }

    /**
     * Get the destination system (if API fund).
     */
    public function toSystem()
    {
        return $this->belongsTo(SystemRegistry::class, 'to_system_id');
    }
}
