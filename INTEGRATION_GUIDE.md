# Priority Bank Central Finance API - Integration Guide

This guide explains how to integrate external systems with Priority Bank's Central Finance API for bidirectional financial data synchronization.

## Overview

Priority Bank acts as the central finance system that:
- Accepts income & expense data from all connected systems
- Pushes finance data back to originating systems when entries are made centrally
- Maintains idempotency to prevent duplicate transactions
- Provides audit trails across all systems

## Architecture

```
External System → Priority Bank API → Priority Bank Database
                    ↓
              Webhook Service → External System Callback URL
```

## Setup

### 1. Configure Priority Bank API Access

Add to your external system's `.env`:

```env
PRIORITY_BANK_API_URL=https://prioritybank.example.com
PRIORITY_BANK_API_TOKEN=your_api_token_here
PRIORITY_BANK_API_TIMEOUT=10
PRIORITY_BANK_API_MAX_RETRIES=3
```

### 2. Install Priority Bank API Client

Copy the `PriorityBankApiClient` service class to your external system, or use it as a reference to implement your own client.

## Pushing Data to Priority Bank

### Income Example

```php
use App\Services\PriorityBankApiClient;

$client = new PriorityBankApiClient();

$result = $client->pushIncome(
    systemId: 'gekymedia',
    externalTransactionId: 'gekymedia_income_12345',
    amount: 1000.00,
    date: '2025-01-15',
    channel: 'bank',
    options: [
        'notes' => 'Payment from client for website development',
        'income_category_name' => 'Web Development',
        'metadata' => [
            'directorate' => 'geky_dev',
            'project' => 'Fabamall',
        ],
    ]
);

if ($result && $result['success']) {
    // Store Priority Bank transaction ID
    $priorityBankTransactionId = $result['data']['id'];
    // Update local record with sync status
} else {
    // Handle error
}
```

### Expense Example

```php
$result = $client->pushExpense(
    systemId: 'priority_accommodation',
    externalTransactionId: 'accommodation_expense_67890',
    amount: 500.00,
    date: '2025-01-15',
    channel: 'momo',
    options: [
        'notes' => 'Maintenance work on property',
        'expense_category_name' => 'Maintenance',
    ]
);
```

## Receiving Data from Priority Bank (Webhook)

When Priority Bank creates an income/expense and selects your system, it will POST to your callback URL.

### Webhook Endpoint Setup

Create a webhook endpoint in your system:

```php
// routes/api.php
Route::post('/webhook/finance/income', [FinanceWebhookController::class, 'handleIncome']);
Route::post('/webhook/finance/expense', [FinanceWebhookController::class, 'handleExpense']);
```

### Webhook Controller Example

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinanceWebhookController extends Controller
{
    public function handleIncome(Request $request)
    {
        // Validate webhook signature if needed
        $data = $request->all();
        
        // Find or create income record in your system
        $income = YourIncomeModel::updateOrCreate(
            [
                'priority_bank_transaction_id' => $data['priority_bank_transaction_id'],
            ],
            [
                'amount' => $data['amount'],
                'date' => $data['date'],
                'channel' => $data['channel'],
                'notes' => $data['notes'],
                'category' => $data['category'],
                'synced_at' => $data['synced_at'],
            ]
        );
        
        return response()->json(['success' => true, 'data' => $income]);
    }
    
    public function handleExpense(Request $request)
    {
        // Similar implementation for expenses
    }
}
```

## System-Specific Integration Examples

### Gekymedia System

```php
// When income is recorded in Gekymedia
public function recordIncome($directorate, $amount, $date, $notes)
{
    // Save to local database
    $income = GekymediaIncome::create([...]);
    
    // Push to Priority Bank
    $client = new PriorityBankApiClient();
    $client->pushIncome(
        systemId: 'gekymedia',
        externalTransactionId: 'gekymedia_' . $income->id,
        amount: $amount,
        date: $date,
        channel: 'bank',
        options: [
            'notes' => $notes,
            'income_category_name' => $directorate,
            'metadata' => ['directorate' => $directorate],
        ]
    );
}
```

### SchoolsGH System

```php
// When subscription payment is received
public function handleSubscriptionPayment($subscription)
{
    // Save to local database
    $income = Income::create([
        'income_category_id' => IncomeCategory::where('name', 'Subscription Payments')->first()->id,
        'amount' => $subscription->amount,
        'date' => $subscription->paid_at,
        'description' => "Subscription payment from {$subscription->school->name}",
    ]);
    
    // Push to Priority Bank
    $client = new PriorityBankApiClient();
    $client->pushIncome(
        systemId: 'schoolsgh',
        externalTransactionId: 'schoolsgh_sub_' . $subscription->id,
        amount: $subscription->amount,
        date: $subscription->paid_at->format('Y-m-d'),
        channel: 'bank', // or 'momo' based on payment method
        options: [
            'notes' => "Subscription payment from {$subscription->school->name}",
            'income_category_name' => 'Subscription Payments',
        ]
    );
}
```

### CUG System (Priority Admissions)

```php
// When form sale is completed
public function handleFormSale($formSale)
{
    // Save to local database
    $income = IncomeRecord::create([
        'source' => 'CUG Form Sale',
        'amount' => $formSale->amount,
        'received_at' => now(),
        'reference' => $formSale->reference,
    ]);
    
    // Push to Priority Bank
    $client = new PriorityBankApiClient();
    $client->pushIncome(
        systemId: 'priority_admissions',
        externalTransactionId: 'cug_form_' . $formSale->id,
        amount: $formSale->amount,
        date: now()->format('Y-m-d'),
        channel: 'bank', // or based on payment method
        options: [
            'notes' => "CUG form sale - Reference: {$formSale->reference}",
            'income_category_name' => 'Form Sales',
        ]
    );
}
```

## Idempotency

The API uses idempotency keys to prevent duplicate transactions. You can:

1. **Let the system generate it** (recommended): The API will generate a key based on `system_id` and `external_transaction_id`
2. **Provide your own**: Include `idempotency_key` in the options array

If a transaction with the same idempotency key already exists, the API will return the existing transaction instead of creating a duplicate.

## Error Handling

The API client includes automatic retry logic with exponential backoff for server errors (5xx). Client errors (4xx) are not retried.

Always check the response:

```php
$result = $client->pushIncome(...);

if ($result && $result['success']) {
    // Success
} else {
    // Log error and handle appropriately
    Log::error('Failed to push income to Priority Bank', [
        'error' => $result['message'] ?? 'Unknown error',
    ]);
}
```

## Testing

For local development, you can use:

```env
PRIORITY_BANK_API_URL=http://localhost:8000
PRIORITY_BANK_API_TOKEN=your_test_token
```

Make sure to run migrations and seeders in Priority Bank to set up the systems registry.

## Security

- Use HTTPS in production
- Store API tokens securely (environment variables)
- Validate webhook signatures if implementing callbacks
- Use scoped API tokens with limited permissions

## Support

For issues or questions, contact the Priority Bank development team.

