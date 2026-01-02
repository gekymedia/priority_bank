@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Payment Details</h1>
                <p class="text-gray-600 mt-1">Payment #{{ $payment->id }}</p>
            </div>
            <a href="{{ route('payments.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Payments
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Payment Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Payment Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Payment Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Amount</label>
                            <p class="mt-1 text-2xl font-bold text-green-600">GHS {{ number_format($payment->amount, 2) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Status</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                    @if($payment->status === 'completed') bg-green-100 text-green-800
                                    @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($payment->status === 'failed') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                            <p class="mt-1 text-lg capitalize">{{ $payment->payment_method }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Date</label>
                            <p class="mt-1 text-lg">{{ $payment->payment_date->format('F d, Y') }}</p>
                        </div>
                        @if($payment->transaction_reference)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Transaction Reference</label>
                            <p class="mt-1 text-sm font-mono bg-gray-100 p-2 rounded">{{ $payment->transaction_reference }}</p>
                        </div>
                        @endif
                    </div>

                    @if($payment->notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <p class="mt-1 text-gray-700">{{ $payment->notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Loan Information -->
                @if($payment->loan)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Related Loan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Loan ID</label>
                            <p class="mt-1 text-lg">#{{ $payment->loan->id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Loan Amount</label>
                            <p class="mt-1 text-lg">GHS {{ number_format($payment->loan->amount, 2) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Remaining Balance</label>
                            <p class="mt-1 text-lg font-medium text-blue-600">
                                GHS {{ number_format($payment->loan->remaining_balance, 2) }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Loan Status</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                    @if($payment->loan->status === 'returned') bg-green-100 text-green-800
                                    @elseif($payment->loan->status === 'borrowed') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($payment->loan->status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('loans.show', $payment->loan->id) }}" class="text-blue-500 hover:text-blue-700 text-sm">
                            View full loan details →
                        </a>
                    </div>
                </div>
                @endif

                <!-- Gateway Response (if available) -->
                @if($payment->payment_gateway_response)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Gateway Response</h2>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <pre class="text-xs text-gray-700 overflow-x-auto">{{ json_encode($payment->payment_gateway_response, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Payment Summary -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Payment Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment ID</span>
                            <span class="font-medium">#{{ $payment->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Amount</span>
                            <span class="font-medium text-green-600">GHS {{ number_format($payment->amount, 2) }}</span>
                        </div>
                        @if($payment->interest_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Interest Portion</span>
                            <span class="font-medium text-orange-600">GHS {{ number_format($payment->interest_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Principal Portion</span>
                            <span class="font-medium text-blue-600">GHS {{ number_format($payment->principal_amount, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-600">Method</span>
                            <span class="font-medium capitalize">{{ $payment->payment_method }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status</span>
                            <span class="font-medium
                                @if($payment->status === 'completed') text-green-600
                                @elseif($payment->status === 'pending') text-yellow-600
                                @elseif($payment->status === 'failed') text-red-600
                                @else text-gray-600 @endif">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Status Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Status Information</h3>
                    @if($payment->status === 'completed')
                        <div class="bg-green-50 border-l-4 border-green-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">
                                        <strong>Payment Completed:</strong> This payment has been successfully processed and applied to your loan balance.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($payment->status === 'pending')
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-13a.75.75 0 00-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 000-1.5h-3.25V5z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        @if($payment->payment_method === 'manual')
                                            <strong>Pending Approval:</strong> Your manual payment is waiting for admin approval.
                                        @else
                                            <strong>Processing:</strong> Your payment is being processed by the payment gateway.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($payment->status === 'failed')
                        <div class="bg-red-50 border-l-4 border-red-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        <strong>Payment Failed:</strong> This payment could not be processed. Please try again or contact support.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                @if(Auth::user()->isAdmin() && $payment->status === 'pending' && $payment->payment_method === 'manual')
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Admin Actions</h3>
                    <div class="space-y-3">
                        <form action="{{ route('payments.update', $payment->id) }}" method="POST" class="inline-block w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md mb-2">
                                Approve Manual Payment
                            </button>
                        </form>
                        <form action="{{ route('payments.update', $payment->id) }}" method="POST" class="inline-block w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="failed">
                            <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">
                                Reject Payment
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
