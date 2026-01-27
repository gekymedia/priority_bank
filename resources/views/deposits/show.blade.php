@extends('layouts.app')

@section('title', 'Deposit Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Deposit Details</h1>
                <p class="text-gray-600 mt-1">View deposit information</p>
            </div>
            <a href="{{ route('deposits.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Deposits
            </a>
        </div>

        <!-- Deposit Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Deposit Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Deposited By</p>
                    <p class="text-lg font-medium">{{ $deposit->user->name }}</p>
                    <p class="text-sm text-gray-600">{{ $deposit->user->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Date</p>
                    <p class="text-lg font-medium">{{ $deposit->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Amount</p>
                    <p class="text-lg font-medium text-blue-600">GHS {{ number_format($deposit->amount, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                        @if($deposit->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($deposit->status === 'approved') bg-green-100 text-green-800
                        @elseif($deposit->status === 'rejected') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($deposit->status) }}
                    </span>
                </div>
                @if($deposit->description)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Description</p>
                    <p class="text-gray-700 mt-1">{{ $deposit->description }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Approval Information (if approved/rejected) -->
        @if($deposit->status !== 'pending')
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Decision Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($deposit->approved_by)
                <div>
                    <p class="text-sm text-gray-500">Approved By</p>
                    <p class="text-lg font-medium">{{ $deposit->approver->name }}</p>
                </div>
                @endif
                @if($deposit->approved_at)
                <div>
                    <p class="text-sm text-gray-500">Decision Date</p>
                    <p class="text-lg font-medium">{{ $deposit->approved_at->format('M d, Y h:i A') }}</p>
                </div>
                @endif
                @if($deposit->admin_notes)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Admin Notes</p>
                    <p class="text-gray-700 mt-1">{{ $deposit->admin_notes }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Approval Actions (Admin only, pending deposits) -->
        @if(Auth::user()->isAdmin() && $deposit->status === 'pending')
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Approve Deposit</h2>
            <div class="flex space-x-3">
                <form action="{{ route('deposits.approve', $deposit) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700"
                            onclick="return confirm('Approve this deposit? This will create an income transaction against Priority Bank source.')">
                        <i class="fas fa-check mr-2"></i> Approve & Record Transaction
                    </button>
                </form>
                <form action="{{ route('deposits.reject', $deposit) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50"
                            onclick="return confirm('Reject this deposit?')">
                        <i class="fas fa-times mr-2"></i> Reject
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
