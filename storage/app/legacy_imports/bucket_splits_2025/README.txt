Curated expense splits for Jan–Oct 2025 (four JSON files).

Source of truth for transaction AMOUNTS per system (CEO, Geky Media, Admissions, Agriculture).
Full month diary text still lives in 2025-MM.txt and is attached to each row as notes (padding lines stripped).

Regenerate from keyword classification + monthly totals:
  php storage/app/legacy_imports/seed_bucket_splits_2025.php

Verify sums match legacy_2025_expense_config.php:
  php storage/app/legacy_imports/audit_bucket_splits_2025.php

Edit the JSON to move lines between buckets; keep each month’s four-bucket sum equal to the config total.
Then:
  php storage/app/legacy_imports/generate_transactions_2025_json.php
