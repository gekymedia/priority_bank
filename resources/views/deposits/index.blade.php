@extends('layouts.app')

@section('title', 'Deposits')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Deposits</h1>
            <p class="text-gray-600 mt-1">Manage deposit requests</p>
        </div>
        @if(!Auth::user()->isAdmin())
            <a href="{{ route('deposits.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
                Request New Deposit
            </a>
        @endif
    </div>

    <!-- Deposits Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        @if(Auth::user()->isAdmin())
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($deposits as $deposit)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $deposit->created_at->format('M d, Y') }}
                        </td>
                        @if(Auth::user()->isAdmin())
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $deposit->user->name }}
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">
                            GHS {{ number_format($deposit->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $deposit->description ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($deposit->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($deposit->status === 'approved') bg-green-100 text-green-800
                                @elseif($deposit->status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($deposit->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('deposits.show', $deposit) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            @if(Auth::user()->isAdmin() && $deposit->status === 'pending')
                                <form action="{{ route('deposits.approve', $deposit) }}" method="POST" class="inline mr-2">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 text-sm" onclick="return confirm('Approve this deposit? This will create an income transaction.')">Approve</button>
                                </form>
                                <form action="{{ route('deposits.reject', $deposit) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm" onclick="return confirm('Reject this deposit?')">Reject</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ Auth::user()->isAdmin() ? '6' : '5' }}" class="px-6 py-4 text-center text-gray-500">
                            No deposits found.
                            @if(!Auth::user()->isAdmin())
                                <br><a href="{{ route('deposits.create') }}" class="text-blue-500 hover:text-blue-700">Submit your first deposit request</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if($deposits->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $deposits->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
