<?php

namespace App\Console\Commands;

use App\Models\Saving;
use Illuminate\Console\Command;
use Carbon\Carbon;

class MarkExpiredPaymentsAsFailed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:mark-expired-as-failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark pending online payments (Paystack/Hubtel) as failed after 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired payments...');

        // Find all pending payments that are older than 24 hours and are online payments
        $expiredPayments = Saving::where('status', 'pending')
            ->whereIn('payment_method', ['paystack', 'hubtel'])
            ->where('approval_status', 'pending')
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->get();

        $count = 0;
        foreach ($expiredPayments as $saving) {
            $saving->update([
                'status' => 'failed',
                'approval_status' => 'rejected',
            ]);
            $count++;
        }

        $this->info("Marked {$count} expired payment(s) as failed.");

        return Command::SUCCESS;
    }
}
