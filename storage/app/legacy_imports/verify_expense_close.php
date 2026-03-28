<?php
$path = __DIR__.'/transactions_2025.json';
$j = json_decode(file_get_contents($path), true);
$byMonth = [];
$byMonthCats = []; // ym => system_id => sum

foreach ($j['transactions'] as $t) {
    if (($t['type'] ?? '') !== 'expense') {
        continue;
    }
    $ym = substr($t['date'], 0, 7);
    $amt = (float) $t['amount'];
    $byMonth[$ym] = ($byMonth[$ym] ?? 0) + $amt;
    $sid = $t['system_id'] ?? 'unknown';
    $byMonthCats[$ym][$sid] = ($byMonthCats[$ym][$sid] ?? 0) + $amt;
}

ksort($byMonth);

$expected = [
    '2025-01' => 9627.0,
    '2025-02' => 11636.5,
    '2025-03' => 18144.0,
    '2025-04' => 13225.0,
    '2025-05' => 16141.5,
    '2025-06' => 15743.0,
    '2025-07' => 17538.0,
    '2025-08' => 28578.5,
    '2025-09' => 40190.3,
    '2025-10' => 16006.0,
    '2025-11' => 60276.0 + 640.0 + 3730.0 + 36715.0, // 101361
];

echo "Expense reconciliation: sum of 4 categories vs diary month total\n";
echo str_repeat('-', 80)."\n";
printf("%-10s %14s %14s %10s\n", 'Month', 'JSON sum', 'Expected', 'Delta');
echo str_repeat('-', 80)."\n";

$allOk = true;
foreach ($expected as $ym => $exp) {
    $got = round($byMonth[$ym] ?? 0, 2);
    $delta = round($got - $exp, 2);
    $ok = abs($delta) < 0.02;
    if (! $ok) {
        $allOk = false;
    }
    printf("%-10s %14s %14s %10s  %s\n", $ym, number_format($got, 2), number_format($exp, 2), $delta === 0.0 ? '0' : number_format($delta, 2), $ok ? 'OK' : 'MISMATCH');
}

// Any extra months in JSON (e.g. Dec)?
foreach ($byMonth as $ym => $v) {
    if (! isset($expected[$ym])) {
        echo "Note: JSON has expenses for {$ym} = ".number_format($v, 2)." (no expected row in script)\n";
    }
}

echo str_repeat('-', 80)."\n";
$totalExp = array_sum($byMonth);
echo 'Total expense rows (all months): '.number_format($totalExp, 2)."\n";
echo 'Four-category breakdown for 2025-11:\n';
if (isset($byMonthCats['2025-11'])) {
    foreach ($byMonthCats['2025-11'] as $sid => $s) {
        echo "  {$sid}: ".number_format($s, 2)."\n";
    }
}

exit($allOk ? 0 : 1);
