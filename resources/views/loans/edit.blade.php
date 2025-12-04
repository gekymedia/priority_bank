@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Loan Record</h1>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('loans.update', $loan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="borrower" class="block text-gray-700 font-medium mb-2">Borrower Name</label>
                <input type="text" name="borrower" id="borrower" class="w-full px-4 py-2 border rounded-lg @error('borrower') border-red-500 @enderror" value="{{ old('borrower', $loan->borrower) }}" required>
                @error('borrower')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="amount" class="block text-gray-700 font-medium mb-2">Amount (GHS)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="w-full px-4 py-2 border rounded-lg @error('amount') border-red-500 @enderror" value="{{ old('amount', $loan->amount) }}" required>
                @error('amount')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Status</label>
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="status" value="given" class="form-radio" {{ old('status', $loan->status) == 'given' ? 'checked' : '' }} required>
                        <span class="ml-2">Given</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="status" value="repaid" class="form-radio" {{ old('status', $loan->status) == 'repaid' ? 'checked' : '' }}>
                        <span class="ml-2">Repaid</span>
                    </label>
                </div>
                @error('status')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="date" class="block text-gray-700 font-medium mb-2">Date</label>
                <input type="date" name="date" id="date" class="w-full px-4 py-2 border rounded-lg @error('date') border-red-500 @enderror" value="{{ old('date', $loan->date->format('Y-m-d')) }}" required>
                @error('date')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-medium mb-2">Description (Optional)</label>
                <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 border rounded-lg">{{ old('description', $loan->description) }}</textarea>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('loans.index') }}" class="text-gray-600 hover:text-gray-800">Back to Loans</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Update Loan Record
                </button>
            </div>
        </form>
    </div>
</div>
@endsection