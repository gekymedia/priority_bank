@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Create New Payout</h1>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('payouts.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="purpose" class="block text-gray-700 font-medium mb-2">Purpose</label>
                <select name="purpose" id="purpose" class="w-full px-4 py-2 border rounded-lg @error('purpose') border-red-500 @enderror" required>
                    <option value="">Select Purpose</option>
                    <option value="Salary" {{ old('purpose') == 'Salary' ? 'selected' : '' }}>Salary</option>
                    <option value="Personal" {{ old('purpose') == 'Personal' ? 'selected' : '' }}>Personal</option>
                    <option value="Bonus" {{ old('purpose') == 'Bonus' ? 'selected' : '' }}>Bonus</option>
                    <option value="Other" {{ old('purpose') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('purpose')
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
                <label for="receiver" class="block text-gray-700 font-medium mb-2">Receiver (Optional)</label>
                <input type="text" name="receiver" id="receiver" class="w-full px-4 py-2 border rounded-lg" value="{{ old('receiver') }}">
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
                <a href="{{ route('payouts.index') }}" class="text-gray-600 hover:text-gray-800">Back to Payouts</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Record Payout
                </button>
            </div>
        </form>
    </div>
</div>
@endsection