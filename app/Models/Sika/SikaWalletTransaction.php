<?php

namespace App\Models\Sika;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SikaWalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'direction',
        'amount',
        'balance_before',
        'balance_after',
        'status',
        'idempotency_key',
        'reference',
        'external_reference',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    public const TYPE_DEPOSIT = 'DEPOSIT';
    public const TYPE_WITHDRAWAL = 'WITHDRAWAL';
    public const TYPE_SIKA_COIN_PURCHASE = 'SIKA_COIN_PURCHASE';
    public const TYPE_SIKA_COIN_CASHOUT = 'SIKA_COIN_CASHOUT';
    public const TYPE_TRANSFER_IN = 'TRANSFER_IN';
    public const TYPE_TRANSFER_OUT = 'TRANSFER_OUT';
    public const TYPE_REFUND = 'REFUND';
    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    public const DIRECTION_CREDIT = 'CREDIT';
    public const DIRECTION_DEBIT = 'DEBIT';

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_REVERSED = 'REVERSED';

    protected static function booted(): void
    {
        static::creating(function (SikaWalletTransaction $transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = self::generateReference();
            }
        });
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(SikaWallet::class, 'wallet_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCredit(): bool
    {
        return $this->direction === self::DIRECTION_CREDIT;
    }

    public function isDebit(): bool
    {
        return $this->direction === self::DIRECTION_DEBIT;
    }

    public static function generateReference(): string
    {
        return 'PBG' . date('Ymd') . strtoupper(Str::random(8));
    }

    public static function findByIdempotencyKey(string $key): ?self
    {
        return self::where('idempotency_key', $key)->first();
    }
}
