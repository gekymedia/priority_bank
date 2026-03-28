# Priority Bank Production Deployment Script (PowerShell)
# Server: gekymedia.com (path: bank.prioritysolutionsagency.com)
# Path: /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html
#
# Legacy imports (after deploy, from your machine):
#   ssh root@gekymedia.com "cd /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html && php artisan bank:report-legacy-system-owners"
#   Set .env LEGACY_USER_* from output if systems_registry.user_id is missing for a split.
#   php artisan transactions:import-legacy --dry-run --file=storage/app/legacy_imports/transactions_2025.json
#   php artisan transactions:import-legacy --file=storage/app/legacy_imports/transactions_2025.json
# Form-sales-only JSON (copy from CUG export or repo): priority_bank_form_sales_legacy_2025.json
#   php artisan transactions:import-legacy --file=storage/app/legacy_imports/priority_bank_form_sales_legacy_2025.json
#
param(
    [switch]$LegacyImportDryRun
)

Write-Host "Committing and pushing local changes..." -ForegroundColor Cyan
git add .
git commit -m "Deploy: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
if ($LASTEXITCODE -ne 0) { Write-Host "No changes to commit" -ForegroundColor Yellow }
git push origin master

Write-Host "Deploying to production..." -ForegroundColor Cyan
$remoteCmd = 'cd /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html && git pull origin master && composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize && php artisan queue:restart'
ssh root@gekymedia.com $remoteCmd

if ($LegacyImportDryRun) {
    Write-Host "Legacy import dry-run on production bank..." -ForegroundColor Cyan
    $bankRoot = '/home/gekymedia/web/bank.prioritysolutionsagency.com/public_html'
    ssh root@gekymedia.com "cd $bankRoot && php artisan bank:report-legacy-system-owners && php artisan transactions:import-legacy --dry-run --file=storage/app/legacy_imports/transactions_2025.json"
}
