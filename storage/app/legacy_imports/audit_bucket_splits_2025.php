<?php
/**
 * Verifies curated JSON sums per month vs legacy_2025_expense_config.php totals.
 * Optional: compare raw keyword line-sum (no remainder) to highlight drift.
 *
 *   php storage/app/legacy_imports/audit_bucket_splits_2025.php
 */

require __DIR__.'/legacy_expense_split.php';
require __DIR__.'/legacy_curated_buckets.php';

$config = require __DIR__.'/legacy_2025_expense_config.php';
$base = __DIR__;

echo "# Legacy 2025 bucket_splits_2025 audit\n\n";
$dir = legacyCuratedBucketDir($base);
if (! is_dir($dir)) {
    echo "MISSING: {$dir} — run seed_bucket_splits_2025.php first.\n";
    exit(1);
}

$allOk = true;
foreach ($config['expense_months'] as $m) {
    $ym = $m['ym'];
    $expected = (float) $m['total'];
    $b = legacyTryLoadCuratedBuckets($base, $ym);
    if ($b === null) {
        echo "## {$ym} FAIL — incomplete curated JSON\n";
        $allOk = false;

        continue;
    }
    try {
        legacyAssertCuratedMatchesExpected($b, $expected, $ym);
    } catch (Throwable $e) {
        echo "## {$ym} FAIL — {$e->getMessage()}\n";
        $allOk = false;

        continue;
    }
    $notesPath = $base.'/'.$m['notes'];
    $text = is_file($notesPath) ? (string) file_get_contents($notesPath) : '';
    $items = legacyExtractExpenseLineItems($text);
    $raw = 0.0;
    foreach ($items as $it) {
        $raw += $it['amount'];
    }
    $raw = round($raw, 2);
    $gap = round($expected - $raw, 2);

    echo "## {$ym} OK — expected GHC {$expected}\n";
    echo '| Bucket | GHC |'."\n";
    echo '|--------|-----|'."\n";
    foreach (LEGACY_CURATED_BUCKET_KEYS as $k) {
        echo '| '.$k.' | '.number_format($b[$k], 2).' |'."\n";
    }
    echo '| **Sum** | **'.number_format($b['parsed_sum'], 2).'** |'."\n\n";
    echo "- Classified line items sum (no remainder): GHC {$raw}\n";
    echo "- Gap vs monthly close (→ CEO remainder in seed): GHC {$gap}\n\n";
}

echo $allOk ? "All months passed.\n" : "Some months failed.\n";
exit($allOk ? 0 : 1);
