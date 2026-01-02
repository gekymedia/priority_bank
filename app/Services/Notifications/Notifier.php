<?php

namespace App\Services\Notifications;

use App\Services\Notifications\Sms\SmsDriverInterface;
use App\Services\Notifications\Email\EmailDriverInterface;

class Notifier
{
    public function __construct(
        protected SmsDriverInterface $sms,
        protected EmailDriverInterface $email,
    ) {}

    public function sms(string $to, string $message): array
    {
        return $this->sms->send($to, $message);
    }

    public function email(string $to, string $message, ?string $subject = null): array
    {
        return $this->email->send($to, $message, $subject);
    }
}
