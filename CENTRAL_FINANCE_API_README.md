# Priority Bank - Central Finance API

## Overview

Priority Bank now serves as the **Central Finance System** for all Priority Group systems. It provides bidirectional financial data synchronization, allowing all systems to push income/expense data to Priority Bank and receive data back when entries are made centrally.

## Features

✅ **System Registry**: Centralized registry of all connected systems  
✅ **Idempotent Transactions**: Prevents duplicate transactions using idempotency keys  
✅ **Bidirectional Sync**: Push data to Priority Bank and receive data back via webhooks  
✅ **Audit Trail**: Complete tracking of external system references and sync status  
✅ **Automatic Retry**: Built-in retry logic with exponential backoff  
✅ **Webhook Support**: Push data back to external systems when CEO creates entries  

## Database Changes

### New Tables

1. **systems_registry**: Registry of all connected systems
   - `system_id`: Unique identifier (e.g., 'gekymedia', 'schoolsgh')
   - `name`: Display name
   - `type`: manual | automated | hybrid
   - `callback_url`: Webhook URL for pushing data back
   - `active_status`: Enable/disable system

### Modified Tables

1. **incomes**: Added fields for external system tracking
   - `external_system_id`: Reference to systems_registry
   - `external_transaction_id`: Transaction ID from external system
   - `idempotency_key`: Unique key to prevent duplicates
   - `sync_status`: pending | synced | failed
   - `synced_at`: Timestamp of last sync
   - `sync_error`: Error message if sync failed

2. **expenses**: Same fields as incomes

## API Endpoints

### Push Income to Priority Bank

```
POST /api/central-finance/income
Authorization: Bearer {token}
X-Idempotency-Key: {optional_key}

{
  "system_id": "gekymedia",
  "external_transaction_id": "gekymedia_income_123",
  "amount": 1000.00,
  "date": "2025-01-15",
  "channel": "bank",
  "notes": "Payment from client",
  "income_category_name": "Web Development"
}
```

### Push Expense to Priority Bank

```
POST /api/central-finance/expense
Authorization: Bearer {token}
X-Idempotency-Key: {optional_key}

{
  "system_id": "priority_accommodation",
  "external_transaction_id": "accommodation_expense_456",
  "amount": 500.00,
  "date": "2025-01-15",
  "channel": "momo",
  "notes": "Maintenance work",
  "expense_category_name": "Maintenance"
}
```

## Setup

### 1. Run Migrations

```bash
php artisan migrate
```

### 2. Seed Systems Registry

```bash
php artisan db:seed --class=SystemsRegistrySeeder
```

Or include in DatabaseSeeder (already added):

```bash
php artisan db:seed
```

### 3. Configure API Tokens

Generate API tokens for external systems using Laravel Sanctum:

```bash
php artisan tinker
```

```php
$user = User::where('email', 'admin@prioritybank.com')->first();
$token = $user->createToken('gekymedia-api')->plainTextToken;
```

Share this token with the external system.

### 4. Configure External Systems

Each external system needs to:

1. Copy `PriorityBankApiClient` service class
2. Add to `.env`:
   ```
   PRIORITY_BANK_API_URL=https://prioritybank.example.com
   PRIORITY_BANK_API_TOKEN=their_token_here
   ```
3. Implement integration service (see INTEGRATION_EXAMPLES/)

## Usage in Priority Bank

### When CEO Creates Income/Expense

When the CEO creates an income or expense in Priority Bank and selects an external system:

1. The transaction is saved in Priority Bank
2. If an external system is selected, the webhook service automatically pushes the data to that system
3. The system receives the data via its callback URL and persists it locally

### Viewing External System Transactions

All transactions from external systems are visible in Priority Bank with:
- System name
- External transaction ID
- Sync status
- Original metadata

## Connected Systems

The following systems are registered in the systems registry:

1. **Gekymedia** (`gekymedia`)
   - Multi-Directorate System
   - Type: Hybrid
   - Directorates: Geky Dev, Geky Studios, Geky Prints, Geky Stations

2. **Priority Solutions Agency** (`priority_solutions_agency`)
   - Includes Priority Travels, Priority Nova
   - Type: Hybrid
   - University contracts: CUG, ANGUTECH

3. **Priority Accommodation** (`priority_accommodation`)
   - Rent/bookings income
   - Type: Manual

4. **Priority Agriculture** (`priority_agriculture`)
   - Poultry and crop farm operations
   - Type: Hybrid

5. **SchoolsGH** (`schoolsgh`)
   - Subscription payments
   - Type: Automated

6. **Priority Admissions** (`priority_admissions`)
   - CUG form sales and other services
   - Type: Hybrid

## Integration Examples

See `INTEGRATION_EXAMPLES/` directory for system-specific integration code:

- `CUG_Integration.php`: CUG system integration
- `SchoolsGH_Integration.php`: SchoolsGH integration
- `PriorityAgriculture_Integration.php`: Agriculture system integration

## Testing

### Local Testing

1. Start Priority Bank: `php artisan serve`
2. In external system, set:
   ```
   PRIORITY_BANK_API_URL=http://localhost:8000
   ```
3. Test pushing income/expense

### Production

1. Ensure HTTPS is enabled
2. Use secure API tokens
3. Monitor logs for sync errors
4. Set up webhook endpoints in external systems

## Troubleshooting

### Sync Failures

Check `sync_error` field in incomes/expenses table for error details.

### Webhook Failures

Check logs:
```bash
tail -f storage/logs/laravel.log | grep "ExternalSystemWebhookService"
```

### API Connection Issues

Verify:
- API URL is correct
- API token is valid
- Network connectivity
- Firewall rules

## Security

- All API endpoints require Sanctum authentication
- Use HTTPS in production
- Store API tokens securely (environment variables)
- Implement webhook signature validation in external systems
- Use scoped tokens with limited permissions

## Support

For integration support, refer to `INTEGRATION_GUIDE.md` or contact the development team.

