@extends('layouts.app')

@section('title', 'Make Loan Payment')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Make Loan Payment</h1>
                <p class="text-gray-600 mt-1">Repay your outstanding loan balance</p>
            </div>
            <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Dashboard
            </a>
        </div>

        <!-- Payment Methods Info -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-blue-800">Available Payment Methods:</h4>
                    <ul class="mt-2 text-sm text-blue-700 list-disc list-inside space-y-1">
                        <li><strong>Paystack:</strong> Card, mobile money, bank transfer</li>
                        <li><strong>Hubtel:</strong> Hubtel wallet and card payments</li>
                        <li><strong>Manual:</strong> Cash or bank transfer (requires admin approval)</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf

                <!-- Loan Selection -->
                <div class="mb-4">
                    <label for="loan_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Loan to Pay *
                    </label>
                    <select id="loan_id" name="loan_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('loan_id') border-red-500 @enderror" required>
                        <option value="">Choose a loan...</option>
                        @foreach($loans as $loan)
                        <option value="{{ $loan->id }}"
                                {{ $selectedLoan && $selectedLoan->id === $loan->id ? 'selected' : '' }}>
                            Loan #{{ $loan->id }} - GHS {{ number_format($loan->remaining_balance, 2) }} remaining
                            ({{ $loan->disbursement_date ? $loan->disbursement_date->format('M d, Y') : 'Pending' }})
                        </option>
                        @endforeach
                    </select>
                    @error('loan_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Amount -->
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Amount (GHS) *
                    </label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('amount') border-red-500 @enderror"
                           placeholder="0.00" step="0.01" min="1" required>
                    <p class="text-sm text-gray-500 mt-1">Enter the amount you want to pay</p>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Method -->
                <div class="mb-4">
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                        Payment Method *
                    </label>
                    <div class="space-y-3">
                        @php
                            $paymentMethods = app(\App\Http\Controllers\PaymentsController::class)->getSupportedMethods();
                        @endphp

                        @foreach($paymentMethods as $key => $method)
                            @if($method['enabled'])
                            <label class="flex items-center">
                                <input type="radio" id="payment_{{ $key }}" name="payment_method" value="{{ $key }}"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                       {{ old('payment_method') === $key ? 'checked' : (!$loop->first ? '' : 'checked') }}>
                                <span class="ml-3">
                                    <span class="block text-sm font-medium text-gray-700">{{ $method['name'] }}</span>
                                    <span class="block text-sm text-gray-500">{{ $method['description'] }}</span>
                                </span>
                            </label>
                            @endif
                        @endforeach
                    </div>
                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-500 @enderror"
                              placeholder="Any additional notes about this payment...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Terms -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Important:</strong> Payments through Paystack and Hubtel are processed immediately.
                                Manual payments require admin approval before being applied to your loan balance.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Proceed to Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update loan selection info when loan changes
document.getElementById('loan_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        // Could add dynamic amount suggestions based on selected loan
        console.log('Selected loan:', selectedOption.value);
    }
});
</script>
@endsection
