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
        'status',
        'notes',
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
     * Check if the saving is available for loans.
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Get available savings for a user.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Get total available savings amount for a user.
     */
    public function scopeTotalAvailableForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
                    ->where('status', 'available')
                    ->sum('amount');
    }
}
