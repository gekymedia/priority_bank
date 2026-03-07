<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Saving;
use Illuminate\Console\Command;

class BackfillTransactionSavingId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:backfill-saving-id
                            {--dry-run : Only report what would be updated, do not save}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set saving_id on transactions created from approved deposits so "view more" shows the same notes as the Savings page';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->warn('Dry run – no changes will be saved.');
        }

        // Transactions that look like approved-deposit income but have no saving_id
        $transactions = Transaction::whereNull('saving_id')
            ->where('type', 'income')
            ->where('category', 'Savings')
            ->whereNotNull('description')
            ->where('description', 'like', 'Savings deposit from% - #%')
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('No transactions found to backfill.');
            return Command::SUCCESS;
        }

        $this->info('Found ' . $transactions->count() . ' transaction(s) that may have been created from approved deposits.');

        $updated = 0;
        $skipped = 0;

        foreach ($transactions as $transaction) {
            $savingId = $this->extractSavingIdFromDescription($transaction->description);
            if ($savingId === null) {
                $this->warn("  [skip] Transaction #{$transaction->id}: could not parse saving id from description.");
                $skipped++;
                continue;
            }

            $saving = Saving::find($savingId);
            if (!$saving) {
                $this->warn("  [skip] Transaction #{$transaction->id}: saving #{$savingId} not found.");
                $skipped++;
                continue;
            }

            // Sanity check: same user, amount, and date
            if ((int) $transaction->user_id !== (int) $saving->user_id ||
                (float) $transaction->amount !== (float) $saving->amount ||
                $transaction->date->format('Y-m-d') !== $saving->deposit_date->format('Y-m-d')) {
                $this->warn("  [skip] Transaction #{$transaction->id}: saving #{$savingId} does not match (user/amount/date).");
                $skipped++;
                continue;
            }

            if (!$dryRun) {
                $transaction->update(['saving_id' => $saving->id]);
            }

            $this->line("  [ok] Transaction #{$transaction->id} → saving #{$saving->id} ({$saving->user->name}, GHS " . number_format($saving->amount, 2) . ')');
            $updated++;
        }

        $this->newLine();
        if ($dryRun) {
            $this->info("Dry run complete: would update {$updated} transaction(s), skipped {$skipped}.");
        } else {
            $this->info("Backfill complete: updated {$updated} transaction(s), skipped {$skipped}.");
        }

        return Command::SUCCESS;
    }

    /**
     * Extract saving id from description like "Savings deposit from X - #123 (Direct Deposit)" or "... (Payment: ...)".
     */
    private function extractSavingIdFromDescription(?string $description): ?int
    {
        if ($description === null || $description === '') {
            return null;
        }
        if (preg_match('/ - #(\d+) \(/', $description, $m)) {
            return (int) $m[1];
        }
        return null;
    }
}
