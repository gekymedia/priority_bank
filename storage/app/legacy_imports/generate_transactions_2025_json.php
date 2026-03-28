<?php
/**
 * Builds storage/app/legacy_imports/transactions_2025.json
 * Reads desc_*.txt and 2025-*.txt / *-income.txt alongside this folder.
 * Expense months Jan–Oct: four rows per month (CEO, Geky, Admissions, Agriculture).
 * Prefer bucket_splits_2025/*.json (curated line items); else keyword split + stub fallback.
 *
 *   php storage/app/legacy_imports/generate_transactions_2025_json.php
 */

require __DIR__.'/legacy_expense_split.php';
require __DIR__.'/legacy_curated_buckets.php';

$base = __DIR__;
$legacyCfg = require __DIR__.'/legacy_2025_expense_config.php';
/** Use static split only when diary file is shorter than this (stub). */
define('LEGACY_STUB_MAX_BYTES', (int) $legacyCfg['stub_max_bytes']);

$read = function (string $name): string {
    $p = $GLOBALS['base'].'/'.$name;
    if (! is_file($p)) {
        return "[Missing file: $name]\n";
    }

    return file_get_contents($p);
};

/**
 * @return array{personal_ceo: float, gekymedia: float, priority_admissions: float, priority_agriculture: float, parsed_sum: float}
 */
function legacyGetExpenseBuckets(string $baseDir, string $ymKey, float $expectedTotal, string $notesFilename): array
{
    $curated = legacyTryLoadCuratedBuckets($baseDir, $ymKey);
    if ($curated !== null) {
        legacyAssertCuratedMatchesExpected($curated, $expectedTotal, $ymKey);

        return $curated;
    }

    $notesPath = $baseDir.'/'.$notesFilename;
    $staticAll = is_file($baseDir.'/legacy_static_splits.php')
        ? require $baseDir.'/legacy_static_splits.php'
        : [];
    $stub = is_file($notesPath) && filesize($notesPath) < LEGACY_STUB_MAX_BYTES;

    if ($stub && isset($staticAll[$ymKey])) {
        $s = $staticAll[$ymKey];
        $sum = round(
            $s['personal_ceo'] + $s['gekymedia'] + $s['priority_admissions'] + $s['priority_agriculture'],
            2
        );
        if (abs($sum - round($expectedTotal, 2)) > 0.02) {
            throw new RuntimeException(
                "legacy_static_splits.php [{$ymKey}] sums to {$sum}, expected {$expectedTotal}"
            );
        }
        $s['parsed_sum'] = $sum;

        return $s;
    }

    $text = is_file($notesPath) ? file_get_contents($notesPath) : '';

    return legacySplitExpenseText($text, $expectedTotal);
}

$monthNames = $legacyCfg['month_names'];
$expenseMonths = $legacyCfg['expense_months'];

$bucketMeta = [
    'personal_ceo' => ['label' => 'CEO Personal', 'system_id' => 'personal_ceo'],
    'gekymedia' => ['label' => 'Geky Media', 'system_id' => 'gekymedia'],
    'priority_admissions' => ['label' => 'Admissions', 'system_id' => 'priority_admissions'],
    'priority_agriculture' => ['label' => 'Agriculture', 'system_id' => 'priority_agriculture'],
];

$tx = [];

foreach ($expenseMonths as $m) {
    $mm = substr($m['ym'], 5, 2);
    $monthLabel = $monthNames[$mm] ?? $m['ym'];
    $buckets = legacyGetExpenseBuckets($base, $m['ym'], $m['total'], $m['notes']);

    foreach ($bucketMeta as $key => $meta) {
        $amt = round($buckets[$key], 2);
        if ($amt <= 0) {
            continue;
        }
        $tx[] = [
            'date' => $m['date'],
            'type' => 'expense',
            'amount' => $amt,
            'category' => "{$meta['label']} [Legacy 2025 – {$monthLabel} rollup]",
            'description' => $m['desc'],
            'notes' => $m['notes'],
            'system_id' => $meta['system_id'],
        ];
    }
}

$incomeRows = [];

$bbf = is_file($base.'/legacy_bbf.php') ? require $base.'/legacy_bbf.php' : ['enabled' => false];
if (! empty($bbf['enabled']) && ($bbf['amount'] ?? 0) > 0) {
    $incomeRows[] = [
        'date' => $bbf['date'] ?? '2025-01-01',
        'type' => 'income',
        'amount' => (float) $bbf['amount'],
        'category' => $bbf['category'] ?? 'Income [Admissions – balance brought forward 2025]',
        'description' => $bbf['description'] ?? 'desc_bbf_admissions.txt',
        'notes' => $bbf['notes'] ?? 'notes_bbf_admissions.txt',
        'system_id' => $bbf['system_id'] ?? 'priority_admissions',
    ];
}

