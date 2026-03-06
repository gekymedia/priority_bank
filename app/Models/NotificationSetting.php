<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class NotificationSetting extends Model
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

    public static function channelEnabled(string $channel): bool
    {
        $v = static::get("channel_{$channel}_enabled", '1');
        return $v === '1' || $v === 'true';
    }

    public static function setChannelEnabled(string $channel, bool $enabled): void
    {
        static::set("channel_{$channel}_enabled", $enabled ? '1' : '0');
    }

    /**
     * Apply stored notification settings to Laravel config (DB overrides .env when set).
     * Call before sending notifications so GekyChat, Mail, SMS use admin-configured values.
     */
    public static function applyToConfig(): void
    {
        $keys = [
            'mail_driver' => 'mail.default',
            'from_email' => 'mail.from.address',
            'from_name' => 'mail.from.name',
            'smtp_host' => 'mail.mailers.smtp.host',
            'smtp_port' => 'mail.mailers.smtp.port',
            'smtp_username' => 'mail.mailers.smtp.username',
            'smtp_password' => 'mail.mailers.smtp.password',
            'smtp_encryption' => 'mail.mailers.smtp.encryption',
            'gekychat_client_id' => 'services.gekychat.client_id',
            'gekychat_client_secret' => 'services.gekychat.client_secret',
            'gekychat_base_url' => 'services.gekychat.base_url',
            'whatsapp_phone_number_id' => 'services.whatsapp.phone_number_id',
            'whatsapp_access_token' => 'services.whatsapp.access_token',
            'whatsapp_base_url' => 'services.whatsapp.base_url',
            'arkesel_api_key' => 'services.arkesel.api_key',
            'arkesel_api_url' => 'services.arkesel.url',
            'arkesel_sender' => 'services.arkesel.sender',
        ];
        foreach ($keys as $dbKey => $configKey) {
            $v = static::get($dbKey);
            if ($v !== null && $v !== '') {
                Config::set($configKey, $v);
            }
        }
    }
}
