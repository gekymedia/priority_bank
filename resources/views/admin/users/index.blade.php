@extends('layouts.app')
@section('title', 'User Management')
@section('content')
@php
    $hasFilters = request()->filled('search') || request()->filled('role') || request()->filled('status');
@endphp
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">User Management</h1>
            <p class="text-sm text-gray-500 mt-1">Accounts, approval status, and balance overview</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center justify-center bg-indigo-600 text-white px-5 py-2.5 rounded-xl hover:bg-indigo-700 font-medium text-sm shadow-sm transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Add New User
        </a>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-6 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Balance & membership summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Users</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ number_format($summary['total_users']) }}</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 mt-3 text-xs">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-medium">
                    {{ $summary['approved_users'] }} approved
                </span>
                @if($summary['pending_users'] > 0)
                    <a href="{{ route('admin.users.index', array_merge(request()->except('page', 'status'), ['status' => 'pending'])) }}"
                       class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-medium hover:bg-amber-100">
                        {{ $summary['pending_users'] }} pending
                    </a>
                @endif
                @if($summary['rejected_users'] > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-red-50 text-red-700 font-medium">
                        {{ $summary['rejected_users'] }} rejected
                    </span>
                @endif
            </div>
            @if($hasFilters)
                <p class="text-[11px] text-gray-400 mt-2">Based on current filters</p>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Savings</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1 tabular-nums">GHS {{ number_format($summary['total_savings'], 2) }}</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-piggy-bank"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3">Successful deposits + income credits</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Outstanding Loans</p>
                    <p class="text-2xl font-bold text-rose-700 mt-1 tabular-nums">GHS {{ number_format($summary['total_loans'], 2) }}</p>
                </div>
                <div class="h-10 w-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3">Borrowed group loans + expense debits</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 {{ $summary['total_net'] >= 0 ? 'ring-1 ring-emerald-100' : 'ring-1 ring-rose-100' }}">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Combined Net Balance</p>
                    <p class="text-2xl font-bold mt-1 tabular-nums {{ $summary['total_net'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        GHS {{ number_format($summary['total_net'], 2) }}
                    </p>
                </div>
                <div class="h-10 w-10 rounded-lg {{ $summary['total_net'] >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center">
                    <i class="fas fa-scale-balanced"></i>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3">Savings minus outstanding loans across listed users</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 sm:p-5 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col lg:flex-row gap-3">
            <div class="flex-1">
                <label for="user-search" class="sr-only">Search users</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input id="user-search" type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by name, email, phone, or account id..."
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:flex gap-3">
                <select name="role" class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent min-w-[140px]">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                </select>
                <select name="status" class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent min-w-[140px]">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <div class="col-span-2 sm:col-span-1 flex gap-2">
                    <button type="submit" class="flex-1 sm:flex-none inline-flex items-center justify-center bg-indigo-600 text-white px-5 py-2.5 rounded-xl hover:bg-indigo-700 text-sm font-medium">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                    @if($hasFilters)
                        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Users table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">People</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }}
                </p>
            </div>
            <p class="text-[11px] text-gray-400 max-w-md sm:text-right">
                Net = savings/income − group loans/expenses (same as each user’s profile)
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50/80 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-3">User</th>
                        <th class="px-5 py-3 whitespace-nowrap">Savings</th>
                        <th class="px-5 py-3 whitespace-nowrap">Loans</th>
                        <th class="px-5 py-3 whitespace-nowrap">Net Balance</th>
                        <th class="px-5 py-3">Phone</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 hidden lg:table-cell">Joined</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        @php
                            $savingsBalance = (float) ($user->aggregated_savings_balance ?? 0);
                            $loanBalance = (float) ($user->aggregated_loan_balance ?? 0);
                            $netBalance = (float) ($user->aggregated_net_balance ?? 0);
                            $isSystem = $user->ownedSystems->isNotEmpty();
                        @endphp
                        <tr class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-5 py-4 align-middle">
                                <div class="flex items-start gap-3 min-w-[220px]">
                                    @if($user->profile_photo_path)
                                        <img class="h-10 w-10 rounded-full object-cover shrink-0 ring-2 ring-white shadow-sm"
                                             src="{{ route('profile.photo', ['path' => $user->profile_photo_path]) }}"
                                             alt="{{ $user->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full shrink-0 flex items-center justify-center text-white text-sm font-semibold bg-indigo-500 shadow-sm">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.users.statement', $user) }}"
                                           class="text-sm font-semibold text-gray-900 hover:text-indigo-600 hover:underline block truncate">
                                            {{ $user->name }}
                                        </a>
                                        <div class="text-xs text-gray-500 mt-0.5 truncate">{{ $user->email }}</div>
                                        <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                                            <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wide {{ $user->role === 'admin' ? 'bg-violet-100 text-violet-800' : 'bg-slate-100 text-slate-600' }}">
                                                {{ $user->role }}
                                            </span>
                                            @if($isSystem)
                                                @foreach($user->ownedSystems as $sys)
                                                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-700" title="Linked system">
                                                        {{ $sys->name ?? $sys->system_id }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-[10px] text-gray-400">No linked system</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 align-middle whitespace-nowrap">
                                <span class="text-sm font-semibold tabular-nums text-blue-700">
                                    GHS {{ number_format($savingsBalance, 2) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 align-middle whitespace-nowrap">
                                <span class="text-sm font-semibold tabular-nums {{ $loanBalance > 0 ? 'text-rose-700' : 'text-gray-400' }}">
                                    GHS {{ number_format($loanBalance, 2) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 align-middle whitespace-nowrap">
                                <div class="inline-flex flex-col">
                                    <span class="text-sm font-bold tabular-nums {{ $netBalance >= 0 ? 'text-emerald-700' : 'text-rose-700' }}"
                                          title="Net balance — matches user profile">
                                        GHS {{ number_format($netBalance, 2) }}
                                    </span>
                                    <span class="text-[10px] font-medium uppercase tracking-wide {{ $netBalance >= 0 ? 'text-emerald-600/70' : 'text-rose-600/70' }}">
                                        {{ $netBalance >= 0 ? 'Credit' : 'Debit' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <span class="text-sm text-gray-700 whitespace-nowrap">{{ $user->phone ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-4 align-middle whitespace-nowrap">
                                @if($user->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Pending</span>
                                @elseif($user->status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Approved</span>
                                @elseif($user->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">{{ ucfirst($user->status ?? 'N/A') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 align-middle whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <div class="flex justify-end items-center gap-1">
                                    @if($user->status === 'pending')
                                        <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="h-8 w-8 rounded-lg text-emerald-600 hover:bg-emerald-50" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.users.reject', $user) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Are you sure you want to reject this user?');">
                                            @csrf
                                            <button type="submit" class="h-8 w-8 rounded-lg text-red-600 hover:bg-red-50" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($user->id !== auth()->id() && $user->status === 'approved')
                                        <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="h-8 w-8 rounded-lg text-violet-600 hover:bg-violet-50" title="Impersonate">
                                                <i class="fas fa-user-secret"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.users.send-welcome-message', $user) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Send welcome message via all channels (SMS, Email, GekyChat, WhatsApp) to this user?');">
                                            @csrf
                                            <input type="hidden" name="channel" value="all">
                                            <button type="submit" class="h-8 w-8 rounded-lg text-emerald-600 hover:bg-emerald-50" title="Send welcome (multi channel)">
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.users.show', $user) }}" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-indigo-600 hover:bg-indigo-50" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.statement', $user) }}" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-teal-600 hover:bg-teal-50" title="View statement">
                                        <i class="fas fa-file-alt"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="mx-auto h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 mb-3">
                                    <i class="fas fa-user-slash text-lg"></i>
                                </div>
                                <p class="text-sm font-medium text-gray-700">No users found</p>
                                <p class="text-xs text-gray-500 mt-1">Try adjusting your search or filters.</p>
                                @if($hasFilters)
                                    <a href="{{ route('admin.users.index') }}" class="inline-flex mt-4 text-sm text-indigo-600 hover:text-indigo-800 font-medium">Clear filters</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="bg-gray-50/80 px-5 py-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
