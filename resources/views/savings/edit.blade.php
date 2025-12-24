@extends('layouts.app')

@section('title', 'Edit Savings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Edit Savings Deposit</h1>
                <p class="text-gray-600 mt-1">Update savings deposit details</p>
            </div>
            <a href="{{ route('savings.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Savings
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('savings.update', $saving->id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Amount -->
                <div class="mb-4">
                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                        Deposit Amount (GHS) *
                    </label>
                    <input type="number" id="amount" name="amount" value="{{ old('amount', $saving->amount) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 @error('amount') border-red-500 @enderror"
                           placeholder="0.00" step="0.01" min="1" required>
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deposit Date -->
                <div class="mb-4">
                    <label for="deposit_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Deposit Date *
                    </label>
                    <input type="date" id="deposit_date" name="deposit_date" value="{{ old('deposit_date', $saving->deposit_date->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 @error('deposit_date') border-red-500 @enderror"
                           max="{{ date('Y-m-d') }}" required>
                    @error('deposit_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if(Auth::user()->isAdmin())
                <!-- Status (Admin only) -->
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status *
                    </label>
                    <select id="status" name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 @error('status') border-red-500 @enderror">
                        <option value="available" {{ old('status', $saving->status) === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="locked" {{ old('status', $saving->status) === 'locked' ? 'selected' : '' }}>Locked</option>
                        <option value="withdrawn" {{ old('status', $saving->status) === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea id="notes" name="notes" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500 @error('notes') border-red-500 @enderror"
                              placeholder="Any additional notes about this deposit...">{{ old('notes', $saving->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if($saving->status === 'available')
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Note:</strong> Changes to available savings may affect loan availability for the group.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('savings.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Update Savings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

