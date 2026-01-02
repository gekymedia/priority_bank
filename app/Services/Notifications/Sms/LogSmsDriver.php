<?php

namespace App\Services\Notifications\Sms;

use Illuminate\Support\Facades\Log;

class LogSmsDriver implements SmsDriverInterface
{
    public function send(string $to, string $message): array
    {
        Log::info("SMS Notification - To: {$to}, Message: {$message}");

        return [
            'ok' => true,
            'status' => 200,
            'body' => ['message' => 'Logged successfully']
        ];
    }
}
