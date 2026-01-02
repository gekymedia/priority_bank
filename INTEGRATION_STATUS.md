# Priority Bank Central Finance API - Integration Status

## Production URL
**Priority Bank API:** `https://prioritybank.gekymedia.com`

## Integration Status

### ✅ COMPLETED: CUG System (Priority Admissions)

**Status:** Fully integrated and ready for testing

**Files Created/Modified:**
- ✅ `cug/app/Services/PriorityBankApiClient.php` - API client
- ✅ `cug/app/Services/PriorityBankIntegrationService.php` - Integration service
- ✅ `cug/app/Http/Controllers/PriorityBankWebhookController.php` - Webhook handler
- ✅ `cug/app/Http/Controllers/IncomeController.php` - Updated to push income
- ✅ `cug/app/Http/Controllers/ExpenseController.php` - Updated to push expense
- ✅ `cug/routes/api.php` - Added webhook routes
- ✅ `cug/config/services.php` - Added Priority Bank config

**Environment Variables Needed:**
```env
PRIORITY_BANK_API_URL=https://prioritybank.gekymedia.com
PRIORITY_BANK_API_TOKEN=token_for_priority_admissions
PRIORITY_BANK_API_TIMEOUT=10
PRIORITY_BANK_API_MAX_RETRIES=3
```

**Webhook URL:** `https://cug.prioritysolutionsagency.com/api/webhook/finance/income` (and `/expense`)

---

### ✅ COMPLETED: SchoolsGH

**Status:** Fully integrated (Landlord + Tenant levels)

---

### ✅ COMPLETED: Priority Agriculture

**Status:** Fully integrated

---

### ✅ COMPLETED: Priority Accommodation

**Status:** Fully integrated and ready for testing

**Files Created/Modified:**
- ✅ `priority_accommodations/app/Services/PriorityBankApiClient.php` - API client
- ✅ `priority_accommodations/app/Services/PriorityBankIntegrationService.php` - Integration service
- ✅ `priority_accommodations/app/Http/Controllers/PriorityBankWebhookController.php` - Webhook handler
- ✅ `priority_accommodations/app/Http/Controllers/PaymentController.php` - Updated to push payments
- ✅ `priority_accommodations/routes/api.php` - Added webhook routes
- ✅ `priority_accommodations/config/services.php` - Added Priority Bank config

**Payment Type Mapping:**
- Rent & Security Deposits → Income
- Maintenance → Expense

**Environment Variables Needed:**
```env
PRIORITY_BANK_API_URL=https://prioritybank.gekymedia.com
PRIORITY_BANK_API_TOKEN=token_for_priority_accommodation
PRIORITY_BANK_API_TIMEOUT=10
PRIORITY_BANK_API_MAX_RETRIES=3
```

**Webhook URLs:** 
- Income: `https://[domain]/api/webhook/finance/income`
- Expense: `https://[domain]/api/webhook/finance/expense`

---

### ✅ COMPLETED: Priority Solutions Agency

**Status:** Fully integrated

---

### ✅ COMPLETED: Gekymedia

**Status:** Fully integrated

---

## Configuration Checklist

### Priority Bank Setup

- [x] System Registry table created
- [x] Systems seeded
- [x] API endpoints created
- [x] Webhook service created
- [ ] Generate API tokens for each system
- [ ] Update callback URLs in systems registry
- [ ] Deploy to gekymedia subdomain

### For Each System

- [ ] Copy PriorityBankApiClient
- [ ] Create integration service
- [ ] Update income/expense controllers
- [ ] Create webhook endpoints
- [ ] Add environment variables
- [ ] Test integration

## API Token Generation

Run in Priority Bank:

```bash
php artisan tinker
```

```php
use App\Models\User;

$admin = User::where('email', 'admin@prioritybank.com')->first();

// Generate token for CUG
$cugToken = $admin->createToken('priority-admissions-api')->plainTextToken;
echo "CUG Token: {$cugToken}\n";

// Generate tokens for other systems as needed
```

## Next Steps

1. **Complete CUG Testing:**
   - Add API token to CUG `.env`
   - Test income push
   - Test expense push
   - Test webhook from Priority Bank

2. **Implement Next System:**
   - Choose next system (SchoolsGH recommended)
   - Follow same pattern as CUG
   - Test thoroughly

3. **Update Systems Registry:**
   - Add callback URLs for each system
   - Mark systems as active

