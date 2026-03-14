@extends('layouts.app')
@section('title', 'User Profile – ' . $user->name)
@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 mb-6">
        <i class="fas fa-arrow-left mr-2"></i>Back to Users
    </a>

    {{-- Profile header card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 h-24"></div>
        <div class="px-6 pb-6 -mt-12 relative">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div class="flex items-end gap-4">
                    <div class="w-24 h-24 rounded-xl bg-white border-4 border-white shadow-md flex items-center justify-center text-2xl font-bold text-indigo-600 bg-indigo-50 shrink-0">
                        @if($user->profile_photo_path)
                            <img src="{{ route('profile.photo', ['path' => $user->profile_photo_path]) }}" alt="" class="w-full h-full rounded-lg object-cover">
                        @else
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        @endif
                    </div>
                    <div class="pb-1">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                        <p class="text-gray-500 text-sm mt-0.5">{{ $user->email }}</p>
                        <span class="inline-flex mt-2 px-3 py-1 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 sm:pb-1">
                    <a href="{{ route('admin.users.statement', $user) }}" class="inline-flex items-center px-4 py-2.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium text-sm shadow-sm">
                        <i class="fas fa-file-alt mr-2"></i>View Statement
                    </a>
                    <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium text-sm">
                        <i class="fas fa-edit mr-2"></i>Edit User
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Details card --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Details</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Name</dt>
                    <dd class="mt-1 text-gray-900 font-medium">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Email</dt>
                    <dd class="mt-1 text-gray-900 break-all">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Phone</dt>
                    <dd class="mt-1 text-gray-900">{{ $user->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Account ID</dt>
                    <dd class="mt-1 text-gray-900 font-mono text-sm">{{ $user->account_id ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Role</dt>
                    <dd class="mt-1">
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Preferred Currency</dt>
                    <dd class="mt-1 text-gray-900">{{ $user->preferred_currency ?? 'GHS' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Member Since</dt>
                    <dd class="mt-1 text-gray-900">{{ $user->created_at->format('F d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 uppercase tracking-wider">Last Updated</dt>
                    <dd class="mt-1 text-gray-900">{{ $user->updated_at->format('F d, Y g:i A') }}</dd>
                </div>
            </dl>
        </div>

        {{-- Statistics card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Statistics</h2>
            <div class="space-y-4">
                <div class="p-4 bg-blue-50 rounded-lg border border-blue-100">
                    <p class="text-xs font-medium text-blue-600 uppercase tracking-wider">Savings Balance</p>
                    <p class="text-xl font-bold text-blue-700 mt-1">{{ $user->preferred_currency ?? 'GHS' }} {{ number_format($user->savings_balance, 2) }}</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-100">
                    <p class="text-xs font-medium text-red-600 uppercase tracking-wider">Loan Balance</p>
                    <p class="text-xl font-bold text-red-700 mt-1">{{ $user->preferred_currency ?? 'GHS' }} {{ number_format($user->loan_balance, 2) }}</p>
                </div>
                <div class="p-4 bg-emerald-50 rounded-lg border border-emerald-100">
                    <p class="text-xs font-medium text-emerald-600 uppercase tracking-wider">Net Balance</p>
                    <p class="text-xl font-bold text-emerald-700 mt-1">{{ $user->preferred_currency ?? 'GHS' }} {{ number_format($user->net_balance, 2) }}</p>
                </div>
                <div class="pt-3 border-t border-gray-100 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Incomes</span>
                        <span class="font-semibold text-gray-900">{{ $user->total_incomes_count }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Expenses</span>
                        <span class="font-semibold text-gray-900">{{ $user->total_expenses_count }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Loans</span>
                        <span class="font-semibold text-gray-900">{{ $user->total_loans_count }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Send welcome message --}}
    <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Send welcome message</h3>
        <form action="{{ route('admin.users.send-welcome-message', $user) }}" method="POST" class="flex flex-wrap items-end gap-3">
            @csrf
            <div>
                <label for="welcome_channel" class="block text-xs font-medium text-gray-500 mb-1">Channel</label>
                <select name="channel" id="welcome_channel" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="all">All</option>
                    <option value="gekychat">GekyChat</option>
                    <option value="sms">SMS</option>
                    <option value="email">Email</option>
                    <option value="whatsapp">WhatsApp</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                <i class="fas fa-paper-plane mr-2"></i>Send welcome
            </button>
        </form>
    </div>
</div>
@endsection
