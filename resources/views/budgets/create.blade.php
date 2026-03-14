@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Add New Budget</h1>
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('budgets.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="month" class="block text-gray-700 font-medium mb-2">Month</label>
                <input type="month" name="month" id="month" class="w-full px-4 py-2 border rounded-lg @error('month') border-red-500 @enderror" value="{{ old('month', now()->format('Y-m')) }}" required>
                @error('month')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="amount" class="block text-gray-700 font-medium mb-2">Budget Amount (GHS)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="w-full px-4 py-2 border rounded-lg @error('amount') border-red-500 @enderror" value="{{ old('amount') }}" required>
                @error('amount')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center justify-between">
                <a href="{{ route('budgets.index') }}" class="text-gray-600 hover:text-gray-800">Back to Budgets</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Save Budget</button>
            </div>
        </form>
    </div>
</div>
@endsection