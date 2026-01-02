<?php

namespace App\Services\Notifications\Email;

use Illuminate\Support\Facades\Mail;

class LaravelMailDriver implements EmailDriverInterface
{
    public function send(string $to, string $message, ?string $subject = null): array
    {
        try {
            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)
                     ->subject($subject ?: 'Priority Savings Group Notification');
            });

            return [
                'ok' => true,
                'status' => 200,
                'body' => ['message' => 'Email sent successfully']
            ];
        } catch (\Exception $e) {
            return [
                'ok' => false,
                'status' => 500,
                'body' => ['error' => $e->getMessage()]
            ];
        }
    }
}
