@extends('layouts.app')

@section('title', 'Create Interest Rate')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Create Interest Rate</h1>
                <p class="text-gray-600 mt-1">Add a new interest rate for loans or savings</p>
            </div>
            <a href="{{ route('interest-rates.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Rates
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('interest-rates.store') }}" method="POST">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Rate Name *
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                           placeholder="e.g., Standard Loan Rate, Premium Savings Rate" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rate Percentage -->
                <div class="mb-4">
                    <label for="rate_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                        Interest Rate (%) *
                    </label>
                    <input type="number" id="rate_percentage" name="rate_percentage" value="{{ old('rate_percentage') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('rate_percentage') border-red-500 @enderror"
                           placeholder="5.00" step="0.01" min="0" max="100" required>
                    @error('rate_percentage')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div class="mb-4">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Rate Type *
                    </label>
                    <select id="type" name="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror" required>
                        <option value="loan_interest" {{ old('type') === 'loan_interest' ? 'selected' : '' }}>Loan Interest Rate</option>
                        <option value="savings_interest" {{ old('type') === 'savings_interest' ? 'selected' : '' }}>Savings Interest Rate</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Effective From -->
                <div class="mb-4">
                    <label for="effective_from" class="block text-sm font-medium text-gray-700 mb-2">
                        Effective From *
                    </label>
                    <input type="date" id="effective_from" name="effective_from" value="{{ old('effective_from', date('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('effective_from') border-red-500 @enderror" required>
                    @error('effective_from')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Effective To -->
                <div class="mb-4">
                    <label for="effective_to" class="block text-sm font-medium text-gray-700 mb-2">
                        Effective To (Optional)
                    </label>
                    <input type="date" id="effective_to" name="effective_to" value="{{ old('effective_to') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('effective_to') border-red-500 @enderror">
                    <p class="text-sm text-gray-500 mt-1">Leave empty for ongoing rate</p>
                    @error('effective_to')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">Active (Rate will be available for use)</span>
                    </label>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description (Optional)
                    </label>
                    <textarea id="description" name="description" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                              placeholder="Describe the purpose and conditions of this interest rate...">{{ old('description') }}</textarea>
                    @error('description')
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
                                <strong>Important:</strong> Interest rates can be changed but existing loans will continue using their original rates. New loans will use the active rates available at the time of approval.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('interest-rates.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Interest Rate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
