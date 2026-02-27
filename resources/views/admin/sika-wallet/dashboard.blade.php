@extends('layouts.app')
@section('title', 'Sika Wallet Dashboard')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold">Sika Wallet Dashboard</h1>
            <p class="text-gray-500 mt-1">GekyChat Integration - Priority Bank Wallet API</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.sika-wallet.transactions') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center">
                <i class="fas fa-list mr-2"></i>
                All Transactions
            </a>
            <a href="{{ route('admin.sika-wallet.wallets') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center">
                <i class="fas fa-wallet mr-2"></i>
                All Wallets
            </a>
        </div>
    </div>

    <!-- Main Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Wallets -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Total Wallets</p>
                    <h2 class="text-2xl font-bold mt-2">{{ number_format($totalWallets) }}</h2>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">{{ number_format($activeWallets) }} active</p>
        </div>

        <!-- Total Balance -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Total Balance</p>
                    <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($totalBalance, 2) }}</h2>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">Across all wallets</p>
        </div>

        <!-- Total Transactions -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Total Transactions</p>
                    <h2 class="text-2xl font-bold mt-2">{{ number_format($totalTransactions) }}</h2>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">{{ number_format($completedTransactions) }} completed</p>
        </div>

        <!-- Pending/Failed -->
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Pending / Failed</p>
                    <h2 class="text-2xl font-bold mt-2">{{ number_format($pendingTransactions) }} / {{ number_format($failedTransactions) }}</h2>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">Requires attention</p>
        </div>
    </div>

    <!-- GekyChat Integration Stats -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-6 mb-8 text-white">
        <h3 class="text-lg font-semibold mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            GekyChat Sika Coins Integration (Last 30 Days)
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Sika Coin Purchases (Debits from PBG) -->
            <div class="bg-white/10 rounded-lg p-4">
                <p class="text-white/80 text-sm">Sika Coin Purchases</p>
                <p class="text-2xl font-bold mt-1">GHS {{ number_format($sikaCoinPurchases, 2) }}</p>
                <p class="text-white/60 text-sm mt-1">{{ number_format($sikaCoinPurchaseCount) }} transactions</p>
                <p class="text-xs text-white/50 mt-2">Money → GekyChat</p>
            </div>
            
            <!-- Sika Coin Cashouts (Credits to PBG) -->
            <div class="bg-white/10 rounded-lg p-4">
                <p class="text-white/80 text-sm">Sika Coin Cashouts</p>
                <p class="text-2xl font-bold mt-1">GHS {{ number_format($sikaCoinCashouts, 2) }}</p>
                <p class="text-white/60 text-sm mt-1">{{ number_format($sikaCoinCashoutCount) }} transactions</p>
                <p class="text-xs text-white/50 mt-2">Money ← GekyChat</p>
            </div>
            
            <!-- Deposits -->
            <div class="bg-white/10 rounded-lg p-4">
                <p class="text-white/80 text-sm">Wallet Deposits</p>
                <p class="text-2xl font-bold mt-1">GHS {{ number_format($deposits, 2) }}</p>
                <p class="text-white/60 text-sm mt-1">{{ number_format($depositCount) }} deposits</p>
                <p class="text-xs text-white/50 mt-2">User funding</p>
            </div>
            
            <!-- Net Flow -->
            <div class="bg-white/10 rounded-lg p-4">
                <p class="text-white/80 text-sm">Net Flow (30 days)</p>
                @php $netFlow = $totalCredits - $totalDebits; @endphp
                <p class="text-2xl font-bold mt-1 {{ $netFlow >= 0 ? 'text-green-300' : 'text-red-300' }}">
                    {{ $netFlow >= 0 ? '+' : '' }}GHS {{ number_format($netFlow, 2) }}
                </p>
                <p class="text-white/60 text-sm mt-1">Credits - Debits</p>
                <p class="text-xs text-white/50 mt-2">{{ $netFlow >= 0 ? 'Inflow' : 'Outflow' }}</p>
            </div>
        </div>
    </div>

    <!-- Charts and Transaction Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Daily Volume Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Daily Transaction Volume (Last 14 Days)</h3>
            <canvas id="dailyVolumeChart" height="250"></canvas>
        </div>

        <!-- Transaction Type Breakdown -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Transaction Type Breakdown (Last 30 Days)</h3>
            @if($transactionsByType->count() > 0)
            <div class="space-y-3">
                @foreach($transactionsByType as $type)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <span class="w-3 h-3 rounded-full mr-3 
                            @if($type->type === 'SIKA_COIN_PURCHASE') bg-red-500
                            @elseif($type->type === 'SIKA_COIN_CASHOUT') bg-green-500
                            @elseif($type->type === 'DEPOSIT') bg-blue-500
                            @elseif($type->type === 'WITHDRAWAL') bg-orange-500
                            @else bg-gray-500
                            @endif
                        "></span>
                        <span class="font-medium">{{ str_replace('_', ' ', $type->type) }}</span>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold">GHS {{ number_format($type->total, 2) }}</p>
                        <p class="text-sm text-gray-500">{{ number_format($type->count) }} txns</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-center py-8">No transactions in the last 30 days</p>
            @endif
        </div>
    </div>

    <!-- Top Wallets and Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Top Wallets by Balance -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Top Wallets by Balance</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($topWallets as $wallet)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium">{{ $wallet->user->name ?? 'User #' . $wallet->user_id }}</p>
                            <p class="text-sm text-gray-500">{{ $wallet->user->email ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-green-600">GHS {{ number_format($wallet->balance, 2) }}</p>
                            <span class="text-xs px-2 py-1 rounded-full {{ $wallet->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($wallet->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-gray-500">No wallets yet</div>
                @endforelse
            </div>
            <div class="p-4 bg-gray-50 text-center">
                <a href="{{ route('admin.sika-wallet.wallets') }}" class="text-blue-500 hover:text-blue-700 font-medium">View All Wallets</a>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Recent Transactions</h3>
            </div>
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                @forelse($recentTransactions as $txn)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-medium text-sm">{{ str_replace('_', ' ', $txn->type) }}</p>
                            <p class="text-xs text-gray-500">{{ $txn->user->name ?? 'User #' . $txn->user_id }}</p>
                            <p class="text-xs text-gray-400">{{ $txn->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold {{ $txn->direction === 'CREDIT' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $txn->direction === 'CREDIT' ? '+' : '-' }}GHS {{ number_format($txn->amount, 2) }}
                            </p>
                            <span class="text-xs px-2 py-1 rounded-full 
                                @if($txn->status === 'COMPLETED') bg-green-100 text-green-800
                                @elseif($txn->status === 'PENDING') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800
                                @endif
                            ">
                                {{ $txn->status }}
                            </span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-gray-500">No transactions yet</div>
                @endforelse
            </div>
            <div class="p-4 bg-gray-50 text-center">
                <a href="{{ route('admin.sika-wallet.transactions') }}" class="text-blue-500 hover:text-blue-700 font-medium">View All Transactions</a>
            </div>
        </div>
    </div>

    <!-- API Health Status -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            API Endpoints Status
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="flex items-center p-3 bg-green-50 rounded-lg">
                <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
                <div>
                    <p class="font-medium text-sm">GET /api/health</p>
                    <p class="text-xs text-gray-500">Health Check</p>
                </div>
            </div>
            <div class="flex items-center p-3 bg-green-50 rounded-lg">
                <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
                <div>
                    <p class="font-medium text-sm">GET /api/wallets/user/{id}/balance</p>
                    <p class="text-xs text-gray-500">Get Balance</p>
                </div>
            </div>
            <div class="flex items-center p-3 bg-green-50 rounded-lg">
                <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
                <div>
                    <p class="font-medium text-sm">POST /api/wallets/debit</p>
                    <p class="text-xs text-gray-500">Debit Wallet (Sika Purchase)</p>
                </div>
            </div>
            <div class="flex items-center p-3 bg-green-50 rounded-lg">
                <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
                <div>
                    <p class="font-medium text-sm">POST /api/wallets/credit</p>
                    <p class="text-xs text-gray-500">Credit Wallet (Sika Cashout)</p>
                </div>
            </div>
            <div class="flex items-center p-3 bg-green-50 rounded-lg">
                <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
                <div>
                    <p class="font-medium text-sm">POST /api/wallets/deposit</p>
                    <p class="text-xs text-gray-500">Deposit Funds</p>
                </div>
            </div>
            <div class="flex items-center p-3 bg-green-50 rounded-lg">
                <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
                <div>
                    <p class="font-medium text-sm">GET /api/transactions/{id}</p>
                    <p class="text-xs text-gray-500">Verify Transaction</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dailyVolumeCtx = document.getElementById('dailyVolumeChart').getContext('2d');
    new Chart(dailyVolumeCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [
                {
                    label: 'Credits (In)',
                    data: {!! json_encode($chartCredits) !!},
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Debits (Out)',
                    data: {!! json_encode($chartDebits) !!},
                    backgroundColor: 'rgba(239, 68, 68, 0.7)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'GHS ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': GHS ' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
