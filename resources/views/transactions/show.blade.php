@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Transaction Details</h1>
        <div class="flex space-x-2">
            <a href="{{ route('transactions.edit', $transaction) }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                Edit
            </a>
            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md"
                        onclick="return confirm('Are you sure you want to delete this transaction?')">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Info -->
                <div>
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Transaction Information</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Type</p>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $transaction->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Category</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $transaction->category }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Amount</p>
                            <p class="mt-1 text-sm {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'income' ? '+' : '-' }}GHS {{ number_format($transaction->amount, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Additional Details -->
                <div>
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Additional Details</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Date</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $transaction->date->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Description</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $transaction->description ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Created</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $transaction->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Last Updated</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $transaction->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('transactions.index') }}" 
           class="text-blue-500 hover:text-blue-700 font-medium">
            ← Back to all transactions
        </a>
    </div>
</div>
@endsection