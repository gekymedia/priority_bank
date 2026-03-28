<?php

/**
 * Optional overrides for transactions:import-legacy user_id when JSON omits user_id.
 * If unset, the importer uses systems_registry.user_id for the row's system_id, then LEGACY_IMPORT_USER_ID.
 *
 * Production: run `php artisan bank:report-legacy-system-owners` and copy ids into .env if registry links are missing.
 */
return [
    'system_user_map' => array_filter([
        'personal_ceo' => env('LEGACY_USER_PERSONAL_CEO'),
        'gekymedia' => env('LEGACY_USER_GEKYMEDIA'),
        'priority_admissions' => env('LEGACY_USER_PRIORITY_ADMISSIONS'),
        'priority_agriculture' => env('LEGACY_USER_PRIORITY_AGRICULTURE'),
    ], fn ($v) => $v !== null && $v !== ''),
];
