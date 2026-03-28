<?php

/**
 * Curated per-bucket line items (Jan–Oct 2025): four JSON files under bucket_splits_2025/.
 * When present and valid, amounts come only from these files; full diary stays in transaction notes.
 */

require_once __DIR__.'/legacy_expense_split.php';

const LEGACY_CURATED_BUCKET_DIR = 'bucket_splits_2025';

const LEGACY_CURATED_BUCKET_KEYS = ['personal_ceo', 'gekymedia', 'priority_admissions', 'priority_agriculture'];

/**
 * Remove generator stub-padding lines from diary text used in UI notes.
 */
function legacySanitizeDiaryForNotes(string $text): string
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $lines = explode("\n", $text);
    $out = [];
    foreach ($lines as $line) {
        $t = trim($line);
        if ($t === '') {
            $out[] = $line;

            continue;
        }
        if (preg_match('/^\/\/\s*---\s*legacy:\s*pad/i', $t)) {
            continue;
        }
        if (preg_match('/^\/\/\s*pad\s+\d+$/i', $t)) {
            continue;
        }

        $out[] = $line;
    }

    return rtrim(implode("\n", $out), "\n")."\n";
}

function legacyCuratedBucketDir(string $baseDir): string
{
    return $baseDir.'/'.LEGACY_CURATED_BUCKET_DIR;
}

/**
 * @return array{personal_ceo: float, gekymedia: float, priority_admissions: float, priority_agriculture: float, parsed_sum: float}|null
 */
function legacyTryLoadCuratedBuckets(string $baseDir, string $ymKey): ?array
{
    $dir = legacyCuratedBucketDir($baseDir);
    if (! is_dir($dir)) {
        return null;
    }

    $buckets = [
        'personal_ceo' => 0.0,
        'gekymedia' => 0.0,
        'priority_admissions' => 0.0,
        'priority_agriculture' => 0.0,
    ];

    foreach (LEGACY_CURATED_BUCKET_KEYS as $key) {
        $path = $dir.'/'.$key.'.json';
        if (! is_file($path)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data) || ! isset($data[$ymKey]) || ! is_array($data[$ymKey])) {
            return null;
        }
        foreach ($data[$ymKey] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $buckets[$key] += (float) ($row['amount'] ?? 0);
        }
    }

    $buckets['parsed_sum'] = round(array_sum([
        $buckets['personal_ceo'],
        $buckets['gekymedia'],
        $buckets['priority_admissions'],
        $buckets['priority_agriculture'],
    ]), 2);

    return $buckets;
}

/**
 * @param  array{personal_ceo: float, gekymedia: float, priority_admissions: float, priority_agriculture: float, parsed_sum: float}  $buckets
 */
function legacyAssertCuratedMatchesExpected(array $buckets, float $expectedTotal, string $ymKey): void
{
    $sum = round(
        $buckets['personal_ceo'] + $buckets['gekymedia'] + $buckets['priority_admissions'] + $buckets['priority_agriculture'],
        2
    );
    $exp = round($expectedTotal, 2);
    if (abs($sum - $exp) > 0.02) {
        throw new RuntimeException(
            "Curated bucket_splits_2025 for {$ymKey} sums to {$sum}, expected monthly total {$exp}"
        );
    }
}
