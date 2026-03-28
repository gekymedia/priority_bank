<?php
// Usage: php sum_ghc_from_file.php path/to.txt
// Sums all Ghc/GHC amounts (naive — includes income lines; use for sanity check only).

$s = file_get_contents($argv[1] ?? '');
preg_match_all('/GHC\s*([\d,]+(?:\.\d+)?)/i', $s, $m);
$t = 0;
foreach ($m[1] as $x) {
    $t += (float) str_replace(',', '', $x);
}
echo "Matches: ".count($m[1])."  Sum: {$t}\n";
