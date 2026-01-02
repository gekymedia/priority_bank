# Priority Bank - Deployment Configuration

## Production URL

Priority Bank will be hosted on: **https://prioritybank.gekymedia.com**

## Environment Configuration

### Priority Bank `.env` Settings

```env
APP_NAME="Priority Bank"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://prioritybank.gekymedia.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=priority_bank
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Priority Bank API Configuration (for webhooks)
PRIORITY_BANK_API_URL=https://prioritybank.gekymedia.com
```

## External Systems Configuration

Each external system needs to add these to their `.env`:

```env
# Priority Bank API Integration
PRIORITY_BANK_API_URL=https://prioritybank.gekymedia.com
PRIORITY_BANK_API_TOKEN=your_system_specific_token_here
PRIORITY_BANK_API_TIMEOUT=10
PRIORITY_BANK_API_MAX_RETRIES=3
```

## API Token Generation

Generate API tokens for each system using Laravel Sanctum:

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Str;

// Get or create admin user
$admin = User::where('email', 'admin@prioritybank.com')->first();

// Generate tokens for each system
$systems = [
    'gekymedia' => 'Gekymedia System',
    'priority_solutions_agency' => 'Priority Solutions Agency',
    'priority_accommodation' => 'Priority Accommodation',
    'priority_agriculture' => 'Priority Agriculture',
    'schoolsgh' => 'SchoolsGH',
    'priority_admissions' => 'Priority Admissions (CUG)',
];

foreach ($systems as $systemId => $systemName) {
    $token = $admin->createToken("{$systemId}-api-token")->plainTextToken;
    echo "{$systemName} ({$systemId}): {$token}\n";
}
```

**Save these tokens securely** and share with each system's administrator.

## Webhook Callback URLs

Update the systems registry with each system's callback URL:

```php
// In Priority Bank, update systems_registry table
use App\Models\SystemRegistry;

$callbacks = [
    'gekymedia' => 'https://gekymedia.com',
    'priority_solutions_agency' => 'https://prioritysolutionsagency.com',
    'priority_accommodation' => 'https://priorityaccommodation.com',
    'priority_agriculture' => 'https://priorityagriculture.com',
    'schoolsgh' => 'https://schoolsgh.com',
    'priority_admissions' => 'https://cug.prioritysolutionsagency.com',
];

foreach ($callbacks as $systemId => $callbackUrl) {
    SystemRegistry::where('system_id', $systemId)
        ->update(['callback_url' => $callbackUrl]);
}
```

## SSL/HTTPS Requirements

- All API calls must use HTTPS
- Ensure SSL certificate is valid
- Update CORS settings if needed

## Next Steps

1. Deploy Priority Bank to gekymedia subdomain
2. Run migrations: `php artisan migrate`
3. Seed systems registry: `php artisan db:seed --class=SystemsRegistrySeeder`
4. Generate API tokens for each system
5. Update callback URLs in systems registry
6. Implement integration in each external system (see implementation files)

