@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Fund Sources</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($fundSources as $fund)
        <div class="bg-white rounded-lg shadow-lg overflow-hidden border-l-4 
            @if($fund['color'] === 'blue') border-blue-500
            @elseif($fund['color'] === 'green') border-green-500
            @elseif($fund['color'] === 'purple') border-purple-500
            @else border-gray-500
            @endif">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full 
                            @if($fund['color'] === 'blue') bg-blue-100 text-blue-600
                            @elseif($fund['color'] === 'green') bg-green-100 text-green-600
                            @elseif($fund['color'] === 'purple') bg-purple-100 text-purple-600
                            @else bg-gray-100 text-gray-600
                            @endif">
                            <i class="fas fa-{{ $fund['icon'] }} text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $fund['name'] }}</h3>
                            <p class="text-sm text-gray-500">{{ $fund['description'] }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="text-3xl font-bold 
                        @if($fund['balance'] >= 0) text-green-600 @else text-red-600 @endif">
                        GHS {{ number_format($fund['balance'], 2) }}
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Current Balance</p>
                </div>

                @if($fund['type'] === 'api' && isset($fund['total_income']))
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total Income:</span>
                        <span class="font-semibold text-green-600">GHS {{ number_format($fund['total_income'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm mt-2">
                        <span class="text-gray-600">Total Expenses:</span>
                        <span class="font-semibold text-red-600">GHS {{ number_format($fund['total_expenses'], 2) }}</span>
                    </div>
                    @if($fund['system_id'])
                    <div class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-link mr-1"></i> Connected: {{ $fund['system_name'] }}
                    </div>
                    @if($fund['balance'] > 0)
                    <div class="mt-3">
                        <button type="button" 
                                class="transfer-fund-btn w-full bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded text-sm"
                                data-system-id="{{ $fund['system_id'] }}"
                                data-system-name="{{ $fund['system_name'] }}"
                                data-available-balance="{{ $fund['balance'] }}">
                            <i class="fas fa-exchange-alt mr-1"></i> Transfer to Friends Savings Fund
                        </button>
                    </div>
                    @endif
                    @else
                    <div class="mt-2 text-xs text-yellow-600">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Not Connected
                    </div>
                    @endif
                </div>
                @endif

                @if($fund['type'] === 'savings')
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i> Available for borrowing
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Fund Sources Overview</h2>
        <div class="space-y-4">
            @foreach($fundSources as $fund)
            <div class="flex items-center justify-between p-4 
                @if($fund['type'] === 'savings') bg-blue-50 rounded-lg
                @elseif($fund['color'] === 'green') bg-green-50 rounded-lg
                @elseif($fund['color'] === 'purple') bg-purple-50 rounded-lg
                @elseif($fund['color'] === 'indigo') bg-indigo-50 rounded-lg
                @elseif($fund['color'] === 'pink') bg-pink-50 rounded-lg
                @elseif($fund['color'] === 'yellow') bg-yellow-50 rounded-lg
                @elseif($fund['color'] === 'orange') bg-orange-50 rounded-lg
                @elseif($fund['color'] === 'teal') bg-teal-50 rounded-lg
                @else bg-gray-50 rounded-lg
                @endif">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $fund['name'] }}</h3>
                    <p class="text-sm text-gray-600">
                        @if($fund['type'] === 'savings')
                            Total available savings from all members that can be borrowed for projects
                        @else
                            {{ $fund['description'] }}
                        @endif
                    </p>
                    @if($fund['type'] === 'api' && isset($fund['total_income']))
                    <div class="mt-2 flex gap-4 text-xs">
                        <span class="text-gray-500">Income: <span class="font-semibold text-green-600">GHS {{ number_format($fund['total_income'], 2) }}</span></span>
                        <span class="text-gray-500">Expenses: <span class="font-semibold text-red-600">GHS {{ number_format($fund['total_expenses'], 2) }}</span></span>
                    </div>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold 
                        @if($fund['balance'] >= 0) 
                            @if($fund['type'] === 'savings') text-blue-600
                            @elseif($fund['color'] === 'green') text-green-600
                            @elseif($fund['color'] === 'purple') text-purple-600
                            @else text-gray-600
                            @endif
                        @else text-red-600 @endif">
                        GHS {{ number_format($fund['balance'], 2) }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Transfer Funds Modal -->
<div id="transferModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50" x-ignore>
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Transfer Funds</h3>
                <button type="button" id="closeTransferModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="transferForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">From Fund</label>
                    <input type="text" id="transferFromFund" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                    <input type="hidden" name="from_system_id" id="transferFromSystemId">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">To Fund</label>
                    <input type="text" value="Friends Savings Fund" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                </div>
                <div class="mb-4">
                    <label for="transferAmount" class="block text-sm font-medium text-gray-700 mb-2">Amount (GHS)</label>
                    <input type="number" step="0.01" min="0.01" name="amount" id="transferAmount" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           required>
                    <div id="transferAmountError" class="text-red-500 text-sm mt-1 hidden"></div>
                    <p class="text-xs text-gray-500 mt-1">Available: <span id="availableBalance">GHS 0.00</span></p>
                </div>
                <div class="mb-4">
                    <label for="transferNotes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea name="notes" id="transferNotes" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="e.g., Loan to friend for project"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" id="cancelTransferBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Show Transfer Modal
    $(document).on('click', '.transfer-fund-btn', function() {
        const systemId = $(this).data('system-id');
        const systemName = $(this).data('system-name');
        const availableBalance = $(this).data('available-balance');
        
        $('#transferFromFund').val(systemName);
        $('#transferFromSystemId').val(systemId);
        $('#availableBalance').text('GHS ' + parseFloat(availableBalance).toFixed(2));
        $('#transferAmount').attr('max', availableBalance);
        $('#transferAmount').val('');
        $('#transferNotes').val('');
        $('#transferAmountError').addClass('hidden');
        
        $('#transferModal').removeClass('hidden');
    });

    // Close Transfer Modal
    $('#closeTransferModal, #cancelTransferBtn').on('click', function() {
        $('#transferModal').addClass('hidden');
    });

    // Validate amount on input
    $('#transferAmount').on('input', function() {
        const amount = parseFloat($(this).val());
        const available = parseFloat($('#availableBalance').text().replace('GHS ', ''));
        
        if (amount > available) {
            $('#transferAmountError').text('Amount exceeds available balance').removeClass('hidden');
            $(this).addClass('border-red-500');
        } else {
            $('#transferAmountError').addClass('hidden');
            $(this).removeClass('border-red-500');
        }
    });

    // Submit Transfer Form
    $('#transferForm').on('submit', function(e) {
        e.preventDefault();
        
        const amount = parseFloat($('#transferAmount').val());
        const available = parseFloat($('#availableBalance').text().replace('GHS ', ''));
        
        if (amount > available) {
            $('#transferAmountError').text('Amount exceeds available balance').removeClass('hidden');
            return;
        }
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("admin.fund-sources.transfer") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Reload page to show updated balances
                window.location.reload();
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.amount) {
                        $('#transferAmountError').text(errors.amount[0]).removeClass('hidden');
                    }
                } else {
                    alert('Transfer failed: ' + (xhr.responseJSON?.message || 'An error occurred'));
                }
            }
        });
    });
});
</script>
@endpush
@endsection
