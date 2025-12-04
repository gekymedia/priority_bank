@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Add New Expense</h1>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('expenses.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="category" class="block text-gray-700 font-medium mb-2">Category</label>
                <select name="category" id="category" class="w-full px-4 py-2 border rounded-lg @error('category') border-red-500 @enderror" required>
                    <option value="">Select Category</option>
                    <option value="Fuel" {{ old('category') == 'Fuel' ? 'selected' : '' }}>Fuel</option>
                    <option value="Airtime" {{ old('category') == 'Airtime' ? 'selected' : '' }}>Airtime</option>
                    <option value="Groceries" {{ old('category') == 'Groceries' ? 'selected' : '' }}>Groceries</option>
                    <option value="Utilities" {{ old('category') == 'Utilities' ? 'selected' : '' }}>Utilities</option>
                    <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('category')
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
                <label for="description" class="block text-gray-700 font-medium mb-2">Description (Optional)</label>
                <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 border rounded-lg">{{ old('description') }}</textarea>
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