<?php

namespace App\Console\Commands;

use App\Models\SystemRegistry;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportLegacyTransactions extends Command
{
    protected $signature = 'transactions:import-legacy
                            {--file= : Path to JSON file (default: storage/app/legacy_imports/transactions_2025.json)}
                            {--user-id= : Fallback owner when JSON omits user_id and systems_registry has no user for system_id (optional if every row resolves)}
                            {--dry-run : Validate and list rows without inserting}
                            {--allow-missing-system : Set external_system_id to null if system_id is unknown}';

    protected $description = 'Import legacy transactions from JSON (e.g. monthly rollups with description + notes). Skips rows that already exist (same date, type, category, amount, source) so re-running the same file does not duplicate.';

    public function handle(): int
    {
        $path = $this->option('file')
            ?: storage_path('app/legacy_imports/transactions_2025.json');

        if (! File::exists($path)) {
            $this->error("File not found: {$path}");
            $this->line('Copy storage/app/legacy_imports/transactions_2025.example.json and fill it, or pass --file=');

            return Command::FAILURE;
        }

        $raw = File::get($path);
        $data = json_decode($raw, true);

        if (! is_array($data) || ! isset($data['transactions']) || ! is_array($data['transactions'])) {
            $this->error('Invalid JSON: root must be an object with a "transactions" array.');

            return Command::FAILURE;
        }

        $defaultUserId = $this->resolveDefaultUserId();

        $rows = $data['transactions'];
        $allowMissing = $this->option('allow-missing-system');
        $dryRun = $this->option('dry-run');

        $prepared = [];
        foreach ($rows as $i => $row) {
            $lineNum = $i + 1;
            $err = $this->validateRow($row, $lineNum);
            if ($err) {
                $this->error($err);

                return Command::FAILURE;
            }

            $systemId = $row['system_id'] ?? null;
            $externalSystemId = null;

            if (isset($row['external_system_id']) && $row['external_system_id'] !== null && $row['external_system_id'] !== '') {
                $externalSystemId = (int) $row['external_system_id'];
                $sysById = SystemRegistry::whereKey($externalSystemId)->first();
                if (! $sysById) {
                    $this->error("Row {$lineNum}: external_system_id {$externalSystemId} not found in systems_registry.");

                    return Command::FAILURE;
                }
            } elseif ($systemId !== null && $systemId !== '') {
                $sys = SystemRegistry::where('system_id', $systemId)->first();
                if (! $sys) {
                    if (! $allowMissing) {
                        $this->error("Row {$lineNum}: unknown system_id \"{$systemId}\". Fix JSON, seed systems, use external_system_id, or use --allow-missing-system.");

                        return Command::FAILURE;
                    }
                    $this->warn("Row {$lineNum}: system_id \"{$systemId}\" not found — storing without source.");
                } else {
                    $externalSystemId = $sys->id;
                }
            }

            $uid = $this->resolveUserIdForLegacyRow($row, $systemId, $defaultUserId, $lineNum);
            if ($uid === null) {
                $this->error("Row {$lineNum}: cannot resolve user_id (set row user_id, LEGACY_IMPORT_USER_ID / --user-id, or systems_registry.user_id for system_id).");

                return Command::FAILURE;
            }

            if (! User::whereKey($uid)->exists()) {
                $this->error("Row {$lineNum}: user_id {$uid} does not exist.");

                return Command::FAILURE;
            }

            $prepared[] = [
                'user_id' => $uid,
                'type' => $row['type'],
                'category' => $row['category'],
                'amount' => (float) $row['amount'],
                'date' => $row['date'],
                'description' => $row['description'] ?? null,
                'notes' => $row['notes'] ?? null,
                'external_system_id' => $externalSystemId,
            ];
        }

        if ($prepared === []) {
            $this->warn('No transactions in file.');

            return Command::SUCCESS;
        }

        $headers = ['date', 'type', 'amount', 'category', 'user_id'];
        $rows = [];
        foreach ($prepared as $p) {
            $row = [
                $p['date'],
                $p['type'],
                number_format($p['amount'], 2),
                \Illuminate\Support\Str::limit($p['category'], 40),
                $p['user_id'],
            ];
            if ($dryRun) {
                $row[] = $this->legacyRowAlreadyImported($p) ? 'already in DB' : 'would insert';
            }
            $rows[] = $row;
        }
        if ($dryRun) {
            $headers[] = 'status';
        }
        $this->table($headers, $rows);

        if ($dryRun) {
            $already = count(array_filter($rows, fn ($r) => end($r) === 'already in DB'));
            $wouldInsert = count($prepared) - $already;
            $this->info("Dry run: {$wouldInsert} would insert, {$already} already present — nothing written.");

            return Command::SUCCESS;
        }

        $inserted = 0;
        $skipped = 0;
        DB::transaction(function () use ($prepared, &$inserted, &$skipped) {
            foreach ($prepared as $p) {
                if ($this->legacyRowAlreadyImported($p)) {
                    $skipped++;

                    continue;
                }
                Transaction::create($p);
                $inserted++;
            }
        });

        $this->info("Inserted {$inserted} transaction(s), skipped {$skipped} already imported.");

        return Command::SUCCESS;
    }

    /**
     * Same JSON row re-run should not insert twice. Key excludes user_id so changing
     * LEGACY_IMPORT_USER_ID does not create a second copy of the same legacy line.
     */
    private function legacyRowAlreadyImported(array $p): bool
    {
        $q = Transaction::query()
            ->whereDate('date', $p['date'])
            ->where('type', $p['type'])
            ->where('category', $p['category'])
            ->whereRaw('ABS(amount - ?) < 0.01', [round((float) $p['amount'], 2)]);

        if ($p['external_system_id'] === null) {
            $q->whereNull('external_system_id');
        } else {
            $q->where('external_system_id', $p['external_system_id']);
        }

        return $q->exists();
    }

    private function resolveDefaultUserId(): ?int
    {
        if ($this->option('user-id') !== null && $this->option('user-id') !== '') {
            return (int) $this->option('user-id');
        }

        $env = env('LEGACY_IMPORT_USER_ID');
        if ($env !== null && $env !== '') {
            return (int) $env;
        }

        return null;
    }

    /**
     * JSON user_id wins; else config legacy_import.system_user_map; else systems_registry.user_id for system_id; else default.
     */
    private function resolveUserIdForLegacyRow(array $row, ?string $systemId, ?int $defaultUserId, int $lineNum): ?int
    {
        if (isset($row['user_id']) && $row['user_id'] !== null && $row['user_id'] !== '') {
            return (int) $row['user_id'];
        }

        $map = config('legacy_import.system_user_map', []);
        if ($systemId && isset($map[$systemId]) && $map[$systemId] !== '') {
            return (int) $map[$systemId];
        }

        if ($systemId) {
            $sys = SystemRegistry::where('system_id', $systemId)->first();
            if ($sys && $sys->user_id) {
                return (int) $sys->user_id;
            }
        }

        if ($defaultUserId !== null) {
            return $defaultUserId;
        }

        return null;
    }

    private function validateRow(array $row, int $lineNum): ?string
    {
        $required = ['date', 'type', 'amount', 'category'];
        foreach ($required as $k) {
            if (! array_key_exists($k, $row) || $row[$k] === null || $row[$k] === '') {
                return "Row {$lineNum}: missing \"{$k}\".";
            }
        }

        if (! in_array($row['type'], ['income', 'expense'], true)) {
            return "Row {$lineNum}: type must be income or expense.";
        }

        if (! is_numeric($row['amount']) || (float) $row['amount'] <= 0) {
            return "Row {$lineNum}: amount must be a positive number.";
        }

        return null;
    }
}
