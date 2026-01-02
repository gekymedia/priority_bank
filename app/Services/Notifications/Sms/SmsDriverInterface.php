<?php

namespace App\Services\Notifications\Sms;

interface SmsDriverInterface
{
    public function send(string $to, string $message): array;
}
