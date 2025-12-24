@extends('layouts.app')

@section('title', 'Request New Loan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Request New Loan</h1>
                <p class="text-gray-600 mt-1">Apply for a loan from available group funds</p>
            </div>
            <a href="{{ route('loan-requests.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Requests
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('loan-requests.store') }}" method="POST">
                @csrf

                <!-- Amount Requested -->
                <div class="mb-4">
                    <label for="amount_requested" class="block text-sm font-medium text-gray-700 mb-2">
                        Loan Amount Requested (GHS) *
                    </label>
                    <input type="number" id="amount_requested" name="amount_requested" value="{{ old('amount_requested') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('amount_requested') border-red-500 @enderror"
                           placeholder="0.00" step="0.01" min="1" max="{{ $groupFund->available_for_loans }}" required>
                    <p class="text-sm text-gray-500 mt-1">Maximum available: GHS {{ number_format($groupFund->available_for_loans, 2) }}</p>
                    @error('amount_requested')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expected Payback Date -->
                <div class="mb-4">
                    <label for="expected_payback_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Expected Payback Date *
                    </label>
                    <input type="date" id="expected_payback_date" name="expected_payback_date" value="{{ old('expected_payback_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('expected_payback_date') border-red-500 @enderror"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                    @error('expected_payback_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Interest Rate Preview -->
                @if($interestRates->count() > 0)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Available Interest Rates
                    </label>
                    <div class="bg-gray-50 p-4 rounded-md">
                        @foreach($interestRates as $rate)
                            <div class="flex justify-between items-center py-1">
                                <span class="text-sm">{{ $rate->name }}</span>
                                <span class="text-sm font-medium">{{ $rate->rate_percentage }}% per month</span>
                            </div>
                        @endforeach
                        <p class="text-xs text-gray-500 mt-2">* Final interest rate will be set by the administrator</p>
                    </div>
                </div>
                @endif

                <!-- Purpose -->
                <div class="mb-6">
                    <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
                        Purpose of Loan *
                    </label>
                    <textarea id="purpose" name="purpose" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('purpose') border-red-500 @enderror"
                              placeholder="Please describe why you need this loan and how you plan to use it..." required>{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Terms and Conditions -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-blue-800">Important Terms:</h4>
                            <ul class="mt-2 text-sm text-blue-700 list-disc list-inside space-y-1">
                                <li>Your loan request will be reviewed by the credit union administrator</li>
                                <li>Interest rates and final approval are at the discretion of the admin</li>
                                <li>You must repay the loan according to the agreed terms</li>
                                <li>Late payments may incur additional charges</li>
                                <li>All loans are subject to available group funds</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Agreement Checkbox -->
                <div class="mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" id="terms_agreed" name="terms_agreed" value="1" class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" required>
                        <span class="ml-2 text-sm text-gray-700">
                            I agree to the terms and conditions of the credit union loan agreement and understand that this is a formal loan request.
                        </span>
                    </label>
                    @error('terms_agreed')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('loan-requests.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Submit Loan Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

