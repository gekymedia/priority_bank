<?php
/**
 * Builds bucket_splits_2025/{personal_ceo,gekymedia,priority_admissions,priority_agriculture}.json
 * from diary files + monthly totals. Stub months with legacy_static_splits.php get one rollup line per bucket.
 * Other months: line-level keyword classification + optional CEO remainder line.
 *
 *   php storage/app/legacy_imports/seed_bucket_splits_2025.php
 */

require __DIR__.'/legacy_expense_split.php';

$config = require __DIR__.'/legacy_2025_expense_config.php';
$base = __DIR__;
$staticAll = is_file($base.'/legacy_static_splits.php')
    ? require $base.'/legacy_static_splits.php'
    : [];
$outDir = $base.'/bucket_splits_2025';

if (! is_dir($outDir)) {
    mkdir($outDir, 0775, true);
}

$keys = ['personal_ceo', 'gekymedia', 'priority_admissions', 'priority_agriculture'];
$store = [];
foreach ($keys as $k) {
    $store[$k] = [];
    foreach ($config['expense_months'] as $m) {
        $store[$k][$m['ym']] = [];
    }
}

foreach ($config['expense_months'] as $m) {
    $ym = $m['ym'];
    $expected = (float) $m['total'];
    $notesPath = $base.'/'.$m['notes'];
    if (! is_file($notesPath)) {
        throw new RuntimeException("Missing diary: {$m['notes']}");
    }
    $text = (string) file_get_contents($notesPath);
    $stub = filesize($notesPath) < (int) $config['stub_max_bytes'];

    if ($stub && isset($staticAll[$ym])) {
        foreach ($staticAll[$ym] as $bucket => $amt) {
            if (! in_array($bucket, $keys, true)) {
                continue;
            }
            $a = round((float) $amt, 2);
            if ($a <= 0) {
                continue;
            }
            $store[$bucket][$ym][] = [
                'line' => '[Rollup] Stub month bucket total — itemize in this file if you want each line listed',
                'amount' => $a,
            ];
        }

        continue;
    }

    $items = legacyExtractExpenseLineItems($text);
    $sums = array_fill_keys($keys, 0.0);
    foreach ($items as $it) {
        $sums[$it['bucket']] += $it['amount'];
    }
    $parsed = round(array_sum($sums), 2);
    $remainder = round($expected - $parsed, 2);

    foreach ($items as $it) {
        $store[$it['bucket']][$ym][] = [
            'line' => $it['line'],
            'amount' => $it['amount'],
        ];
    }
    if (abs($remainder) > 0.009) {
        $store['personal_ceo'][$ym][] = [
            'line' => '[Month close alignment] Remainder to CEO (diary monthly total minus sum of classified lines)',
            'amount' => $remainder,
        ];
    }
}

foreach ($keys as $k) {
    $path = $outDir.'/'.$k.'.json';
    file_put_contents(
        $path,
        json_encode($store[$k], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n"
    );
    echo "Wrote {$path}\n";
}

echo "Done. Run: php storage/app/legacy_imports/audit_bucket_splits_2025.php\n";
