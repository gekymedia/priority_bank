<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'deposit_date',
        'reference',
        'status',
        'notes',
        'payment_method',
        'approval_status',
        'transaction_reference',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deposit_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the saving.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the saving is successful and available for loans.
     */
    public function isAvailable()
    {
        return $this->status === 'successful';
    }

    /**
     * Get successful savings for a user.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'successful');
    }

    /**
     * Get total successful savings amount for a user.
     */
    public function scopeTotalAvailableForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
                    ->where('status', 'successful')
                    ->sum('amount');
    }

    /**
     * Scope for savings pending admin approval (e.g. direct deposits).
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending')
            ->where('status', 'pending');
    }
}
