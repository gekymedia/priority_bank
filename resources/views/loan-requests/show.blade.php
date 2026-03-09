@extends('layouts.app')

@section('title', 'Loan Request Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Loan Request Details</h1>
                <p class="text-gray-600 mt-1">Review and manage this loan request</p>
            </div>
            <a href="{{ route('loan-requests.index') }}" class="text-gray-600 hover:text-gray-900">
                ← Back to Requests
            </a>
        </div>

        <!-- Request Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Request Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Requested By</p>
                    <p class="text-lg font-medium">{{ $loanRequest->user->name }}</p>
                    <p class="text-sm text-gray-600">{{ $loanRequest->user->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Request Date</p>
                    <p class="text-lg font-medium">{{ $loanRequest->request_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Amount Requested</p>
                    <p class="text-lg font-medium text-blue-600">GHS {{ number_format($loanRequest->amount_requested, 2) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Expected Payback Date</p>
                    <p class="text-lg font-medium">{{ $loanRequest->expected_payback_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                        @if($loanRequest->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($loanRequest->status === 'approved') bg-green-100 text-green-800
                        @elseif($loanRequest->status === 'rejected') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($loanRequest->status) }}
                    </span>
                </div>
                @if($loanRequest->purpose)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Purpose</p>
                    <p class="text-gray-700 mt-1">{{ $loanRequest->purpose }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Approval Information (if approved/rejected) -->
        @if($loanRequest->status !== 'pending')
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Decision Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($loanRequest->amount_approved)
                <div>
                    <p class="text-sm text-gray-500">Amount Approved</p>
                    <p class="text-lg font-medium text-green-600">GHS {{ number_format($loanRequest->amount_approved, 2) }}</p>
                </div>
                @endif
                @if($loanRequest->approved_by)
                <div>
                    <p class="text-sm text-gray-500">Approved By</p>
                    <p class="text-lg font-medium">{{ $loanRequest->approver->name }}</p>
                </div>
                @endif
                @if($loanRequest->approved_at)
                <div>
                    <p class="text-sm text-gray-500">Decision Date</p>
                    <p class="text-lg font-medium">{{ $loanRequest->approved_at->format('M d, Y h:i A') }}</p>
                </div>
                @endif
                @if($loanRequest->admin_notes)
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Admin Notes</p>
                    <p class="text-gray-700 mt-1">{{ $loanRequest->admin_notes }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Record Payment Button (Admin only, approved requests, not yet disbursed) -->
        @if(Auth::user()->isAdmin() && $loanRequest->status === 'approved' && !$loanRequest->hasDisbursementTransaction())
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Record Transaction</h2>
            @if($loanRequest->loan)
            <p class="text-gray-600 mb-4">The loan has been approved. Record the transaction as paid to disburse funds and create the expense transaction.</p>
            @else
            <p class="text-gray-600 mb-4">This request was approved but no loan record was created yet (e.g. from an earlier error). Click below to create the loan record and record the disbursement as a transaction.</p>
            @endif
            <form action="{{ route('loan-requests.record-payment', $loanRequest) }}" method="POST" onsubmit="return confirm('Record this loan payment as a transaction? This will create an expense transaction for {{ $loanRequest->user->name }} (the borrower).');">
                @csrf
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-check-circle mr-2"></i> {{ $loanRequest->loan ? 'Record Transaction as Paid' : 'Create loan & Record Transaction as Paid' }}
                </button>
            </form>
        </div>
        @endif

        <!-- Already disbursed message (Admin only, approved and payment recorded) -->
        @if(Auth::user()->isAdmin() && $loanRequest->status === 'approved' && $loanRequest->hasDisbursementTransaction())
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Disbursement Recorded</h2>
            <p class="text-gray-600">The loan disbursement has been recorded. The expense transaction was created and funds have been disbursed.</p>
            <p class="text-sm text-green-600 mt-2 font-medium"><i class="fas fa-check-circle mr-1"></i> Transaction recorded as paid</p>
        </div>
        @endif

        <!-- Approval Form (Admin only, pending requests) -->
        @if(Auth::user()->isAdmin() && $loanRequest->status === 'pending')
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Approve Loan Request</h2>

            <p class="text-sm text-gray-700 mb-4">Group fund available for loans: <strong>GHS {{ number_format($availableForLoans, 2) }}</strong></p>
            @if($availableForLoans <= 0)
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                <p class="text-sm text-red-800">
                    <strong>Cannot approve:</strong> No group funds available. Approve <a href="{{ route('savings.index', ['approval' => 'pending']) }}" class="underline font-medium">pending savings</a> first so the group fund has money to lend.
                </p>
            </div>
            @elseif($availableForLoans < $loanRequest->amount_requested)
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-md">
                <p class="text-sm text-amber-800">
                    <strong>Group fund is less than requested.</strong> Requested: GHS {{ number_format($loanRequest->amount_requested, 2) }}. You can approve up to <strong>GHS {{ number_format($availableForLoans, 2) }}</strong> (current group fund). Approve more savings to lend more.
                </p>
            </div>
            @endif

            <form action="{{ route('loan-requests.approve', $loanRequest) }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="amount_approved" class="block text-sm font-medium text-gray-700 mb-2">
                        Amount to Approve (GHS) *
                    </label>
                    <input type="number" id="amount_approved" name="amount_approved" 
                           value="{{ old('amount_approved', $availableForLoans > 0 ? $loanRequest->amount_requested : 0) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('amount_approved') border-red-500 @enderror {{ $availableForLoans <= 0 ? 'bg-gray-100' : '' }}"
                           step="0.01" min="{{ $availableForLoans > 0 ? '1' : '0' }}" max="{{ $availableForLoans }}" {{ $availableForLoans <= 0 ? 'readonly' : '' }} required>
                    <p class="text-sm text-gray-500 mt-1">Requested: GHS {{ number_format($loanRequest->amount_requested, 2) }}. You may approve up to <strong>GHS {{ number_format($availableForLoans, 2) }}</strong> (group fund).</p>
                    @error('amount_approved')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="interest_rate_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Interest Rate *
                    </label>
                    <select id="interest_rate_id" name="interest_rate_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('interest_rate_id') border-red-500 @enderror" required>
                        <option value="">Select Interest Rate</option>
                        @foreach($interestRates as $rate)
                            <option value="{{ $rate->id }}" {{ (old('interest_rate_id', $defaultInterestRate->id ?? null) == $rate->id) ? 'selected' : '' }}>
                                {{ $rate->name }} - {{ $rate->rate_percentage }}% 
                                @if($rate->rate_type === 'monthly')
                                    (Monthly)
                                @elseif($rate->rate_type === 'annual')
                                    (Annual)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Default: 1% (MoMo charges/processing fee)</p>
                    @error('interest_rate_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Admin Notes (Optional)
                    </label>
                    <textarea id="admin_notes" name="admin_notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('admin_notes') border-red-500 @enderror"
                              placeholder="Optional notes about this approval...">{{ old('admin_notes') }}</textarea>
                    @error('admin_notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-3">
                    <form action="{{ route('loan-requests.reject', $loanRequest) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                onclick="return confirm('Are you sure you want to reject this loan request?')">
                            Reject Request
                        </button>
                    </form>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 {{ $availableForLoans <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $availableForLoans <= 0 ? 'disabled title="Approve savings first to add funds to the group."' : '' }}>
                        Approve & Create Loan
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Rejection Form (Admin only, pending requests) -->
        @if(Auth::user()->isAdmin() && $loanRequest->status === 'pending' && !isset($approvalFormShown))
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-semibold mb-4 text-red-600">Reject Loan Request</h2>
            <form action="{{ route('loan-requests.reject', $loanRequest) }}" method="POST">
                @csrf
                
                <div class="mb-6">
                    <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">
                        Rejection Reason (Optional)
                    </label>
                    <textarea id="admin_notes" name="admin_notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500 @error('admin_notes') border-red-500 @enderror"
                              placeholder="Optional reason for rejection...">{{ old('admin_notes') }}</textarea>
                    @error('admin_notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            onclick="return confirm('Are you sure you want to reject this loan request?')">
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
