@extends('layouts.app')

@section('title', 'Add New Transaction')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Add New Transaction</h1>

    <form action="{{ route('transactions.store') }}" method="POST">
        @csrf
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Type Selection -->
            <div class="mb-4">
                <label for="type" class="block text-sm font-medium text-gray-700">Transaction Type</label>
                <select name="type" id="type" required
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">Select Type</option>
                    <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Dynamic Category Selection -->
            <div class="mb-4">
                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" id="category" required
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">Select Category</option>
                    @if(old('type') && isset($categories[old('type')]))
                        @foreach($categories[old('type')] as $category)
                            <option value="{{ $category }}" {{ old('category') == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @error('category')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Amount -->
            <div class="mb-4">
                <label for="amount" class="block text-sm font-medium text-gray-700">Amount (GHS)</label>
                <input type="number" step="0.01" name="amount" id="amount" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    value="{{ old('amount') }}">
                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date -->
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" name="date" id="date" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    value="{{ old('date', now()->format('Y-m-d')) }}">
                @error('date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- External System Selection -->
            <div class="mb-4">
                <label for="external_system_id" class="block text-sm font-medium text-gray-700">Sync to External System (Optional)</label>
                <select name="external_system_id" id="external_system_id" 
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">None - Internal Only</option>
                    @foreach($systems as $id => $name)
                        <option value="{{ $id }}" {{ old('external_system_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('external_system_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Select an external system to sync this transaction. If selected, this transaction will be sent to the external system via webhook.</p>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                    Save Transaction
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const categories = @json($categories);
    
    document.getElementById('type').addEventListener('change', function() {
        const type = this.value;
        const categorySelect = document.getElementById('category');
        
        // Clear existing options
        categorySelect.innerHTML = '<option value="">Select Category</option>';
        
        if (type && categories[type]) {
            categories[type].forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categorySelect.appendChild(option);
            });
        }
    });

    // Initialize with old input if available
    @if(old('type'))
        document.getElementById('type').dispatchEvent(new Event('change'));
    @endif
</script>
@endpush
@endsection