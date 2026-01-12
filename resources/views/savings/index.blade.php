@extends('layouts.app')

@section('title', 'Savings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Savings</h1>
            <p class="text-gray-600 mt-1">Manage your savings deposits and withdrawals</p>
        </div>
        @if(!Auth::user()->isAdmin())
            <div class="flex gap-3">
                <button onclick="document.getElementById('depositModal').classList.remove('hidden')" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Deposit Money
                </button>
                <button onclick="document.getElementById('withdrawModal').classList.remove('hidden')" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                    <i class="fas fa-minus mr-2"></i>
                    Withdraw Money
                </button>
            </div>
        @else
            <a href="{{ route('savings.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                Add New Savings
            </a>
        @endif
    </div>

    <!-- Savings Summary -->
    @if(Auth::user()->isAdmin())
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Total Group Savings</p>
                        <h2 class="text-2xl font-bold mt-2 text-green-600">GHS {{ number_format(\App\Models\Saving::where('status', 'available')->sum('amount'), 2) }}</h2>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Available for lending</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Active Contributors</p>
                        <h2 class="text-2xl font-bold mt-2 text-blue-600">{{ \App\Models\Saving::distinct('user_id')->count('user_id') }}</h2>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Members with savings</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Average Deposit</p>
                        <h2 class="text-2xl font-bold mt-2 text-purple-600">GHS {{ number_format(\App\Models\Saving::avg('amount') ?? 0, 2) }}</h2>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Per deposit amount</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Your Total Savings</p>
                        <h2 class="text-2xl font-bold mt-2 text-green-600">GHS {{ number_format(Auth::user()->savings_balance, 2) }}</h2>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Available for lending</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 font-medium">Your Deposits</p>
                        <h2 class="text-2xl font-bold mt-2 text-blue-600">{{ $savings->count() }}</h2>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Total deposits made</p>
            </div>
        </div>
    @endif

    <!-- Savings Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        @if(Auth::user()->isAdmin())
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        @endif
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approval Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($savings as $saving)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $saving->deposit_date->format('M d, Y') }}
                        </td>
                        @if(Auth::user()->isAdmin())
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $saving->user->name }}
                        </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $saving->status === 'withdrawn' ? 'text-red-600' : 'text-green-600' }} font-medium">
                            {{ $saving->status === 'withdrawn' ? '-' : '+' }}GHS {{ number_format($saving->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $saving->reference ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($saving->payment_method === 'direct')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">Direct Deposit</span>
                            @elseif($saving->payment_method === 'paystack')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-purple-100 text-purple-800">Paystack</span>
                            @elseif($saving->payment_method === 'hubtel')
                                <span class="px-2 py-1 text-xs font-medium rounded bg-blue-100 text-blue-800">Hubtel</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-800">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($saving->approval_status === 'pending')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @elseif($saving->approval_status === 'approved')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Approved
                                </span>
                            @elseif($saving->approval_status === 'rejected')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst($saving->approval_status ?? 'N/A') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                @if($saving->status === 'available') bg-green-100 text-green-800
                                @elseif($saving->status === 'locked') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($saving->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $saving->notes ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @can('view', $saving)
                                <a href="{{ route('savings.show', $saving->id) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            @endcan
                            @if(Auth::user()->isAdmin() && $saving->approval_status === 'pending')
                                <form action="{{ route('savings.approve', $saving->id) }}" method="POST" class="inline mr-2">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('Approve this deposit?')">Approve</button>
                                </form>
                                <form action="{{ route('savings.reject', $saving->id) }}" method="POST" class="inline mr-2">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Reject this deposit?')">Reject</button>
                                </form>
                            @endif
                            @can('update', $saving)
                                <a href="{{ route('savings.edit', $saving->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            @endcan
                            @can('delete', $saving)
                                <form action="{{ route('savings.destroy', $saving->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this savings deposit?')">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ Auth::user()->isAdmin() ? '9' : '8' }}" class="px-6 py-4 text-center text-gray-500">
                            No savings deposits found.
                            @if(Auth::user()->isAdmin())
                                <br><a href="{{ route('savings.create') }}" class="text-blue-500 hover:text-blue-700">Add the first savings deposit</a>
                            @else
                                <br><a href="{{ route('savings.create') }}" class="text-blue-500 hover:text-blue-700">Make your first deposit</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        @if($savings->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $savings->links() }}
        </div>
        @endif
    </div>

    @if(!Auth::user()->isAdmin())
    <!-- Deposit Modal -->
    <div id="depositModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Deposit Money</h3>
                    <button onclick="document.getElementById('depositModal').classList.add('hidden')" 
                            class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('savings.store') }}" method="POST" id="depositForm">
                    @csrf
                    <div class="mb-4">
                        <label for="deposit_amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (GHS) *</label>
                        <input type="number" name="amount" id="deposit_amount" step="0.01" min="0.01" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label for="deposit_reference" class="block text-sm font-medium text-gray-700 mb-2">Reference (Optional)</label>
                        <input type="text" name="reference" id="deposit_reference" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               placeholder="Transaction ID, Receipt Number">
                    </div>
                    <div class="mb-4">
                        <label for="deposit_date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                        <input type="date" name="deposit_date" id="deposit_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label for="deposit_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea name="notes" id="deposit_notes" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                    </div>
                    
                    <!-- Payment Method Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                        <div class="space-y-2">
                            <!-- Direct Deposit Option -->
                            <label class="flex items-start p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="payment_method" value="direct" checked class="mt-1 mr-3" required>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">Direct Deposit (Bank/MoMo)</div>
                                    <div class="text-sm text-gray-500">Send money directly to bank account or MoMo. Admin will verify and approve.</div>
                                </div>
                            </label>
                            
                            <!-- Online Payment Options -->
                            @if($isOnlinePaymentAvailable)
                                @if($activeGateway === 'hubtel' || $activeGateway === 'paystack')
                                    <label class="flex items-start p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                        <input type="radio" name="payment_method" value="{{ $activeGateway }}" class="mt-1 mr-3" required>
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">
                                                Online Payment ({{ strtoupper($activeGateway) }})
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Pay instantly using {{ $activeGateway === 'hubtel' ? 'Hubtel wallet or card' : 'card, mobile money, or bank transfer' }}. Instant approval.
                                            </div>
                                        </div>
                                    </label>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>Note:</strong> Direct deposits require admin verification. Online payments are processed immediately.
                        </p>
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('depositModal').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Proceed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div id="withdrawModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Withdraw Money</h3>
                    <button onclick="document.getElementById('withdrawModal').classList.add('hidden')" 
                            class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-600">Available Balance:</p>
                    <p class="text-2xl font-bold text-blue-600">GHS {{ number_format(Auth::user()->savings_balance, 2) }}</p>
                </div>
                <form action="{{ route('savings.withdraw') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="withdraw_amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (GHS) *</label>
                        <input type="number" name="amount" id="withdraw_amount" step="0.01" min="0.01" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="mt-1 text-xs text-gray-500" id="overdraft-warning" style="display: none;">
                            <span class="text-red-600 font-semibold">⚠️ Insufficient balance. Excess will be created as Loan Overdraft.</span>
                        </p>
                    </div>
                    <div class="mb-4">
                        <label for="withdraw_reference" class="block text-sm font-medium text-gray-700 mb-2">Reference (Optional)</label>
                        <input type="text" name="reference" id="withdraw_reference" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Transaction ID, Receipt Number">
                    </div>
                    <div class="mb-4">
                        <label for="withdraw_date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                        <input type="date" name="withdraw_date" id="withdraw_date" value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label for="withdraw_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea name="notes" id="withdraw_notes" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('withdrawModal').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Withdraw
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show overdraft warning if withdrawal amount exceeds balance
        document.getElementById('withdraw_amount')?.addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            const balance = {{ Auth::user()->savings_balance }};
            const warning = document.getElementById('overdraft-warning');
            
            if (amount > balance) {
                warning.style.display = 'block';
            } else {
                warning.style.display = 'none';
            }
        });
    </script>
    @endif
</div>
@endsection

