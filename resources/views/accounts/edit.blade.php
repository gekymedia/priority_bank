@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Account</h1>
    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('accounts.update', $account->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-medium mb-2">Account Name</label>
                <input type="text" name="name" id="name" class="w-full px-4 py-2 border rounded-lg @error('name') border-red-500 @enderror" value="{{ old('name', $account->name) }}" required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="type" class="block text-gray-700 font-medium mb-2">Type</label>
                <select name="type" id="type" class="w-full px-4 py-2 border rounded-lg @error('type') border-red-500 @enderror" required>
                    <option value="">Select Type</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" {{ old('type', $account->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="opening_balance" class="block text-gray-700 font-medium mb-2">Opening Balance (Optional)</label>
                <input type="number" step="0.01" name="opening_balance" id="opening_balance" class="w-full px-4 py-2 border rounded-lg @error('opening_balance') border-red-500 @enderror" value="{{ old('opening_balance', $account->opening_balance) }}">
                @error('opening_balance')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center justify-between">
                <a href="{{ route('accounts.index') }}" class="text-gray-600 hover:text-gray-800">Back to Accounts</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Update Account</button>
            </div>
        </form>
    </div>
</div>
@endsection