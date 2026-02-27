@extends('layouts.app')
@section('title', 'Sika Wallet Transactions')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold">Sika Wallet Transactions</h1>
            <p class="text-gray-500 mt-1">All wallet transactions</p>
        </div>
        <a href="{{ route('admin.sika-wallet.dashboard') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.sika-wallet.transactions') }}" class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    <option value="DEPOSIT" {{ request('type') === 'DEPOSIT' ? 'selected' : '' }}>Deposit</option>
                    <option value="WITHDRAWAL" {{ request('type') === 'WITHDRAWAL' ? 'selected' : '' }}>Withdrawal</option>
                    <option value="SIKA_COIN_PURCHASE" {{ request('type') === 'SIKA_COIN_PURCHASE' ? 'selected' : '' }}>Sika Coin Purchase</option>
                    <option value="SIKA_COIN_CASHOUT" {{ request('type') === 'SIKA_COIN_CASHOUT' ? 'selected' : '' }}>Sika Coin Cashout</option>
                    <option value="TRANSFER_IN" {{ request('type') === 'TRANSFER_IN' ? 'selected' : '' }}>Transfer In</option>
                    <option value="TRANSFER_OUT" {{ request('type') === 'TRANSFER_OUT' ? 'selected' : '' }}>Transfer Out</option>
                    <option value="REFUND" {{ request('type') === 'REFUND' ? 'selected' : '' }}>Refund</option>
                    <option value="ADJUSTMENT" {{ request('type') === 'ADJUSTMENT' ? 'selected' : '' }}>Adjustment</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Statuses</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pending</option>
                    <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                    <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>Failed</option>
                    <option value="REVERSED" {{ request('status') === 'REVERSED' ? 'selected' : '' }}>Reversed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
                <select name="direction" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All</option>
                    <option value="CREDIT" {{ request('direction') === 'CREDIT' ? 'selected' : '' }}>Credit</option>
                    <option value="DEBIT" {{ request('direction') === 'DEBIT' ? 'selected' : '' }}>Debit</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference, key..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    Filter
                </button>
                <a href="{{ route('admin.sika-wallet.transactions') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance After</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $txn->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">User #{{ $txn->external_user_id }}</div>
                            <div class="text-sm text-gray-500">{{ ucfirst($txn->source) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                @if($txn->type === 'SIKA_COIN_PURCHASE') bg-purple-100 text-purple-800
                                @elseif($txn->type === 'SIKA_COIN_CASHOUT') bg-indigo-100 text-indigo-800
                                @elseif($txn->type === 'DEPOSIT') bg-blue-100 text-blue-800
                                @elseif($txn->type === 'WITHDRAWAL') bg-orange-100 text-orange-800
                                @else bg-gray-100 text-gray-800
                                @endif
                            ">
                                {{ str_replace('_', ' ', $txn->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-semibold {{ $txn->direction === 'CREDIT' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $txn->direction === 'CREDIT' ? '+' : '-' }}GHS {{ number_format($txn->amount, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            GHS {{ number_format($txn->balance_after, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                @if($txn->status === 'COMPLETED') bg-green-100 text-green-800
                                @elseif($txn->status === 'PENDING') bg-yellow-100 text-yellow-800
                                @elseif($txn->status === 'FAILED') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif
                            ">
                                {{ $txn->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $txn->reference }}</div>
                            <div class="text-xs text-gray-500 truncate max-w-xs" title="{{ $txn->idempotency_key }}">{{ Str::limit($txn->idempotency_key, 20) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $txn->created_at->format('M d, Y') }}<br>
                            <span class="text-xs">{{ $txn->created_at->format('H:i:s') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No transactions found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $transactions->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
