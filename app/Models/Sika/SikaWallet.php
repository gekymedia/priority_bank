<?php

namespace App\Models\Sika;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SikaWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_user_id',
        'source',
        'balance',
        'status',
        'currency',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'external_user_id' => 'integer',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_FROZEN = 'frozen';

    public const SOURCE_GEKYCHAT = 'gekychat';

    public function transactions(): HasMany
    {
        return $this->hasMany(SikaWalletTransaction::class, 'wallet_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function canTransact(): bool
    {
        return $this->isActive();
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return (float) $this->balance >= $amount;
    }

    /**
     * Get or create wallet for an external user
     */
    public static function getOrCreateForExternalUser(int $externalUserId, string $source = self::SOURCE_GEKYCHAT): self
    {
        return self::firstOrCreate(
            ['external_user_id' => $externalUserId, 'source' => $source],
            ['balance' => 0, 'status' => self::STATUS_ACTIVE, 'currency' => 'GHS']
        );
    }

    /**
     * @deprecated Use getOrCreateForExternalUser instead
     */
    public static function getOrCreateForUser(int $userId, string $source = self::SOURCE_GEKYCHAT): self
    {
        return self::getOrCreateForExternalUser($userId, $source);
    }
}
