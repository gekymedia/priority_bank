@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    @if(Auth::user()->isAdmin() && isset($transactions))
        <!-- Priority Bank Transactions View -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold">Priority Bank</h1>
            <p class="text-gray-600 mt-1">Income and Expenditure from Priority Bank Source</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Total Income</p>
                        <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($totalIncome ?? 0, 2) }}</h2>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-arrow-down text-green-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Total Expenses</p>
                        <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($totalExpense ?? 0, 2) }}</h2>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-arrow-up text-red-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Net Balance</p>
                        <h2 class="text-2xl font-bold mt-2">GHS {{ number_format(($totalIncome ?? 0) - ($totalExpense ?? 0), 2) }}</h2>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-balance-scale text-blue-500 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maximum Available for Loans Card -->
        @php
            $groupFund = \App\Models\GroupFund::getInstance();
            $maxAvailable = $groupFund->available_for_loans ?? 0;
        @endphp
        <div class="mb-6">
            <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg shadow-md p-4 text-white">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium opacity-90">Maximum Available for Loans</p>
                        <h3 class="text-2xl font-bold mt-1">GHS {{ number_format($maxAvailable, 2) }}</h3>
                        <p class="text-xs opacity-75 mt-1">Users can request any amount, but approval depends on available funds</p>
                    </div>
                    <div class="bg-white bg-opacity-20 p-3 rounded-full">
                        <i class="fas fa-coins text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category/Directorate</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transaction->date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ \App\Models\Transaction::textForDisplay($transaction->description) ?: '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $transaction->category }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'income' ? '+' : '-' }}GHS {{ number_format($transaction->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                No transactions found for Priority Bank source.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $transactions->links() }}
            </div>
            @endif
        </div>
    @else
        <!-- Regular Loans View -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ Auth::user()->isAdmin() ? 'All Group Loans' : 'My Loans' }}</h1>
            <p class="text-gray-600 mt-1">{{ Auth::user()->isAdmin() ? 'Manage all group loans' : 'View your loan history' }}</p>
        </div>
        @if(Auth::user()->isAdmin())
            <a href="{{ route('loans.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Create New Loan
            </a>
        @else
            <a href="{{ route('loan-requests.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Request New Loan
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @if(Auth::user()->isAdmin())
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                    @endif
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total with Interest</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disbursed On</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($loans as $loan)
                <tr>
                    @if(Auth::user()->isAdmin())
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $loan->user->name ?? $loan->borrower_name }}</div>
                            <div class="text-sm text-gray-500">{{ $loan->user->email ?? '' }}</div>
                        </td>
                    @endif
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        GHS {{ number_format($loan->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        GHS {{ number_format($loan->total_amount_with_interest ?? $loan->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ ($loan->remaining_balance ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                        GHS {{ number_format($loan->remaining_balance ?? 0, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusClasses = [
                                'borrowed' => 'bg-yellow-100 text-yellow-800',
                                'returned' => 'bg-green-100 text-green-800',
                                'lost' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusClasses[$loan->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($loan->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ optional($loan->disbursement_date ?? $loan->date_given)->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ optional($loan->expected_return_date)->format('M d, Y') ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap space-x-2">
                        <a href="{{ route('loans.edit', $loan->id) }}" class="text-blue-500 hover:text-blue-700">Edit</a>
                        <form action="{{ route('loans.destroy', $loan->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                        @if($loan->status === 'borrowed')
                            <!-- Mark returned form -->
                            <form action="{{ route('loans.return', $loan->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="returned_amount" value="{{ $loan->amount }}">
                                <input type="hidden" name="date" value="{{ now()->format('Y-m-d') }}">
                                <input type="hidden" name="account_id" value="{{ $loan->account_id }}">
                                <input type="hidden" name="channel" value="{{ $loan->channel }}">
                                <button type="submit" class="text-green-500 hover:text-green-700" onclick="return confirm('Mark this loan as returned?')">Mark Returned</button>
                            </form>
                            <!-- Mark lost form -->
                            <form action="{{ route('loans.lost', $loan->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="date" value="{{ now()->format('Y-m-d') }}">
                                <input type="hidden" name="account_id" value="{{ $loan->account_id }}">
                                <input type="hidden" name="channel" value="{{ $loan->channel }}">
                                <button type="submit" class="text-yellow-700 hover:text-yellow-800" onclick="return confirm('Mark this loan as lost?')">Mark Lost</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $loans->links() }}
    </div>
    @endif
</div>
@endsection