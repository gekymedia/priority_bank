@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Budget</h1>
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('budgets.update', $budget->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="expense_category_id" class="block text-gray-700 font-medium mb-2">Expense Category</label>
                <select name="expense_category_id" id="expense_category_id" class="w-full px-4 py-2 border rounded-lg @error('expense_category_id') border-red-500 @enderror" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $id => $name)
                        <option value="{{ $id }}" {{ old('expense_category_id', $budget->expense_category_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('expense_category_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="month" class="block text-gray-700 font-medium mb-2">Month</label>
                <input type="month" name="month" id="month" class="w-full px-4 py-2 border rounded-lg @error('month') border-red-500 @enderror" value="{{ old('month', $budget->month) }}" required>
                @error('month')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="amount" class="block text-gray-700 font-medium mb-2">Budget Amount (GHS)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="w-full px-4 py-2 border rounded-lg @error('amount') border-red-500 @enderror" value="{{ old('amount', $budget->amount) }}" required>
                @error('amount')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center justify-between">
                <a href="{{ route('budgets.index', ['month' => $budget->month]) }}" class="text-gray-600 hover:text-gray-800">Back to Budgets</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Update Budget</button>
            </div>
        </form>
    </div>
</div>
@endsection