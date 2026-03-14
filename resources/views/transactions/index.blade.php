@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">
            @if(Auth::user()->isAdmin() && request('user_id') == (string) auth()->id())
                CEO Personal {{ request('type') === 'income' ? 'Income' : (request('type') === 'expense' ? 'Expenditure' : 'Transactions') }}
            @else
                {{ Auth::user()->isAdmin() ? 'All Transactions' : 'My Transactions' }}
            @endif
        </h1>
        @if(Auth::user()->isAdmin())
            <button id="newTransactionBtnFromPage" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Add New Transaction
            </button>
        @endif
    </div>

    <!-- Search: filter on page as you type -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-4">
        <label for="transactionSearch" class="block text-sm font-medium text-gray-700 mb-2">Search (filters all columns as you type)</label>
        <input type="text" id="transactionSearch" placeholder="Search date, user, type, description, category, amount…" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
    </div>

    <!-- Transaction Filters -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('transactions.index') }}">
            @if(request()->filled('user_id'))
                <input type="hidden" name="user_id" value="{{ request('user_id') }}">
            @endif
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Type Filter -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                    <select id="type" name="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">All Types</option>
                        <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">From</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">To</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <!-- Submit Button -->
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md h-10">
                        Filter
                    </button>
                    @if(request()->has('type') || request()->has('start_date') || request()->has('end_date') || request()->has('user_id'))
                        <a href="{{ route('transactions.index') }}" class="ml-2 text-gray-500 hover:text-gray-700 h-10 flex items-center">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        @if(Auth::user()->isAdmin())
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category/Directorate</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($transactions as $transaction)
                    <tr class="transaction-row" data-search="{{ strtolower(implode(' ', array_filter([
                        $transaction->date->format('M d Y'),
                        $transaction->user?->name ?? '',
                        $transaction->type,
                        $transaction->description ?? '',
                        $transaction->category ?? '',
                        (string) $transaction->amount,
                        $transaction->externalSystem?->name ?? ''
                    ]))) }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction->date->format('M d, Y') }}
                        </td>
                        @if(Auth::user()->isAdmin())
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($transaction->user)
                                <a href="{{ route('admin.users.statement', $transaction->user) }}" class="font-medium text-blue-600 hover:text-blue-800 hover:underline focus:outline-none focus:underline">
                                    {{ $transaction->user->name }}
                                </a>
                            @else
                                <span class="text-gray-500">N/A</span>
                            @endif
                            @if($transaction->externalSystem)
                                <br><span class="text-xs text-gray-500">Source: {{ $transaction->externalSystem->name }}</span>
                            @endif
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                            <span class="inline-block align-middle">{{ Str::limit($transaction->description ?? '—', 50) }}</span>
                            <button type="button"
                                class="transaction-view-more ml-1 inline-flex align-middle text-blue-600 hover:text-blue-800 focus:outline-none"
                                title="View details"
                                data-description="{{ e($transaction->description ?? '') }}"
                                data-notes="{{ e($transaction->depositSaving?->notes ?? $transaction->notes ?? '') }}"
                                aria-label="View description and notes">
                                <i class="fas fa-info-circle text-sm"></i>
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction->category }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->type === 'income' ? '+' : '-' }}GHS {{ number_format($transaction->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('transactions.edit', $transaction->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                            <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this transaction?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal: Description & notes detail -->
<div id="transactionDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true" role="dialog">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" id="transactionDetailModalBackdrop"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 z-10">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Transaction details</h3>
                <button type="button" id="transactionDetailModalClose" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="font-medium text-gray-600">Description</p>
                    <p id="transactionDetailDescription" class="mt-1 text-gray-900 whitespace-pre-wrap">—</p>
                </div>
                <div id="transactionDetailNotesWrap" class="hidden">
                    <p class="font-medium text-gray-600">Note</p>
                    <p id="transactionDetailNotes" class="mt-1 text-gray-900 whitespace-pre-wrap">—</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Live search: filter table rows by all columns as user types
        var searchInput = document.getElementById('transactionSearch');
        var rows = document.querySelectorAll('.transaction-row');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var q = (this.value || '').toLowerCase().trim();
                rows.forEach(function(tr) {
                    var text = (tr.getAttribute('data-search') || '').toLowerCase();
                    tr.style.display = q === '' || text.indexOf(q) !== -1 ? '' : 'none';
                });
            });
        }

        @if(Auth::user()->isAdmin())
        // Open transaction modal from page button
        const newTransactionBtnFromPage = document.getElementById('newTransactionBtnFromPage');
        const transactionModal = document.getElementById('transactionModal');
        if (newTransactionBtnFromPage && transactionModal) {
            newTransactionBtnFromPage.addEventListener('click', function() {
                transactionModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                setTimeout(() => {
                    const firstInput = document.getElementById('modal_external_system_id');
                    if (firstInput) firstInput.focus();
                }, 100);
            });
        }
        @endif

        // View more: description & notes modal (all users)
        const detailModal = document.getElementById('transactionDetailModal');
        const detailDesc = document.getElementById('transactionDetailDescription');
        const detailNotes = document.getElementById('transactionDetailNotes');
        const detailNotesWrap = document.getElementById('transactionDetailNotesWrap');
        const detailBackdrop = document.getElementById('transactionDetailModalBackdrop');
        const detailClose = document.getElementById('transactionDetailModalClose');

        function openDetailModal(description, notes) {
            detailDesc.textContent = description || '—';
            if (notes && notes.trim() !== '') {
                detailNotes.textContent = notes;
                detailNotesWrap.classList.remove('hidden');
            } else {
                detailNotesWrap.classList.add('hidden');
            }
            detailModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDetailModal() {
            detailModal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('.transaction-view-more').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openDetailModal(this.getAttribute('data-description') || '', this.getAttribute('data-notes') || '');
            });
        });
        if (detailBackdrop) detailBackdrop.addEventListener('click', closeDetailModal);
        if (detailClose) detailClose.addEventListener('click', closeDetailModal);
    });
</script>
@endpush
@endsection