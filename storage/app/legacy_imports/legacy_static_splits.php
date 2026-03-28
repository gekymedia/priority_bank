<?php
/**
 * Manual expense splits (GHC) for months whose diary file is still a short stub
 * (< ~4k chars). When you paste the full month into 2025-MM.txt, run the
 * generator again — file size will exceed the threshold and keyword split
 * replaces these numbers automatically.
 *
 * Keys: YYYY-MM. Sums must match the monthly expense total in generate script.
 */
return [
    '2025-08' => [
        'personal_ceo' => 15278.5,
        'gekymedia' => 800.0,
        'priority_admissions' => 800.0,
        'priority_agriculture' => 11700.0,
    ],
    '2025-09' => [
        'personal_ceo' => 10190.3,
        'gekymedia' => 2000.0,
        'priority_admissions' => 15000.0,
        'priority_agriculture' => 13000.0,
    ],
    '2025-10' => [
        'personal_ceo' => 12517.0,
        'gekymedia' => 167.0,
        'priority_admissions' => 70.0,
        'priority_agriculture' => 667.0,
    ],
];
