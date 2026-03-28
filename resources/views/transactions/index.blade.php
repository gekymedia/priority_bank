@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
<div class="container mx-auto px-4 py-8">
    @php
        // Used to give each calendar day a consistent, subtle highlight (low opacity).
        // Income/expense text colors remain, but background becomes date-based.
        $dayBgPalette = [
            'rgba(59, 130, 246, 0.06)',  // blue-500
            'rgba(16, 185, 129, 0.06)',  // emerald-500
            'rgba(245, 158, 11, 0.06)',  // amber-500
            'rgba(239, 68, 68, 0.06)',   // red-500
            'rgba(168, 85, 247, 0.06)',  // purple-500
            'rgba(34, 197, 94, 0.06)',   // green-500
        ];
        $dayBgPaletteCount = count($dayBgPalette);
    @endphp
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

    <!-- Search + filters: GET form (search runs on server across all transactions, debounced while typing) -->
    <form method="GET" action="{{ route('transactions.index') }}" id="transactionsFilterForm" class="space-y-4 mb-6">
        @if(request()->filled('user_id'))
            <input type="hidden" name="user_id" value="{{ request('user_id') }}">
        @endif

        <div class="bg-white rounded-lg shadow-md p-4">
            <label for="transactionSearch" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input type="search"
                   name="search"
                   id="transactionSearch"
                   value="{{ request('search') }}"
                   placeholder="Date, user, email, type, description, category, amount, notes, source system…"
                   autocomplete="off"
                   class="block w-full rounded-md border border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-3 py-2">
            <p class="mt-2 text-xs text-gray-500">
                Filters <strong>all</strong> transactions in the database (not only the current page). Results update shortly after you stop typing.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                    <select id="type" name="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="">All Types</option>
                        <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                    </select>
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">From</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-3 py-2">
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">To</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm px-3 py-2">
                </div>

                <div class="flex items-end gap-2 flex-wrap">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md h-10">
                        Apply filters
                    </button>
                    @if(request()->anyFilled(['type', 'start_date', 'end_date', 'user_id', 'search']))
                        <a href="{{ request()->filled('user_id') ? route('transactions.index', ['user_id' => request('user_id')]) : route('transactions.index') }}" class="text-gray-500 hover:text-gray-700 h-10 flex items-center px-2">
                            Clear all
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    @if(request()->filled('search'))
        <p class="text-sm text-gray-600 mb-3">
            <strong>{{ $transactions->total() }}</strong> transaction(s) match your search.
        </p>
    @endif

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
                    @forelse($transactions as $transaction)
                    @php
                        $dayKey = $transaction->date?->format('Y-m-d') ?? ($transaction->created_at?->format('Y-m-d') ?? null);
                        $dayIdx = 0;
                        if ($dayKey !== null) {
                            $dayIdx = crc32($dayKey) % $dayBgPaletteCount;
                            if ($dayIdx < 0) {
                                $dayIdx += $dayBgPaletteCount;
                            }
                        }
                        $dayBg = $dayBgPalette[$dayIdx] ?? $dayBgPalette[0];
                    @endphp
                    <tr class="transaction-row" style="background-color: {{ $dayBg }};">
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
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction->type === 'income' ? 'text-green-800' : 'text-red-800' }}"
                                style="background-color: {{ $dayBg }};"
                            >
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                            @php
                                $detailDesc = \App\Models\Transaction::textForDisplay($transaction->description ?? '');
                                $detailNotes = \App\Models\Transaction::textForDisplay($transaction->depositSaving?->notes ?? $transaction->notes ?? '');
                            @endphp
                            <span class="inline-block align-middle">{{ Str::limit($detailDesc !== '' ? $detailDesc : '—', 50) }}</span>
                            <button type="button"
                                class="transaction-view-more ml-1 inline-flex align-middle text-blue-600 hover:text-blue-800 focus:outline-none"
                                title="View details"
                                data-description-b64="{{ base64_encode($detailDesc) }}"
                                data-notes-b64="{{ base64_encode($detailNotes) }}"
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
                    @empty
                    <tr>
                        <td colspan="{{ Auth::user()->isAdmin() ? 7 : 6 }}" class="px-6 py-12 text-center text-gray-500">
                            @if(request()->filled('search') || request()->anyFilled(['type', 'start_date', 'end_date']))
                                No transactions match your current filters.
                            @else
                                No transactions yet.
                            @endif
                        </td>
                    </tr>
                    @endforelse
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
        <div class="relative bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] flex flex-col p-6 z-10">
            <div class="flex justify-between items-start mb-4 shrink-0">
                <h3 class="text-lg font-semibold text-gray-900">Transaction details</h3>
                <button type="button" id="transactionDetailModalClose" class="text-gray-400 hover:text-gray-600" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4 text-sm overflow-y-auto min-h-0 pr-1">
                <div class="rounded-lg border border-indigo-100 bg-indigo-50/90 p-3 shadow-sm">
                    <p class="font-semibold text-indigo-800 text-xs uppercase tracking-wide">Description</p>
                    <p id="transactionDetailDescription" class="mt-2 text-indigo-950 whitespace-pre-wrap font-mono text-xs sm:text-sm leading-relaxed">—</p>
                </div>
                <div id="transactionDetailNotesWrap" class="hidden rounded-lg border border-amber-100 bg-amber-50/90 p-3 shadow-sm">
                    <p class="font-semibold text-amber-900 text-xs uppercase tracking-wide">Notes</p>
                    <p id="transactionDetailNotes" class="mt-2 text-amber-950 whitespace-pre-wrap font-mono text-xs sm:text-sm leading-relaxed">—</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Server-side search: debounce GET submit so typing searches the full database (all pages)
        var form = document.getElementById('transactionsFilterForm');
        var searchInput = document.getElementById('transactionSearch');
        var searchDebounceTimer = null;
        if (form && searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(function() {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                }, 450);
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

        function decodeTxDetailB64(b64) {
            if (!b64 || String(b64).trim() === '') {
                return '';
            }
            try {
                var binary = atob(b64);
                var bytes = new Uint8Array(binary.length);
                for (var i = 0; i < binary.length; i++) {
                    bytes[i] = binary.charCodeAt(i);
                }
                return new TextDecoder('utf-8').decode(bytes);
            } catch (e) {
                return '';
            }
        }

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
                openDetailModal(
                    decodeTxDetailB64(this.getAttribute('data-description-b64')),
                    decodeTxDetailB64(this.getAttribute('data-notes-b64'))
                );
            });
        });
        if (detailBackdrop) detailBackdrop.addEventListener('click', closeDetailModal);
        if (detailClose) detailClose.addEventListener('click', closeDetailModal);
    });
</script>
@endpush
@endsection