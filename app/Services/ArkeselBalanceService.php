<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArkeselBalanceService
{
    protected const DEFAULT_BALANCE_URL = 'https://sms.arkesel.com/api/v2/clients/balance-details';
    protected const LEGACY_BALANCE_BASE = 'https://sms.arkesel.com';

    /**
     * Get Arkesel SMS balance. Uses config (after NotificationSetting::applyToConfig()).
     *
     * @return array{success: bool, balance?: int, main_balance?: string, error?: string}
     */
    public function getBalance(): array
    {
        $apiKey = config('services.arkesel.api_key');
        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'SMS not configured. Set Arkesel API key in Notification Settings.'];
        }
        if (str_contains(strtolower($apiKey), 'dummy') || $apiKey === 'your_api_key_here' || preg_match('/^x+$/i', $apiKey)) {
            return ['success' => false, 'error' => 'Set a valid Arkesel API key in Notification Settings to see balance.'];
        }

        $sendUrl = config('services.arkesel.url', '');
        $useLegacy = is_string($sendUrl) && $sendUrl !== '' && !str_contains($sendUrl, '/api/v2/sms/send');

        if ($useLegacy) {
            return $this->getBalanceLegacy($apiKey);
        }

        $url = config('services.arkesel.balance_url') ?: self::DEFAULT_BALANCE_URL;
        try {
            $res = Http::timeout(10)
                ->withHeaders([
                    'api-key' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->get($url);

            $body = $res->json();
            if ($res->successful() && is_array($body) && ($body['status'] ?? '') === 'success' && isset($body['data']['sms_balance'])) {
                $data = $body['data'];
                return [
                    'success' => true,
                    'balance' => (int) $data['sms_balance'],
                    'main_balance' => $data['main_balance'] ?? null,
                ];
            }
            if ($res->successful() && is_array($body) && isset($body['balance'])) {
                return [
                    'success' => true,
                    'balance' => (int) $body['balance'],
                    'user' => $body['user'] ?? null,
                    'country' => $body['country'] ?? null,
                ];
            }
            return ['success' => false, 'error' => $body['message'] ?? $body['error'] ?? 'Invalid response'];
        } catch (\Throwable $e) {
            Log::warning('Arkesel balance check failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Legacy: GET sms.arkesel.com/sms/api?action=check-balance&api_key=...&response=json
     */
    protected function getBalanceLegacy(string $apiKey): array
    {
        try {
            $url = self::LEGACY_BALANCE_BASE . '/sms/api?' . http_build_query([
                'action' => 'check-balance',
                'api_key' => $apiKey,
                'response' => 'json',
            ]);
            $res = Http::timeout(10)->acceptJson()->get($url);
            $body = $res->json();
            if ($res->successful() && is_array($body) && isset($body['balance'])) {
                return [
                    'success' => true,
                    'balance' => (int) $body['balance'],
                    'user' => $body['user'] ?? null,
                    'country' => $body['country'] ?? null,
                ];
            }
            return ['success' => false, 'error' => $body['message'] ?? $body['error'] ?? 'Invalid response'];
        } catch (\Throwable $e) {
            Log::warning('Arkesel legacy balance check failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
