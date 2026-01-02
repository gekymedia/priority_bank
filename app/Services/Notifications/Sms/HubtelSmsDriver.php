<?php

namespace App\Services\Notifications\Sms;

use Illuminate\Support\Facades\Http;

class HubtelSmsDriver implements SmsDriverInterface
{
    public function send(string $to, string $message): array
    {
        $id = config('services.hubtel.client_id');
        $secret = config('services.hubtel.client_secret');
        $from = config('services.hubtel.from', 'PriorityBank');

        if (!$id || !$secret) {
            return ['ok' => false, 'error' => 'Missing Hubtel credentials'];
        }

        $response = Http::withBasicAuth($id, $secret)->post('https://smsc.hubtel.com/v1/messages/send', [
            'From' => $from,
            'To' => $to,
            'Content' => $message,
            'ClientId' => $id,
            'ClientSecret' => $secret,
            'RegisteredDelivery' => 'true',
        ]);

        return [
            'ok' => $response->ok(),
            'status' => $response->status(),
            'body' => $response->json()
        ];
    }
}
