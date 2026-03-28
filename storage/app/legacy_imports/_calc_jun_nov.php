<?php
// Best-effort month totals (manual extraction from user paste). Reconcile before production.

$june = [80, 440, 20, 375, 880, 2023, 62, 555, 77, 1379, 339, 510, 265, 460, 570, 233, 1227, 2820, 443, 699, 955, 115, 192, 95, 55, 302, 130, 442];
echo 'June: '.array_sum($june)."\n";

// July: dated lines + end LOANS block (user summary — may overlap dated Sammy lines; verify)
$julyDaily = [260, 100, 729, 0, 210, 560, 506, 30, 165, 2130, 625, 130, 190, 168, 559, 880, 1034, 0, 200, 865, 0, 150, 20, 600, 300, 500, 770, 1757];
$julyLoans = [300, 50, 200, 250, 300, 1000, 200, 200, 150, 50, 550, 100, 100, 400, 250];
echo 'July daily: '.array_sum($julyDaily)."\n";
echo 'July loans block: '.array_sum($julyLoans).' (verify vs diary — may double-count Sammy lines)'."\n";

$augExp = [
    1680, 750, 542, 1670, 1764, 585, 2900, 630, 2400, 370, 1000, 380, 700, 162, 1975, 400, 190, 60, 78, 30, 495, 2271, 101, 1671, 100, 538, 360, 1355, 2310, 361.5, 750,
];
echo 'Aug exp: '.array_sum($augExp)."\n";
$augInc = [1000, 3400, 500, 980];
echo 'Aug income: '.array_sum($augInc)."\n";
