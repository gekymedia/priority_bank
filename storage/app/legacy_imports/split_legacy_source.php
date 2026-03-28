<?php
/**
 * Split a single paste (Jan–May block) into 2025-01.txt … 2025-05.txt
 * Expects month sections separated by a line like: ===============
 *
 * Usage:
 *   Save your full paste as legacy_source_full.txt in this folder, then:
 *   php storage/app/legacy_imports/split_legacy_source.php
 */

$base = __DIR__;
$src = $base.'/legacy_source_full.txt';
if (! is_file($src)) {
    fwrite(STDERR, "Missing {$src}\nSave your full JANUARY…MAY paste there.\n");
    exit(1);
}

$raw = file_get_contents($src);
$raw = str_replace(["\r\n", "\r"], "\n", $raw);

$map = [
    'JANUARY' => '2025-01.txt',
    'FEBRUARY' => '2025-02.txt',
    'MARCH' => '2025-03.txt',
    'APRIL' => '2025-04.txt',
    'MAY' => '2025-05.txt',
];

$blocks = preg_split('/\n={5,}\s*\n/', $raw);
foreach ($blocks as $b) {
    $b = trim($b);
    if ($b === '') {
        continue;
    }
    if (preg_match('/^(JANUARY|FEBRUARY|MARCH|APRIL|MAY)\s+2025\b/', $b, $m)) {
        $file = $map[$m[1]] ?? null;
        if ($file) {
            file_put_contents($base.'/'.$file, $b."\n");
            echo "Wrote {$file}\n";
        }
    }
}

$inc = $base.'/2025-04-income.txt';
if (! is_file($inc) || filesize($inc) < 20) {
    file_put_contents($inc, "Optional: add a bullet list of April income lines for audit. Amount GHC 22,619 is already in JSON.\n");
    echo "Wrote stub {$inc}\n";
}

$inc5 = $base.'/2025-05-income.txt';
if (! is_file($inc5) || filesize($inc5) < 20) {
    file_put_contents($inc5, "Optional: add May daily income breakdown. Key figure GHC 20,000 is in JSON — verify.\n");
    echo "Wrote stub {$inc5}\n";
}

echo "Done.\n";
