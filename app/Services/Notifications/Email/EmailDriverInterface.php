<?php

namespace App\Services\Notifications\Email;

interface EmailDriverInterface
{
    public function send(string $to, string $message, ?string $subject = null): array;
}
