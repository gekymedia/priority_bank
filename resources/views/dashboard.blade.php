@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="container mx-auto px-4 py-8">
    @if(Auth::user()->isAdmin())
        <h1 class="text-3xl font-bold mb-8">Admin Financial Dashboard</h1>
    @else
        <h1 class="text-3xl font-bold mb-8">Credit Union Dashboard</h1>
    @endif
@if($errors->has('ai_error'))
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
        {{ $errors->first('ai_error') }}
    </div>
@endif
    @if(Auth::user()->isAdmin())
        <!-- Admin Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Income -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Total Income</p>
                        <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($totalIncome, 2) }}</h2>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Last 30 days</p>
            </div>

            <!-- Total Expenses -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Total Expenses</p>
                        <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($totalExpenses, 2) }}</h2>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Last 30 days</p>
            </div>

            <!-- Active Loans -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Active Loans</p>
                        <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($activeLoans, 2) }}</h2>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">{{ $loansCount }} outstanding</p>
            </div>

            <!-- Net Balance -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Net Balance</p>
                        <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($netBalance, 2) }}</h2>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Current available</p>
            </div>
        </div>
    @else
        <!-- User Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Savings Balance -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Savings Balance</p>
                        <h2 class="text-2xl font-bold mt-2 text-green-600">GHS {{ number_format($savingsBalance, 2) }}</h2>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Available for lending</p>
            </div>

            <!-- Loan Balance -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Loan Balance</p>
                        <h2 class="text-2xl font-bold mt-2 text-red-600">GHS {{ number_format($loanBalance, 2) }}</h2>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Outstanding loans</p>
            </div>

            <!-- Net Balance -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 {{ $netBalance >= 0 ? 'border-green-500' : 'border-red-500' }}">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Net Balance</p>
                        <h2 class="text-2xl font-bold mt-2 {{ $netBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            GHS {{ number_format(abs($netBalance), 2) }}
                        </h2>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">{{ $netBalance >= 0 ? 'Credit balance' : 'Debit balance' }}</p>
            </div>

            <!-- Group Fund Available -->
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Group Fund</p>
                        <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($groupFund->available_for_loans, 2) }}</h2>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Available for loans</p>
            </div>
        </div>
    @endif

    @if(Auth::user()->isAdmin())
        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Income vs Expenses Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Income vs Expenses (Last 30 Days)</h3>
                <canvas id="incomeExpenseChart" height="250"></canvas>
            </div>

            <!-- Expense Breakdown Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Expense Breakdown</h3>
                <canvas id="expenseCategoryChart" height="250"></canvas>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Recent Transactions</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($recentTransactions as $transaction)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            @if($transaction['type'] === 'income')
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            @elseif($transaction['type'] === 'expense')
                            <div class="bg-red-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            @endif
                            <div>
                                <p class="font-medium">{{ $transaction['description'] }}</p>
                                <p class="text-sm text-gray-500">{{ $transaction['date']->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium {{ $transaction['type'] === 'income' ? 'text-green-500' : 'text-red-500' }}">
                                {{ $transaction['type'] === 'income' ? '+' : '-' }}GHS {{ number_format($transaction['amount'], 2) }}
                            </p>
                            <p class="text-sm text-gray-500">{{ ucfirst($transaction['category']) }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="p-4 bg-gray-50 text-center">
                <a href="{{ route('transactions.index') }}" class="text-blue-500 hover:text-blue-700 font-medium">View All Transactions</a>
            </div>
        </div>
    @else
        <!-- User Credit Union Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Recent Savings -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Recent Savings</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($recentSavings as $saving)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="bg-green-100 p-3 rounded-full mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium">Savings Deposit</p>
                                    <p class="text-sm text-gray-500">{{ $saving->deposit_date->format('M d, Y') }}</p>
                                    <p class="text-sm text-gray-500">{{ ucfirst($saving->status) }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-green-500">+GHS {{ number_format($saving->amount, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-gray-500">
                        No savings yet
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Active Loan Requests -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Loan Requests</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($activeLoanRequests as $request)
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full mr-4 {{ $request->status === 'approved' ? 'bg-green-100' : 'bg-yellow-100' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $request->status === 'approved' ? 'text-green-500' : 'text-yellow-500' }}" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium">Loan Request</p>
                                    <p class="text-sm text-gray-500">{{ $request->request_date->format('M d, Y') }}</p>
                                    <p class="text-sm {{ $request->status === 'approved' ? 'text-green-600' : 'text-yellow-600' }}">{{ ucfirst($request->status) }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">GHS {{ number_format($request->amount_requested, 2) }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-gray-500">
                        No active loan requests
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Recent Payments & Loans</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @php
                    $combinedActivities = collect();
                    foreach($recentLoans as $loan) {
                        $combinedActivities->push([
                            'type' => 'loan',
                            'item' => $loan,
                            'date' => $loan->disbursement_date ?? $loan->date_given
                        ]);
                    }
                    foreach($recentPayments as $payment) {
                        $combinedActivities->push([
                            'type' => 'payment',
                            'item' => $payment,
                            'date' => $payment->payment_date
                        ]);
                    }
                    $combinedActivities = $combinedActivities->sortByDesc('date')->take(10);
                @endphp

                @forelse($combinedActivities as $activity)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            @if($activity['type'] === 'loan')
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Loan Disbursed</p>
                                <p class="text-sm text-gray-500">{{ $activity['date']->format('M d, Y') }}</p>
                            </div>
                            @else
                            <div class="bg-green-100 p-3 rounded-full mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium">Payment Made</p>
                                <p class="text-sm text-gray-500">{{ $activity['date']->format('M d, Y') }}</p>
                            </div>
                            @endif
                        </div>
                        <div class="text-right">
                            @if($activity['type'] === 'loan')
                            <p class="font-medium text-blue-500">-GHS {{ number_format($activity['item']->amount, 2) }}</p>
                            @else
                            <p class="font-medium text-green-500">-GHS {{ number_format($activity['item']->amount, 2) }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-gray-500">
                    No recent activity
                </div>
                @endforelse
            </div>
        </div>
    @endif

    @if(Auth::user()->isAdmin())
        <!-- AI Insights Section -->
        @if($aiInsights)
        <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 bg-blue-50">
                <h3 class="text-lg font-semibold flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                    </svg>
                    AI Financial Insights
                </h3>
            </div>
            <div class="p-6">
                <div class="prose max-w-none">
                    {!! $aiInsights !!}
                </div>
            </div>
        </div>
        @endif
    @else
        <!-- User Actions -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="#" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="text-center">
                    <div class="bg-green-100 p-4 rounded-full w-16 h-16 mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Add Savings</h3>
                    <p class="text-gray-600 text-sm">Make money available for the group</p>
                </div>
            </a>

            <a href="#" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="text-center">
                    <div class="bg-blue-100 p-4 rounded-full w-16 h-16 mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Request Loan</h3>
                    <p class="text-gray-600 text-sm">Apply for a loan from group funds</p>
                </div>
            </a>

            <a href="#" class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="text-center">
                    <div class="bg-purple-100 p-4 rounded-full w-16 h-16 mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Make Payment</h3>
                    <p class="text-gray-600 text-sm">Pay back your outstanding loans</p>
                </div>
            </a>
        </div>
    @endif
</div>

@if(Auth::user()->isAdmin())
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Income vs Expenses Chart
    const incomeExpenseCtx = document.getElementById('incomeExpenseChart').getContext('2d');
    new Chart(incomeExpenseCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($incomeExpenseChart['labels']) !!},
            datasets: [
                {
                    label: 'Income',
                    data: {!! json_encode($incomeExpenseChart['income']) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Expenses',
                    data: {!! json_encode($incomeExpenseChart['expenses']) !!},
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
                    beginAtZero: true
                }
            }
        }
    });

    // Expense Category Chart
    const expenseCategoryCtx = document.getElementById('expenseCategoryChart').getContext('2d');
    new Chart(expenseCategoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($expenseCategoryChart['labels']) !!},
            datasets: [{
                data: {!! json_encode($expenseCategoryChart['data']) !!},
                backgroundColor: [
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(249, 115, 22, 0.7)',
                    'rgba(234, 179, 8, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(139, 92, 246, 0.7)'
                ],
                borderColor: [
                    'rgba(239, 68, 68, 1)',
                    'rgba(249, 115, 22, 1)',
                    'rgba(234, 179, 8, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(139, 92, 246, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });
</script>
@endpush
@endif
@endsection