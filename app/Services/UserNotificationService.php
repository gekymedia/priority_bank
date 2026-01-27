<?php

namespace App\Services;

use App\Models\User;
use App\Jobs\SendNotificationMessage;
use App\Services\GekyChatService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class UserNotificationService
{
    protected $gekyChatService;
    protected $whatsAppService;

    public function __construct()
    {
        $this->gekyChatService = app(GekyChatService::class);
        // WhatsApp service would be injected here if available
        // $this->whatsAppService = app(WhatsAppService::class);
    }

    /**
     * Notify a user based on their notification preferences
     * 
     * @param User $user The user to notify
     * @param string $message The message to send
     * @param string|null $subject Optional subject for email
     * @param array $metadata Optional metadata for GekyChat
     */
    public function notifyUser(User $user, string $message, ?string $subject = null, array $metadata = []): void
    {
        try {
            // Email notification
            if ($user->notification_email && $user->email) {
                try {
                    SendNotificationMessage::dispatch('email', $user->email, $message, $subject ?: 'Priority Bank Notification');
                    Log::info('User email notification queued', [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to queue email notification', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Browser notification (in-app)
            if ($user->notification_browser) {
                try {
                    // Create Laravel notification record
                    $user->notify(new \App\Notifications\GenericNotification($message, $subject));
                    Log::info('User browser notification created', [
                        'user_id' => $user->id
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to create browser notification', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // SMS notification
            if ($user->notification_sms && $user->phone) {
                try {
                    SendNotificationMessage::dispatch('sms', $user->phone, $message);
                    Log::info('User SMS notification queued', [
                        'user_id' => $user->id,
                        'phone' => $user->phone
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to queue SMS notification', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // WhatsApp notification
            if ($user->notification_whatsapp && $user->phone) {
                try {
                    // Check if WhatsApp service is available
                    if (class_exists(\App\Services\WhatsAppService::class)) {
                        $whatsAppService = app(\App\Services\WhatsAppService::class);
                        $whatsAppService->sendMessage($user->phone, $message);
                        Log::info('User WhatsApp notification sent', [
                            'user_id' => $user->id,
                            'phone' => $user->phone
                        ]);
                    } else {
                        Log::warning('WhatsApp service not available', [
                            'user_id' => $user->id
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to send WhatsApp notification', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // GekyChat notification
            if ($user->notification_gekychat && $user->phone) {
                try {
                    $result = $this->gekyChatService->sendMessageByPhone($user->phone, $message, $metadata);
                    if ($result['success'] ?? false) {
                        Log::info('User GekyChat notification sent', [
                            'user_id' => $user->id,
                            'phone' => $user->phone,
                            'message_id' => $result['message_id'] ?? null
                        ]);
                    } else {
                        Log::warning('GekyChat notification failed', [
                            'user_id' => $user->id,
                            'error' => $result['error'] ?? 'Unknown error'
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Failed to send GekyChat notification', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

        } catch (\Throwable $e) {
            Log::error('User notification service error', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Notify user about loan request approval
     */
    public function notifyLoanApproved(User $user, float|int|string $amountRequested, float|int|string $amountApproved, ?string $notes = null): void
    {
        $message = "✅ Loan Request Approved!\n\n";
        $message .= "Requested Amount: GHS " . number_format($amountRequested, 2) . "\n";
        $message .= "Approved Amount: GHS " . number_format($amountApproved, 2) . "\n";
        if ($notes) {
            $message .= "Notes: {$notes}\n";
        }
        $message .= "\nPlease check your dashboard for details.";

        $this->notifyUser($user, $message, "Loan Request Approved");
    }

    /**
     * Notify user about loan request rejection
     */
    public function notifyLoanRejected(User $user, float|int|string $amountRequested, ?string $reason = null): void
    {
        $message = "❌ Loan Request Rejected\n\n";
        $message .= "Requested Amount: GHS " . number_format($amountRequested, 2) . "\n";
        if ($reason) {
            $message .= "Reason: {$reason}\n";
        }
        $message .= "\nPlease contact admin for more information.";

        $this->notifyUser($user, $message, "Loan Request Rejected");
    }

    /**
     * Notify user about deposit approval
     */
    public function notifyDepositApproved(User $user, float|int|string $amount, string $method = 'Direct Deposit'): void
    {
        $message = "✅ Deposit Approved!\n\n";
        $message .= "Amount: GHS " . number_format($amount, 2) . "\n";
        $message .= "Method: {$method}\n";
        $message .= "\nYour deposit has been successfully processed.";

        $this->notifyUser($user, $message, "Deposit Approved");
    }

    /**
     * Notify user about deposit rejection/failure
     */
    public function notifyDepositFailed(User $user, float|int|string $amount, ?string $reason = null): void
    {
        $message = "❌ Deposit Failed\n\n";
        $message .= "Amount: GHS " . number_format($amount, 2) . "\n";
        if ($reason) {
            $message .= "Reason: {$reason}\n";
        }
        $message .= "\nYou can try again from your savings page.";

        $this->notifyUser($user, $message, "Deposit Failed");
    }

    /**
     * Notify user about transaction created by admin
     */
    public function notifyTransactionCreated(User $user, string $type, float|int|string $amount, string $category, ?string $description = null): void
    {
        $icon = $type === 'income' ? '💰' : '💸';
        $message = "{$icon} New Transaction Recorded\n\n";
        $message .= "Type: " . ucfirst($type) . "\n";
        $message .= "Amount: GHS " . number_format($amount, 2) . "\n";
        $message .= "Category: {$category}\n";
        if ($description) {
            $message .= "Description: {$description}\n";
        }
        $message .= "\nCheck your transactions for details.";

        $this->notifyUser($user, $message, "New Transaction Recorded");
    }

    /**
     * Notify user about loan payment recorded
     */
    public function notifyLoanPaymentRecorded(User $user, float|int|string $amount, float|int|string $remainingBalance): void
    {
        $message = "💳 Loan Payment Recorded\n\n";
        $message .= "Amount Paid: GHS " . number_format($amount, 2) . "\n";
        $message .= "Remaining Balance: GHS " . number_format($remainingBalance, 2) . "\n";
        $message .= "\nThank you for your payment!";

        $this->notifyUser($user, $message, "Loan Payment Recorded");
    }
}
