<?php

namespace App\Services\Notifications\Email;

use Illuminate\Support\Facades\Log;

class LogEmailDriver implements EmailDriverInterface
{
    public function send(string $to, string $message, ?string $subject = null): array
    {
        Log::info("Email Notification - To: {$to}, Subject: {$subject}, Message: {$message}");

        return [
            'ok' => true,
            'status' => 200,
            'body' => ['message' => 'Logged successfully']
        ];
    }
}
