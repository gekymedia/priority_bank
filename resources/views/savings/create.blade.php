@extends('layouts.app')

@section('title', 'Add New Savings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Add New Savings</h1>
                <p class="text-gray-600 mt-1">Deposit money to make it available for lending to group members</p>
            </div>
            <a href="{{ route('savings.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Savings
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('savings.store') }}" method="POST">
                @csrf

                <!-- Amount -->
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Deposit Amount (GHS) *
                    </label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 @error('amount') border-red-500 @enderror"
                           placeholder="0.00" step="0.01" min="1" required>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deposit Date -->
                <div class="mb-4">
                    <label for="deposit_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Deposit Date *
                    </label>
                    <input type="date" id="deposit_date" name="deposit_date" value="{{ old('deposit_date', date('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 @error('deposit_date') border-red-500 @enderror"
                           max="{{ date('Y-m-d') }}" required>
                    @error('deposit_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea id="notes" name="notes" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 @error('notes') border-red-500 @enderror"
                              placeholder="Any additional notes about this deposit...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Information Box -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Important:</strong> By depositing this amount, you agree to make these funds available for lending to other group members who need loans. The money will be managed by the savings group administrator.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('savings.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Add Savings Deposit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

