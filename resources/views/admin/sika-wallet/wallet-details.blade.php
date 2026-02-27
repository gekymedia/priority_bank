@extends('layouts.app')
@section('title', 'Wallet Details')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold">Wallet Details</h1>
            <p class="text-gray-500 mt-1">External User #{{ $wallet->external_user_id }} ({{ ucfirst($wallet->source) }})</p>
        </div>
        <a href="{{ route('admin.sika-wallet.wallets') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Wallets
        </a>
    </div>

    <!-- Wallet Info Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <p class="text-sm text-gray-500">Wallet ID</p>
                <p class="text-lg font-semibold">{{ $wallet->id }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">External User</p>
                <p class="text-lg font-semibold">#{{ $wallet->external_user_id }}</p>
                <p class="text-sm text-gray-500">Source: {{ ucfirst($wallet->source) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Balance</p>
                <p class="text-2xl font-bold text-green-600">GHS {{ number_format($wallet->balance, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                    @if($wallet->status === 'active') bg-green-100 text-green-800
                    @elseif($wallet->status === 'suspended') bg-yellow-100 text-yellow-800
                    @else bg-red-100 text-red-800
                    @endif
                ">
                    {{ ucfirst($wallet->status) }}
                </span>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Currency:</span>
                    <span class="font-medium ml-2">{{ $wallet->currency }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Created:</span>
                    <span class="font-medium ml-2">{{ $wallet->created_at->format('M d, Y H:i') }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Last Updated:</span>
                    <span class="font-medium ml-2">{{ $wallet->updated_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Transaction History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance Before</th>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            GHS {{ number_format($txn->balance_before, 2) }}
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
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $txn->created_at->format('M d, Y H:i') }}
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
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
