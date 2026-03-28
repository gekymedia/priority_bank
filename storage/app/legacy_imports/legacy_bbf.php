<?php
/**
 * Balance brought forward (admissions income) — opening line dated 2025-01-01.
 *
 * Default: implied CUG form revenue for ALL applicants with created_at < 2025-01-01,
 * using pre–June 2025 fee assumption (UG GHC 130, PG GHC 180). Recount in CUG if needed.
 *
 * Optional: use 36335.0 for 50% of pre-2025 implied fees if you ever need a lower BBF.
 *
 * Default disabled: opening form-fee / BBF is tracked via Priority Admissions (legacy) instead of this file.
 */
return [
    'enabled' => false,
    'date' => '2025-01-01',
    'amount' => 72670.0,
    'category' => 'Income [Admissions – balance brought forward 2025]',
    'description' => 'desc_bbf_admissions.txt',
    'notes' => 'notes_bbf_admissions.txt',
    'system_id' => 'priority_admissions',
];
