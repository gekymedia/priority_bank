# Priority Bank Production Deployment Script (PowerShell)
# Server: gekymedia.com (path: bank.prioritysolutionsagency.com)
# Path: /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html

Write-Host "Committing and pushing local changes..." -ForegroundColor Cyan
git add .
git commit -m "Deploy: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
if ($LASTEXITCODE -ne 0) { Write-Host "No changes to commit" -ForegroundColor Yellow }
git push origin master

Write-Host "Deploying to production..." -ForegroundColor Cyan
$remoteCmd = 'cd /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html && git pull origin master && composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize && php artisan queue:restart'
ssh root@gekymedia.com $remoteCmd
