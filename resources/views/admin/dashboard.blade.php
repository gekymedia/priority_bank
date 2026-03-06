@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Admin Financial Dashboard</h1>
        <div class="flex gap-3">
            @if($pendingUsersCount > 0)
                <a href="{{ route('admin.users.index', ['status' => 'pending']) }}" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 flex items-center">
                    <i class="fas fa-user-clock mr-2"></i>
                    Pending Users ({{ $pendingUsersCount }})
                </a>
            @endif
            <a href="{{ route('admin.users.index') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center">
                <i class="fas fa-users mr-2"></i>
                Manage Users
            </a>
        </div>
    </div>

    @if($errors->has('ai_error'))
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
            {{ $errors->first('ai_error') }}
        </div>
    @endif

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

    <!-- Credit Union Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Group Fund</p>
                    <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($groupFund->available_for_loans, 2) }}</h2>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">Available for loans</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Pending Loan Requests</p>
                    <h2 class="text-2xl font-bold mt-2">{{ $pendingLoanRequests }}</h2>
                </div>
                <div class="bg-orange-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">Awaiting approval</p>
            @if($pendingLoanRequests > 0)
                <a href="{{ route('loan-requests.index', ['status' => 'pending']) }}" class="text-orange-600 hover:text-orange-800 text-sm font-medium mt-2 inline-block">
                    View Requests →
                </a>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-indigo-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Total Credit Union Loans</p>
                    <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($totalCreditUnionLoans, 2) }}</h2>
                </div>
                <div class="bg-indigo-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">Active group loans</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-teal-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Pending Savings (Pre-approval)</p>
                    <h2 class="text-2xl font-bold mt-2">{{ $pendingSavingsCount }}</h2>
                </div>
                <div class="bg-teal-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">Direct deposits awaiting approval</p>
            @if($pendingSavingsCount > 0)
                <a href="{{ route('savings.index', ['approval' => 'pending']) }}" class="text-teal-600 hover:text-teal-800 text-sm font-medium mt-2 inline-block">
                    Review & Approve →
                </a>
            @endif
        </div>
    </div>

    <!-- Pending Savings (Pre-approval) -->
    @if(isset($pendingSavingsList) && $pendingSavingsList->count() > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-200 bg-teal-50">
            <h3 class="text-lg font-semibold flex items-center">
                <i class="fas fa-piggy-bank text-teal-500 mr-2"></i>
                Pending Savings – Pre-approval ({{ $pendingSavingsList->count() }})
            </h3>
            <p class="text-sm text-gray-600 mt-1">Direct deposits (Bank/MoMo) awaiting your approval</p>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($pendingSavingsList as $saving)
            <div class="p-4 hover:bg-gray-50">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="font-medium text-gray-900">{{ $saving->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $saving->user->email }}</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-teal-100 text-teal-800">
                                Pending
                            </span>
                        </div>
                        <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Amount:</span>
                                <span class="font-semibold text-gray-900 ml-1">GHS {{ number_format($saving->amount, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Deposit date:</span>
                                <span class="font-semibold text-gray-900 ml-1">{{ $saving->deposit_date->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Reference:</span>
                                <span class="font-semibold text-gray-900 ml-1">{{ $saving->reference ?? '–' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Method:</span>
                                <span class="font-semibold text-gray-900 ml-1">{{ ucfirst($saving->payment_method) }}</span>
                            </div>
                        </div>
                        @if($saving->notes)
                        <div class="mt-2">
                            <span class="text-gray-500 text-sm">Notes:</span>
                            <p class="text-sm text-gray-700 mt-1">{{ $saving->notes }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="ml-4 flex flex-col gap-2">
                        <a href="{{ route('savings.show', $saving) }}" class="text-teal-600 hover:text-teal-900 text-sm font-medium">
                            <i class="fas fa-eye mr-1"></i> Review & Approve
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($pendingSavingsCount > $pendingSavingsList->count())
        <div class="p-4 bg-gray-50 text-center">
            <a href="{{ route('savings.index', ['approval' => 'pending']) }}" class="text-teal-600 hover:text-teal-800 font-medium">
                View All {{ $pendingSavingsCount }} Pending Savings →
            </a>
        </div>
        @endif
    </div>
    @endif

    <!-- Pending Loan Requests Notifications -->
    @if(isset($pendingLoanRequestsList) && $pendingLoanRequestsList->count() > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-200 bg-orange-50">
            <h3 class="text-lg font-semibold flex items-center">
                <i class="fas fa-bell text-orange-500 mr-2"></i>
                Pending Loan Requests ({{ $pendingLoanRequestsList->count() }})
            </h3>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($pendingLoanRequestsList as $loanRequest)
            <div class="p-4 hover:bg-gray-50">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="font-medium text-gray-900">{{ $loanRequest->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $loanRequest->user->email }}</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                Pending
                            </span>
                        </div>
                        <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Amount:</span>
                                <span class="font-semibold text-gray-900 ml-1">GHS {{ number_format($loanRequest->amount_requested, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Payback Date:</span>
                                <span class="font-semibold text-gray-900 ml-1">{{ $loanRequest->expected_payback_date->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Requested:</span>
                                <span class="font-semibold text-gray-900 ml-1">{{ $loanRequest->request_date->format('M d, Y') }}</span>
                            </div>
                        </div>
                        @if($loanRequest->purpose)
                        <div class="mt-2">
                            <span class="text-gray-500 text-sm">Purpose:</span>
                            <p class="text-sm text-gray-700 mt-1">{{ $loanRequest->purpose }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="ml-4 flex flex-col gap-2">
                        <a href="{{ route('loan-requests.show', $loanRequest) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            <i class="fas fa-eye mr-1"></i> Review & Approve
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($pendingLoanRequests > $pendingLoanRequestsList->count())
        <div class="p-4 bg-gray-50 text-center">
            <a href="{{ route('loan-requests.index', ['status' => 'pending']) }}" class="text-blue-500 hover:text-blue-700 font-medium">
                View All {{ $pendingLoanRequests }} Pending Requests →
            </a>
        </div>
        @endif
    </div>
    @endif

    <!-- Pending Deposits Notifications -->
    @if(isset($pendingDepositsList) && $pendingDepositsList->count() > 0)
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-200 bg-blue-50">
            <h3 class="text-lg font-semibold flex items-center">
                <i class="fas fa-money-bill-wave text-blue-500 mr-2"></i>
                Pending Deposit Requests ({{ $pendingDepositsList->count() }})
            </h3>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($pendingDepositsList as $deposit)
            <div class="p-4 hover:bg-gray-50">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="font-medium text-gray-900">{{ $deposit->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $deposit->user->email }}</p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                Pending
                            </span>
                        </div>
                        <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Amount:</span>
                                <span class="font-semibold text-gray-900 ml-1">GHS {{ number_format($deposit->amount, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Date:</span>
                                <span class="font-semibold text-gray-900 ml-1">{{ $deposit->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        @if($deposit->description)
                        <div class="mt-2">
                            <span class="text-gray-500 text-sm">Description:</span>
                            <p class="text-sm text-gray-700 mt-1">{{ $deposit->description }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="ml-4 flex flex-col gap-2">
                        <a href="{{ route('deposits.show', $deposit) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            <i class="fas fa-eye mr-1"></i> Review & Approve
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($pendingDepositsCount > $pendingDepositsList->count())
        <div class="p-4 bg-gray-50 text-center">
            <a href="{{ route('deposits.index') }}" class="text-blue-500 hover:text-blue-700 font-medium">
                View All {{ $pendingDepositsCount }} Pending Deposits →
            </a>
        </div>
        @endif
    </div>
    @endif

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
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
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

    <!-- AI Insights Section -->
    @if($aiInsights)
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
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
</div>

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
@endsection
