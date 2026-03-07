<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class PaymentSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null): ?string
    {
        $row = static::where('key', $key)->first();
        return $row ? $row->value : $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Active payment gateway: when both Hubtel and Paystack are configured, Hubtel takes precedence.
     * Returns 'hubtel', 'paystack', or null.
     */
    public static function getActiveGateway(): ?string
    {
        $hubtelConfigured = static::isHubtelConfigured();
        $paystackConfigured = static::isPaystackConfigured();

        if ($hubtelConfigured) {
            return 'hubtel';
        }
        if ($paystackConfigured) {
            return 'paystack';
        }
        return null;
    }

    public static function isHubtelConfigured(): bool
    {
        $clientId = static::get('hubtel_client_id') ?: config('services.hubtel.client_id');
        $clientSecret = static::get('hubtel_client_secret') ?: config('services.hubtel.client_secret');
        $apiKey = static::get('hubtel_api_key') ?: config('services.hubtel.api_key');
        $apiSecret = static::get('hubtel_api_secret') ?: config('services.hubtel.api_secret');
        return (!empty($clientId) && !empty($clientSecret)) || (!empty($apiKey) && !empty($apiSecret));
    }

    public static function isPaystackConfigured(): bool
    {
        $secret = static::get('paystack_secret_key') ?: config('services.paystack.secret_key');
        return !empty($secret);
    }

    /**
     * Apply stored payment settings to Laravel config (DB overrides .env when set).
     */
    public static function applyToConfig(): void
    {
        $keys = [
            'hubtel_client_id' => 'services.hubtel.client_id',
            'hubtel_client_secret' => 'services.hubtel.client_secret',
            'hubtel_api_key' => 'services.hubtel.api_key',
            'hubtel_api_secret' => 'services.hubtel.api_secret',
            'hubtel_merchant_account_number' => 'services.hubtel.merchant_account_number',
            'paystack_public_key' => 'services.paystack.public_key',
            'paystack_secret_key' => 'services.paystack.secret_key',
            'hubtel_merchant_account_number' => 'services.hubtel.merchant_account_number',
        ];
        foreach ($keys as $dbKey => $configKey) {
            $v = static::get($dbKey);
            if ($v !== null && $v !== '') {
                Config::set($configKey, $v);
            }
        }
    }
}