// CUG / form-sales / access income: omit here — record via Priority Admissions (legacy) instead of this roll-up file.

foreach ($incomeRows as $row) {
    $tx[] = $row;
}

$novExpense = [
    ['date' => '2025-11-30', 'type' => 'expense', 'amount' => 60276.0, 'category' => 'Agriculture [Legacy 2025 – November rollup]', 'description' => 'desc_2025-11-agriculture.txt', 'notes' => '2025-11.txt', 'system_id' => 'priority_agriculture'],
    ['date' => '2025-11-30', 'type' => 'expense', 'amount' => 640.0, 'category' => 'Geky Media [Legacy 2025 – November rollup]', 'description' => 'desc_2025-11-geky.txt', 'notes' => '2025-11.txt', 'system_id' => 'gekymedia'],
    ['date' => '2025-11-30', 'type' => 'expense', 'amount' => 3730.0, 'category' => 'Admissions [Legacy 2025 – November ops]', 'description' => 'desc_2025-11-admissions-expense.txt', 'notes' => '2025-11.txt', 'system_id' => 'priority_admissions'],
    ['date' => '2025-11-30', 'type' => 'expense', 'amount' => 36715.0, 'category' => 'CEO Personal [Legacy 2025 – November remainder]', 'description' => 'desc_2025-11-ceo.txt', 'notes' => '2025-11.txt', 'system_id' => 'personal_ceo'],
];
foreach ($novExpense as $row) {
    $tx[] = $row;
}

$tx[] = ['date' => '2025-11-30', 'type' => 'income', 'amount' => 3000.0, 'category' => 'Income [Salary & Consulting – Legacy 2025]', 'description' => 'desc_2025-11-income.txt', 'notes' => '2025-11-income.txt', 'system_id' => 'personal_ceo'];

usort($tx, function (array $a, array $b): int {
    $d = strcmp($a['date'], $b['date']);
    if ($d !== 0) {
        return $d;
    }
    if (($a['type'] ?? '') !== ($b['type'] ?? '')) {
        return ($a['type'] ?? '') === 'expense' ? -1 : 1;
    }

    return 0;
});

$transactions = [];
foreach ($tx as $row) {
    $notesRaw = $read($row['notes']);
    $transactions[] = [
        'date' => $row['date'],
        'type' => $row['type'],
        'amount' => $row['amount'],
        'category' => $row['category'],
        'description' => $read($row['description']),
        'notes' => legacySanitizeDiaryForNotes($notesRaw),
        'system_id' => $row['system_id'],
        'user_id' => null,
    ];
}

$data = [
    'meta' => [
        'generated' => date('c'),
        'label' => 'Jan–Nov 2025 legacy rollups',
        'split_by_system' => 'Jan–Oct: amounts from bucket_splits_2025/*.json when present (curated line items per system); else keyword split + stub (legacy_static_splits.php). Full diary text in transaction notes. November: fixed manual rows.',
        'expense_monthly_close' => 'Curated JSON: four buckets must sum to the monthly total in legacy_2025_expense_config.php. Fallback parser: remainder to CEO in legacy_expense_split.php.',
        'curated_buckets' => 'Edit bucket_splits_2025/{personal_ceo,gekymedia,priority_admissions,priority_agriculture}.json; run audit_bucket_splits_2025.php; regenerate JSON.',
        'bbf_admissions' => 'legacy_bbf.php is disabled by default. Opening BBF / form-fee carry-in should be recorded in Priority Admissions (legacy), not this generator. Set enabled=true only if you intentionally add a one-off BBF line here.',
        'admissions_forms_income' => 'Monthly CUG/forms/access income is not included in this JSON — import or enter those via Priority Admissions (legacy).',
        'verification' => [
            'july' => 'GHC 17,538 = dated lines (GHC 13,438) + LOANS summary block (GHC 4,100). May overlap Sammy lines — verify.',
            'november_split' => 'Agriculture/admissions/Geky/CEO amounts are reconciled splits — verify against bank and move lines if needed.',
            'august_income' => 'Former roll-up included GHC 5,880 forms/access income — now excluded; reconcile in Priority Admissions if needed.',
            'stub_months' => 'Aug–Oct expense bucket amounts may use legacy_static_splits.php when diary files are stubs; edit that file or paste full month text and regenerate.',
        ],
    ],
    'transactions' => $transactions,
];

$out = $base.'/transactions_2025.json';
file_put_contents($out, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo 'Wrote '.$out.' ('.count($transactions).' transactions)'.PHP_EOL;
