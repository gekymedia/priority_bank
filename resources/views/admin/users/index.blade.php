@extends('layouts.app')
@section('title', 'User Management')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">User Management</h1>
        <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Add New User
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search by name, email, phone, or account id..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div class="min-w-[150px]">
                <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>
            <div class="min-w-[150px]">
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-search mr-2"></i>Search
            </button>
            @if(request('search') || request('role'))
                <a href="{{ route('admin.users.index') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    @php
                        /** @var float $netBalance Same formula as User::net_balance (set on index via batch aggregates). */
                        $netBalance = (float) ($user->aggregated_net_balance ?? 0);
                    @endphp
                    <tr class="{{ $user->ownedSystems->isEmpty() ? 'bg-yellow-50 hover:bg-yellow-100' : 'hover:bg-gray-50' }}">
                        <td class="px-6 py-4 align-top">
                            <div class="flex items-start">
                                @if($user->profile_photo_path)
                                    <img class="h-10 w-10 rounded-full object-cover mr-3 shrink-0 mt-0.5" 
                                         src="{{ route('profile.photo', ['path' => $user->profile_photo_path]) }}" 
                                         alt="{{ $user->name }}">
                                @else
                                    <div class="h-10 w-10 min-w-[2.5rem] min-h-[2.5rem] rounded-full overflow-hidden flex items-center justify-center text-white text-sm font-medium mr-3 shrink-0 bg-indigo-500 mt-0.5">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('admin.users.statement', $user) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 hover:underline block">{{ $user->name }}</a>
                                    <div class="text-xs text-gray-500 mt-1 break-all">{{ $user->email }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        <span class="font-medium text-gray-600">Linked:</span>
                                        @if($user->ownedSystems->isNotEmpty())
                                            @foreach($user->ownedSystems as $sys)
                                                <span class="inline-block px-1.5 py-0.5 rounded bg-gray-100 text-gray-800 mr-1 mt-0.5">{{ $sys->name ?? $sys->system_id }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </div>
                                    <div class="text-xs mt-0.5">
                                        <span class="px-1.5 py-0.5 rounded font-medium {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">{{ ucfirst($user->role) }}</span>
                                    </div>
                                    {{-- Balance stacked under identity on small screens (no horizontal scroll) --}}
                                    <div class="md:hidden mt-3 pt-3 border-t border-gray-200">
                                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Balance</div>
                                        <span class="text-base font-semibold tabular-nums {{ $netBalance >= 0 ? 'text-emerald-700' : 'text-red-700' }}" title="Net balance — matches user profile (successful savings + income transactions − borrowed group loans − expense transactions)">
                                            GHS {{ number_format($netBalance, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap align-top">
                            <span class="text-sm font-semibold tabular-nums {{ $netBalance >= 0 ? 'text-emerald-700' : 'text-red-700' }}" title="Net balance — matches user profile (successful savings + income transactions − borrowed group loans − expense transactions)">
                                GHS {{ number_format($netBalance, 2) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 align-top whitespace-normal md:whitespace-nowrap">
                            <div class="text-sm text-gray-900 break-words md:break-normal">{{ $user->phone ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            @if($user->status === 'pending')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @elseif($user->status === 'approved')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Approved
                                </span>
                            @elseif($user->status === 'rejected')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst($user->status ?? 'N/A') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 align-top">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-top">
                            <div class="flex justify-end space-x-2">
                                @if($user->status === 'pending')
                                    <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.reject', $user) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to reject this user?');">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($user->id !== auth()->id() && $user->status === 'approved')
                                    <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-purple-600 hover:text-purple-900" title="Impersonate">
                                            <i class="fas fa-user-secret"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.users.send-welcome-message', $user) }}" method="POST" class="inline" onsubmit="return confirm('Send welcome message via all channels (SMS, Email, GekyChat, WhatsApp) to this user?');">
                                        @csrf
                                        <input type="hidden" name="channel" value="all">
                                        <button type="submit" class="text-green-600 hover:text-green-900" title="Send welcome (multi channel)">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.users.show', $user) }}" class="text-indigo-600 hover:text-indigo-900" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.statement', $user) }}" class="text-teal-600 hover:text-teal-900" title="View statement">
                                    <i class="fas fa-file-alt"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:text-blue-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
