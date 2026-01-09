@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Add New Expense</h1>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('expenses.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="expense_category_id" class="block text-gray-700 font-medium mb-2">Category</label>
                <select name="expense_category_id" id="expense_category_id" class="w-full px-4 py-2 border rounded-lg @error('expense_category_id') border-red-500 @enderror" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $id => $name)
                        <option value="{{ $id }}" {{ old('expense_category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('expense_category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="amount" class="block text-gray-700 font-medium mb-2">Amount (GHS)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="w-full px-4 py-2 border rounded-lg @error('amount') border-red-500 @enderror" value="{{ old('amount') }}" required>
                @error('amount')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="date" class="block text-gray-700 font-medium mb-2">Date</label>
                <input type="date" name="date" id="date" class="w-full px-4 py-2 border rounded-lg @error('date') border-red-500 @enderror" value="{{ old('date') ?? now()->format('Y-m-d') }}" required>
                @error('date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="channel" class="block text-gray-700 font-medium mb-2">Channel</label>
                <select name="channel" id="channel" class="w-full px-4 py-2 border rounded-lg @error('channel') border-red-500 @enderror" required>
                    <option value="">Select Channel</option>
                    @foreach($channels as $value => $label)
                        <option value="{{ $value }}" {{ old('channel') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('channel')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="account_id" class="block text-gray-700 font-medium mb-2">Account</label>
                <select name="account_id" id="account_id" class="w-full px-4 py-2 border rounded-lg @error('account_id') border-red-500 @enderror" required>
                    <option value="">Select Account</option>
                    @foreach($accounts as $id => $name)
                        <option value="{{ $id }}" {{ old('account_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('account_id')
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

            <div class="mb-4">
                <label for="external_system_id" class="block text-gray-700 font-medium mb-2">Sync to External System (Optional)</label>
                <select name="external_system_id" id="external_system_id" class="w-full px-4 py-2 border rounded-lg @error('external_system_id') border-red-500 @enderror">
                    <option value="">None - Internal Only</option>
                    @foreach($systems as $id => $name)
                        <option value="{{ $id }}" {{ old('external_system_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('external_system_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-gray-500 text-xs mt-1">Select an external system to sync this expense record. If selected, this transaction will be sent to the external system via webhook.</p>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('expenses.index') }}" class="text-gray-600 hover:text-gray-800">Back to Expenses</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Save Expense
                </button>
            </div>
        </form>
    </div>
</div>
@endsection