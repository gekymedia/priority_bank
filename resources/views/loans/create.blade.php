@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">{{ Auth::user()->isAdmin() ? 'Create New Loan' : 'Record New Loan' }}</h1>

    @if(Auth::user()->isAdmin())
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <p class="text-blue-800 text-sm">
                <strong>Note:</strong> Creating a loan here will automatically approve and disburse it. The money is assumed to have been sent to the borrower.
            </p>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('loans.store') }}" method="POST">
            @csrf

            @if(Auth::user()->isAdmin())
                <div class="mb-4">
                    <label for="user_id" class="block text-gray-700 font-medium mb-2">Select Member *</label>
                    <select name="user_id" id="user_id" class="w-full px-4 py-2 border rounded-lg @error('user_id') border-red-500 @enderror" required>
                        <option value="">Select a member</option>
                        @foreach($users as $id => $name)
                            <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="mb-4">
                <label for="amount" class="block text-gray-700 font-medium mb-2">Loan Amount (GHS) *</label>
                <input type="number" step="0.01" min="1" name="amount" id="amount" class="w-full px-4 py-2 border rounded-lg @error('amount') border-red-500 @enderror" value="{{ old('amount') }}" required>
                @if(Auth::user()->isAdmin())
                    <p class="text-gray-500 text-sm mt-1">Available for loans: GHS {{ number_format($groupFund->available_for_loans, 2) }}</p>
                @endif
                @error('amount')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="interest_rate_id" class="block text-gray-700 font-medium mb-2">Interest Rate *</label>
                <select name="interest_rate_id" id="interest_rate_id" class="w-full px-4 py-2 border rounded-lg @error('interest_rate_id') border-red-500 @enderror" required>
                    <option value="">Select Interest Rate</option>
                    @foreach($interestRates as $rate)
                        <option value="{{ $rate->id }}" {{ old('interest_rate_id') == $rate->id ? 'selected' : '' }}>
                            {{ $rate->name }} ({{ $rate->rate_percentage }}%)
                        </option>
                    @endforeach
                </select>
                @error('interest_rate_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="date_given" class="block text-gray-700 font-medium mb-2">Disbursement Date *</label>
                <input type="date" name="date_given" id="date_given" class="w-full px-4 py-2 border rounded-lg @error('date_given') border-red-500 @enderror" value="{{ old('date_given') ?? now()->format('Y-m-d') }}" required>
                <p class="text-gray-500 text-sm mt-1">Date when the money is sent to the borrower</p>
                @error('date_given')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="expected_return_date" class="block text-gray-700 font-medium mb-2">Expected Return Date *</label>
                <input type="date" name="expected_return_date" id="expected_return_date" class="w-full px-4 py-2 border rounded-lg @error('expected_return_date') border-red-500 @enderror" value="{{ old('expected_return_date') }}" required>
                @error('expected_return_date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="notes" class="block text-gray-700 font-medium mb-2">Notes (Optional)</label>
                <textarea name="notes" id="notes" rows="3" class="w-full px-4 py-2 border rounded-lg">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('loans.index') }}" class="text-gray-600 hover:text-gray-800">Back to Loans</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    {{ Auth::user()->isAdmin() ? 'Create & Approve Loan' : 'Save Loan Record' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection