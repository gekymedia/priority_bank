@extends('layouts.app')
@section('title', 'User Details')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-800 mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>Back to Users
        </a>
        <h1 class="text-3xl font-bold">User Details: {{ $user->name }}</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Information -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">User Information</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Name</label>
                    <p class="mt-1 text-lg text-gray-900">{{ $user->name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500">Email</label>
                    <p class="mt-1 text-lg text-gray-900">{{ $user->email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500">Phone</label>
                    <p class="mt-1 text-lg text-gray-900">{{ $user->phone ?? 'N/A' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500">Role</label>
                    <span class="mt-1 inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500">Preferred Currency</label>
                    <p class="mt-1 text-lg text-gray-900">{{ $user->preferred_currency ?? 'GHS' }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500">Member Since</label>
                    <p class="mt-1 text-lg text-gray-900">{{ $user->created_at->format('F d, Y') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                    <p class="mt-1 text-lg text-gray-900">{{ $user->updated_at->format('F d, Y h:i A') }}</p>
                </div>
            </div>

            <div class="mt-6 flex space-x-4">
                <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-edit mr-2"></i>Edit User
                </a>
                @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" 
                          onsubmit="return confirm('Are you sure you want to delete this user?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            <i class="fas fa-trash mr-2"></i>Delete User
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- User Statistics -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Statistics</h2>
            
            <div class="space-y-4">
                <div class="p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-600">Savings Balance</p>
                    <p class="text-2xl font-bold text-blue-600">GHS {{ number_format($user->savings_balance, 2) }}</p>
                </div>

                <div class="p-4 bg-red-50 rounded-lg">
                    <p class="text-sm text-gray-600">Loan Balance</p>
                    <p class="text-2xl font-bold text-red-600">GHS {{ number_format($user->loan_balance, 2) }}</p>
                </div>

                <div class="p-4 bg-green-50 rounded-lg">
                    <p class="text-sm text-gray-600">Net Balance</p>
                    <p class="text-2xl font-bold text-green-600">GHS {{ number_format($user->net_balance, 2) }}</p>
                </div>

                <div class="pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600 mb-2">Total Incomes</p>
                    <p class="text-lg font-semibold">{{ $user->incomes()->count() }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600 mb-2">Total Expenses</p>
                    <p class="text-lg font-semibold">{{ $user->expenses()->count() }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-600 mb-2">Total Loans</p>
                    <p class="text-lg font-semibold">{{ $user->loans()->count() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
