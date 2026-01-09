@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('income-categories.index') }}" class="text-blue-500 hover:text-blue-700 mb-4 inline-block">
            ← Back to Categories
        </a>
        <h1 class="text-2xl font-bold">Edit Income Category</h1>
    </div>

    <div class="bg-white shadow-md rounded-lg p-6">
        <form action="{{ route('income-categories.update', $incomeCategory) }}" method="POST">
            @csrf
            @method('PATCH')
            
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Category Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $incomeCategory->name) }}"
                       class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                       placeholder="e.g., Salary, Business Income, Investment"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    @if($incomeCategory->user_id === null)
                        This is a global category (available to all users).
                    @else
                        This is a personal category (only visible to you).
                    @endif
                </p>
                @if($incomeCategory->incomes()->count() > 0)
                    <p class="mt-2 text-sm text-yellow-600">
                        ⚠️ This category has {{ $incomeCategory->incomes()->count() }} {{ Str::plural('income record', $incomeCategory->incomes()->count()) }}. 
                        Changing the name will update all associated records.
                    </p>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('income-categories.index') }}" 
                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Update Category
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
