<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\Notifications\Notifier;

class SendNotificationMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $channel,
        protected string $to,
        protected string $message,
        protected ?string $subject = null,
    ) {}

    public function handle(Notifier $notifier): void
    {
        match($this->channel) {
            'sms' => $notifier->sms($this->to, $this->message),
            'email' => $notifier->email($this->to, $this->message, $this->subject),
            default => throw new \InvalidArgumentException("Unsupported channel: {$this->channel}"),
        };
    }
}
