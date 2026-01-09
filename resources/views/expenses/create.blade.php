@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Add New Expense</h1>

    <form action="{{ route('expenses.store') }}" method="POST" id="expenseForm">
        @csrf

        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="mb-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold">Expense Records</h2>
                <button type="button" id="addRowBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Row
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="expenseTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount (GHS)</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">External System</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="expenseTableBody">
                        <!-- First row -->
                        <tr class="expense-row">
                            <td class="px-3 py-2">
                                <select name="expenses[0][expense_category_id]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm @error('expenses.0.expense_category_id') border-red-500 @enderror" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $id => $name)
                                        <option value="{{ $id }}" {{ old('expenses.0.expense_category_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.01" name="expenses[0][amount]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm @error('expenses.0.amount') border-red-500 @enderror" value="{{ old('expenses.0.amount') }}" required>
                            </td>
                            <td class="px-3 py-2">
                                <input type="date" name="expenses[0][date]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm @error('expenses.0.date') border-red-500 @enderror" value="{{ old('expenses.0.date', now()->format('Y-m-d')) }}" required>
                            </td>
                            <td class="px-3 py-2">
                                <select name="expenses[0][channel]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm @error('expenses.0.channel') border-red-500 @enderror" required>
                                    <option value="">Select Channel</option>
                                    @foreach($channels as $value => $label)
                                        <option value="{{ $value }}" {{ old('expenses.0.channel') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <select name="expenses[0][account_id]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm @error('expenses.0.account_id') border-red-500 @enderror" required>
                                    <option value="">Select Account</option>
                                    @foreach($accounts as $id => $name)
                                        <option value="{{ $id }}" {{ old('expenses.0.account_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="expenses[0][notes]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm @error('expenses.0.notes') border-red-500 @enderror" value="{{ old('expenses.0.notes') }}" placeholder="Optional">
                            </td>
                            <td class="px-3 py-2">
                                <select name="expenses[0][external_system_id]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm @error('expenses.0.external_system_id') border-red-500 @enderror">
                                    <option value="">None</option>
                                    @foreach($systems as $id => $name)
                                        <option value="{{ $id }}" {{ old('expenses.0.external_system_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2">
                                <button type="button" class="remove-row-btn text-red-500 hover:text-red-700 hidden">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('expenses.index') }}" class="text-gray-600 hover:text-gray-800">Back to Expenses</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Save All Expense Records
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let rowCount = {{ is_array(old('expenses')) ? count(old('expenses')) : 1 }};
    const categories = @json($categories);
    const channels = @json($channels);
    const accounts = @json($accounts);
    const systems = @json($systems);

    // Add new row
    $('#addRowBtn').on('click', function() {
        const newRow = `
            <tr class="expense-row">
                <td class="px-3 py-2">
                    <select name="expenses[${rowCount}][expense_category_id]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                        <option value="">Select Category</option>
                        ${Object.entries(categories).map(([id, name]) => `<option value="${id}">${name}</option>`).join('')}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="number" step="0.01" name="expenses[${rowCount}][amount]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                </td>
                <td class="px-3 py-2">
                    <input type="date" name="expenses[${rowCount}][date]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" value="{{ now()->format('Y-m-d') }}" required>
                </td>
                <td class="px-3 py-2">
                    <select name="expenses[${rowCount}][channel]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                        <option value="">Select Channel</option>
                        ${Object.entries(channels).map(([value, label]) => `<option value="${value}">${label}</option>`).join('')}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <select name="expenses[${rowCount}][account_id]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                        <option value="">Select Account</option>
                        ${Object.entries(accounts).map(([id, name]) => `<option value="${id}">${name}</option>`).join('')}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="expenses[${rowCount}][notes]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Optional">
                </td>
                <td class="px-3 py-2">
                    <select name="expenses[${rowCount}][external_system_id]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                        <option value="">None</option>
                        ${Object.entries(systems).map(([id, name]) => `<option value="${id}">${name}</option>`).join('')}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <button type="button" class="remove-row-btn text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#expenseTableBody').append(newRow);
        rowCount++;
        updateRemoveButtons();
    });

    // Remove row
    $(document).on('click', '.remove-row-btn', function() {
        const rowCount = $('.expense-row').length;
        if (rowCount > 1) {
            $(this).closest('tr').remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const rowCount = $('.expense-row').length;
        $('.remove-row-btn').toggle(rowCount > 1);
    }

    // Initialize remove buttons visibility
    updateRemoveButtons();
});
</script>
@endpush
@endsection
