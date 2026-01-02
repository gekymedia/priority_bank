<?php

namespace App\Services;

use App\Models\User;
use App\Jobs\SendNotificationMessage;
use Illuminate\Support\Facades\Log;

class AdminNotificationService
{
    /**
     * Send notification to all admin users when an API action occurs
     */
    public function notifyAdmins(string $message, ?string $subject = null): void
    {
        try {
            $admins = User::where('role', 'admin')->get();

            if ($admins->isEmpty()) {
                Log::warning('No admin users found for notification');
                return;
            }

            foreach ($admins as $admin) {
                // Send SMS if admin has phone
                if ($admin->phone) {
                    SendNotificationMessage::dispatch('sms', $admin->phone, $message);
                }

                // Send email if admin has email
                if ($admin->email) {
                    SendNotificationMessage::dispatch('email', $admin->email, $message, $subject ?: 'Priority Savings Group Alert');
                }
            }

            Log::info('Admin notifications queued', [
                'admin_count' => $admins->count(),
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send admin notification', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    /**
     * Notify admins when an expense is recorded via API
     */
    public function notifyExpenseRecorded(string $userName, float $amount, string $category, string $date): void
    {
        $message = "ALERT: New expense recorded via API\nUser: {$userName}\nAmount: GHS {$amount}\nCategory: {$category}\nDate: {$date}";
        $subject = "New Expense Recorded - Priority Savings Group";

        $this->notifyAdmins($message, $subject);
    }

    /**
     * Notify admins when a payment is recorded via API
     */
    public function notifyPaymentRecorded(string $userName, float $amount, string $loanInfo, string $paymentMethod): void
    {
        $message = "ALERT: New payment recorded via API\nUser: {$userName}\nAmount: GHS {$amount}\nLoan: {$loanInfo}\nMethod: {$paymentMethod}";
        $subject = "New Payment Recorded - Priority Savings Group";

        $this->notifyAdmins($message, $subject);
    }

    /**
     * Notify admins when a loan is marked as returned via API
     */
    public function notifyLoanReturned(string $userName, float $amount, string $borrowerName): void
    {
        $message = "ALERT: Loan marked as returned via API\nUser: {$userName}\nAmount: GHS {$amount}\nBorrower: {$borrowerName}";
        $subject = "Loan Returned - Priority Savings Group";

        $this->notifyAdmins($message, $subject);
    }

    /**
     * Notify admins when a loan is marked as lost via API
     */
    public function notifyLoanLost(string $userName, float $amount, string $borrowerName): void
    {
        $message = "ALERT: Loan marked as lost via API\nUser: {$userName}\nAmount: GHS {$amount}\nBorrower: {$borrowerName}";
        $subject = "Loan Loss Recorded - Priority Savings Group";

        $this->notifyAdmins($message, $subject);
    }
}









