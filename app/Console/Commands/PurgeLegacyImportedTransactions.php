<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeLegacyImportedTransactions extends Command
{
    /**
     * Removes rows created by `transactions:import-legacy` (transactions_2025.json).
     *
     * CUG / central-finance pushes are kept: they always have external_transaction_id.
     * Legacy import does not set that column; categories use "Legacy 2025" in the label.
     */
    protected $signature = 'transactions:purge-legacy-import
                            {--dry-run : List matching rows without deleting}
                            {--force : Actually delete (required without dry-run)}';

    protected $description = 'Delete legacy JSON-imported transactions; keep API-synced rows (e.g. CUG form sales with external_transaction_id).';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        if (! $dryRun && ! $force) {
            $this->error('Refusing to delete: pass --dry-run to preview, or --force to delete.');

            return Command::FAILURE;
        }

        $query = Transaction::query()
            ->whereNull('external_transaction_id')
            ->where('category', 'like', '%Legacy 2025%');

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info('No matching legacy-import transactions found.');

            return Command::SUCCESS;
        }

        $rows = (clone $query)
            ->orderBy('date')
            ->orderBy('id')
            ->get(['id', 'user_id', 'date', 'type', 'category', 'amount']);

        $this->info("Matching rows: {$count} (external_transaction_id IS NULL and category LIKE %Legacy 2025%).");

        $this->table(
            ['id', 'user_id', 'date', 'type', 'amount', 'category'],
            $rows->map(fn ($t) => [
                $t->id,
                $t->user_id,
                $t->date->format('Y-m-d'),
                $t->type,
                number_format((float) $t->amount, 2),
                \Illuminate\Support\Str::limit($t->category, 48),
            ])->all()
        );

        if ($dryRun) {
            $this->warn('Dry run — no rows deleted. Run with --force to delete.');

            return Command::SUCCESS;
        }

        $deleted = 0;
        DB::transaction(function () use ($query, &$deleted) {
            $deleted = $query->delete();
        });

        $this->info("Deleted {$deleted} transaction(s). Balances recalculate from remaining savings + transactions.");

        return Command::SUCCESS;
    }
}
