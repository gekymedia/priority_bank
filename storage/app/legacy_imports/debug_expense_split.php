<?php
require __DIR__.'/legacy_expense_split.php';

$months = [
    '2025-01.txt' => 9627.0,
    '2025-02.txt' => 11636.5,
    '2025-03.txt' => 18144.0,
    '2025-04.txt' => 13225.0,
    '2025-05.txt' => 16141.5,
    '2025-06.txt' => 15743.0,
    '2025-07.txt' => 17538.0,
    '2025-08.txt' => 28578.5,
    '2025-09.txt' => 40190.3,
    '2025-10.txt' => 16006.0,
];

foreach ($months as $file => $expected) {
    $path = __DIR__.'/'.$file;
    if (! is_file($path)) {
        echo "MISSING $file\n";
        continue;
    }
    $text = file_get_contents($path);
    $b = legacySplitExpenseText($text, $expected);
    echo "=== $file (expected $expected) ===\n";
    echo 'CEO: '.$b['personal_ceo'].' | Geky: '.$b['gekymedia'].' | Adm: '.$b['priority_admissions'].' | Agri: '.$b['priority_agriculture']."\n";
    echo 'Sum check: '.$b['parsed_sum']."\n\n";
}
