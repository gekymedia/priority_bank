@extends('layouts.app')

@section('title', 'Edit Interest Rate')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Edit Interest Rate</h1>
                <p class="text-gray-600 mt-1">Update interest rate settings</p>
            </div>
            <a href="{{ route('interest-rates.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Rates
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('interest-rates.update', $interestRate->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Rate Name *
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $interestRate->name) }}"
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
                    <input type="number" id="rate_percentage" name="rate_percentage" value="{{ old('rate_percentage', $interestRate->rate_percentage) }}"
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
                        <option value="loan_interest" {{ old('type', $interestRate->type) === 'loan_interest' ? 'selected' : '' }}>Loan Interest Rate</option>
                        <option value="savings_interest" {{ old('type', $interestRate->type) === 'savings_interest' ? 'selected' : '' }}>Savings Interest Rate</option>
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
                    <input type="date" id="effective_from" name="effective_from" value="{{ old('effective_from', $interestRate->effective_from->format('Y-m-d')) }}"
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
                    <input type="date" id="effective_to" name="effective_to" value="{{ old('effective_to', $interestRate->effective_to?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('effective_to') border-red-500 @enderror">
                    <p class="text-sm text-gray-500 mt-1">Leave empty for ongoing rate</p>
                    @error('effective_to')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $interestRate->is_active) ? 'checked' : '' }}
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
                              placeholder="Describe the purpose and conditions of this interest rate...">{{ old('description', $interestRate->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Usage Information -->
                @if($interestRate->loans()->count() > 0)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Note:</strong> This rate is currently being used by <strong>{{ $interestRate->loans()->count() }}</strong> loan(s).
                                Changes will only affect new loans. Existing loans will continue using the original rate.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('interest-rates.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Update Interest Rate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
