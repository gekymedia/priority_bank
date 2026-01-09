@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-3xl font-bold mb-6">Priority Bank API Documentation</h1>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h2 class="text-lg font-semibold text-blue-900 mb-2">Base URL</h2>
        <code class="text-blue-800">{{ $baseUrl }}/api</code>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold mb-4">Authentication</h2>
        <p class="text-gray-700 mb-4">
            All API requests require authentication using a Bearer token. Include your API key in the Authorization header:
        </p>
        <pre class="bg-gray-100 p-4 rounded overflow-x-auto"><code>Authorization: Bearer YOUR_API_TOKEN</code></pre>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold mb-4">Endpoints</h2>
        
        <div class="space-y-6">
            <div>
                <h3 class="text-xl font-semibold mb-2">Income</h3>
                <div class="ml-4 space-y-2">
                    <div>
                        <code class="bg-gray-100 px-2 py-1 rounded">GET /api/income</code>
                        <span class="text-gray-600 ml-2">List all income records</span>
                    </div>
                    <div>
                        <code class="bg-gray-100 px-2 py-1 rounded">POST /api/income</code>
                        <span class="text-gray-600 ml-2">Create a new income record</span>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-xl font-semibold mb-2">Expenses</h3>
                <div class="ml-4 space-y-2">
                    <div>
                        <code class="bg-gray-100 px-2 py-1 rounded">GET /api/expenses</code>
                        <span class="text-gray-600 ml-2">List all expense records</span>
                    </div>
                    <div>
                        <code class="bg-gray-100 px-2 py-1 rounded">POST /api/expenses</code>
                        <span class="text-gray-600 ml-2">Create a new expense record</span>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-xl font-semibold mb-2">Loans</h3>
                <div class="ml-4 space-y-2">
                    <div>
                        <code class="bg-gray-100 px-2 py-1 rounded">GET /api/loans</code>
                        <span class="text-gray-600 ml-2">List all loan records</span>
                    </div>
                    <div>
                        <code class="bg-gray-100 px-2 py-1 rounded">POST /api/loans</code>
                        <span class="text-gray-600 ml-2">Create a new loan record</span>
                    </div>
                    <div>
                        <code class="bg-gray-100 px-2 py-1 rounded">POST /api/loans/{id}/return</code>
                        <span class="text-gray-600 ml-2">Mark loan as returned</span>
                    </div>
                    <div>
                        <code class="bg-gray-100 px-2 py-1 rounded">POST /api/loans/{id}/lost</code>
                        <span class="text-gray-600 ml-2">Mark loan as lost</span>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-xl font-semibold mb-2">Dashboard</h3>
                <div class="ml-4 space-y-2">
                    <div>
                        <code class="bg-gray-100 px-2 py-1 rounded">GET /api/dashboard/summary</code>
                        <span class="text-gray-600 ml-2">Get financial summary</span>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-xl font-semibold mb-2">Central Finance API</h3>
                <div class="ml-4 space-y-2">
                    <div>
                        <code class="bg-gray-100 px-2 py-1 rounded">POST /api/central-finance/income</code>
                        <span class="text-gray-600 ml-2">Push income to external system</span>
                    </div>
                    <div>
                        <code class="bg-gray-100 px-2 py-1 rounded">POST /api/central-finance/expense</code>
                        <span class="text-gray-600 ml-2">Push expense to external system</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold mb-4">Example Request</h2>
        <pre class="bg-gray-100 p-4 rounded overflow-x-auto"><code>curl -X POST {{ $baseUrl }}/api/income \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 1000.00,
    "income_category_id": 1,
    "date": "2026-01-08",
    "channel": "bank",
    "account_id": 1,
    "notes": "Salary payment"
  }'</code></pre>
    </div>

    <div class="mt-6">
        <a href="{{ route('api-keys.index') }}" 
           class="text-blue-500 hover:text-blue-700 underline">
            ← Back to API Keys
        </a>
    </div>
</div>
@endsection
