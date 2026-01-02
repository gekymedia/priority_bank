@extends('layouts.app')

@section('title', 'Interest Rates Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Interest Rates Management</h1>
            <p class="text-gray-600 mt-1">Manage interest rates for loans and savings</p>
        </div>
        <a href="{{ route('interest-rates.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
            Add New Rate
        </a>
    </div>

    <!-- Rate Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Active Loan Rates</p>
                    <h2 class="text-2xl font-bold mt-2 text-green-600">{{ \App\Models\InterestRate::active()->forLoans()->count() }}</h2>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">Currently active</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Active Savings Rates</p>
                    <h2 class="text-2xl font-bold mt-2 text-blue-600">{{ \App\Models\InterestRate::active()->forSavings()->count() }}</h2>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">For deposits</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Inactive Rates</p>
                    <h2 class="text-2xl font-bold mt-2 text-yellow-600">{{ \App\Models\InterestRate::where('is_active', false)->count() }}</h2>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">Disabled rates</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 font-medium">Total Rates</p>
                    <h2 class="text-2xl font-bold mt-2 text-purple-600">{{ \App\Models\InterestRate::count() }}</h2>
                </div>
                <div class="bg-purple-100 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-2">All rates</p>
        </div>
    </div>

    <!-- Interest Rates Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Effective Period</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($interestRates as $rate)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $rate->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                            {{ $rate->rate_percentage }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($rate->type === 'loan_interest') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $rate->type === 'loan_interest' ? 'Loan' : 'Savings' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($rate->is_active && $rate->isCurrentlyActive()) bg-green-100 text-green-800
                                @elseif($rate->is_active) bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @if($rate->is_active && $rate->isCurrentlyActive())
                                    Active
                                @elseif($rate->is_active)
                                    Scheduled
                                @else
                                    Inactive
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div>{{ $rate->effective_from->format('M d, Y') }}</div>
                            <div class="text-xs">
                                @if($rate->effective_to)
                                    to {{ $rate->effective_to->format('M d, Y') }}
                                @else
                                    Ongoing
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('interest-rates.show', $rate->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            <a href="{{ route('interest-rates.edit', $rate->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            @if($rate->loans()->count() === 0)
                                <form action="{{ route('interest-rates.destroy', $rate->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this interest rate?')">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No interest rates found.
                            <br><a href="{{ route('interest-rates.create') }}" class="text-blue-500 hover:text-blue-700">Create the first interest rate</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if($interestRates->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $interestRates->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
