@extends('layouts.app')
@section('title', 'Statement – ' . $user->name)
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>Back to User
        </a>
        <h1 class="text-3xl font-bold">Statement: {{ $user->name }}</h1>
        <p class="text-gray-600 mt-1">{{ $user->email }} · {{ $user->phone ?? 'N/A' }}</p>
        <p class="text-sm text-gray-500 mt-1">Savings balance: GHS {{ number_format($user->savings_balance, 2) }} · Loan balance: GHS {{ number_format($user->loan_balance, 2) }} · Net: GHS {{ number_format($user->net_balance, 2) }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.users.statement', $user) }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">From</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate?->format('Y-m-d') }}"
                    class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">To</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate?->format('Y-m-d') }}"
                    class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Filter</button>
            @if($startDate || $endDate)
                <a href="{{ route('admin.users.statement', $user) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category / Source</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($entries as $entry)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $entry->date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $entry->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $entry->type === 'income' ? 'Credit' : 'Debit' }}
                            </span>
                            @if(isset($entry->status) && $entry->status !== 'successful')
                                <span class="ml-1 text-xs text-amber-600">{{ $entry->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $entry->description ?: '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entry->category }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium {{ $entry->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $entry->type === 'income' ? '+' : '-' }}GHS {{ number_format($entry->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No statement entries in this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
