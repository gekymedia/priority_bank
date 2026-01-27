@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Expense Records</h1>
        <div class="flex gap-3">
            <button type="button" id="addExpenseCategoryBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                Add Expenses Category
            </button>
            <button type="button" id="addExpenseBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Add New Expense
            </button>
        </div>
    </div>

    <div id="alertContainer"></div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Channel</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="expensesTableBody">
                @foreach($expenses as $expense)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ optional($expense->category)->name ?? 'Uncategorised' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">GHS {{ number_format($expense->amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $expense->date?->format('M d, Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap capitalize">{{ $expense->channel }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ optional($expense->account)->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ \Illuminate\Support\Str::limit($expense->notes, 50) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('expenses.edit', $expense->id) }}" class="text-blue-500 hover:text-blue-700 mr-3">Edit</a>
                        <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $expenses->links() }}
    </div>
</div>

<!-- Add Expense Category Modal -->
<div id="expenseCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50" x-ignore>
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Add Expense Category</h3>
                <button type="button" id="closeExpenseCategoryModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="expenseCategoryForm">
                @csrf
                <div class="mb-4">
                    <label for="category_name" class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                    <input type="text" name="name" id="category_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <div id="category_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" id="cancelExpenseCategoryBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div id="expenseModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50" x-ignore>
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white my-10">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Add New Expense</h3>
                <button type="button" id="closeExpenseModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="expenseForm">
                @csrf
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-md font-semibold">Expense Details</h4>
                        <button type="button" id="addExpenseRowBtn" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                            <i class="fas fa-plus mr-1"></i> Add Row
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="expenseTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Channel</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="expenseTableBody">
                                <tr class="expense-row">
                                    <td class="px-3 py-2">
                                        <select name="expenses[0][expense_category_id]" class="expense-category-select w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" name="expenses[0][amount]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="date" name="expenses[0][date]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" value="{{ now()->format('Y-m-d') }}" required>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select name="expenses[0][channel]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                                            <option value="">Select Channel</option>
                                            @foreach($channels as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select name="expenses[0][account_id]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                                            <option value="">Select Account</option>
                                            @foreach($accounts as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="expenses[0][notes]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Optional">
                                    </td>
                                    <td class="px-3 py-2">
                                        <button type="button" class="remove-expense-row-btn text-red-500 hover:text-red-700 hidden">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" id="cancelExpenseBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Ensure jQuery is loaded
if (typeof jQuery === 'undefined') {
    console.error('jQuery is not loaded!');
} else {
    console.log('jQuery version:', jQuery.fn.jquery);
}

$(document).ready(function() {
    try {
        // Safely initialize variables with proper defaults
        let categories = {};
        let channels = {};
        let accounts = {};
        
        try {
            const cats = @json($categories ?? []);
            const chans = @json($channels ?? []);
            const accs = @json($accounts ?? []);
            
            categories = (cats && typeof cats === 'object' && !Array.isArray(cats)) ? cats : {};
            channels = (chans && typeof chans === 'object' && !Array.isArray(chans)) ? chans : {};
            accounts = (accs && typeof accs === 'object' && !Array.isArray(accs)) ? accs : {};
        } catch(e) {
            console.error('Error initializing data:', e);
        }
        
        let expenseRowCount = 1;

    // Close Expense Category Modal
    $('#closeExpenseCategoryModal, #cancelExpenseCategoryBtn').on('click', function() {
        $('#expenseCategoryModal').addClass('hidden');
    });

    // Submit Expense Category Form
    $('#expenseCategoryForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("expense-categories.store") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#expenseCategoryModal').addClass('hidden');
                    // Update categories object and dropdowns
                    categories[response.category.id] = response.category.name;
                    updateCategoryDropdowns();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.name) {
                        $('#category_name_error').text(errors.name[0]).removeClass('hidden');
                    }
                } else {
                    showAlert('error', 'An error occurred. Please try again.');
                }
            }
        });
    });

    function updateCategoryDropdowns() {
        // Update all category dropdowns
        $('.expense-category-select').each(function() {
            const currentValue = $(this).val();
            $(this).empty().append('<option value="">Select Category</option>');
            if (categories && typeof categories === 'object' && !Array.isArray(categories)) {
                try {
                    Object.entries(categories).forEach(([id, name]) => {
                        const option = $('<option></option>').attr('value', id).text(name);
                        if (id == currentValue) {
                            option.attr('selected', 'selected');
                        }
                        $(this).append(option);
                    });
                } catch(e) {
                    console.error('Error updating category dropdowns:', e);
                }
            }
        });
    }


    // Close Expense Modal
    $('#closeExpenseModal, #cancelExpenseBtn').on('click', function() {
        $('#expenseModal').addClass('hidden');
        $('#expenseForm')[0].reset();
        // Reset to single row
        $('.expense-row').not(':first').remove();
        expenseRowCount = 1;
    });

    // Add Expense Row
    $('#addExpenseRowBtn').on('click', function() {
        // Safely build options
        const categoryOptions = (categories && typeof categories === 'object' && !Array.isArray(categories)) 
            ? Object.entries(categories).map(([id, name]) => `<option value="${id}">${name}</option>`).join('') 
            : '';
        const channelOptions = (channels && typeof channels === 'object' && !Array.isArray(channels))
            ? Object.entries(channels).map(([value, label]) => `<option value="${value}">${label}</option>`).join('')
            : '';
        const accountOptions = (accounts && typeof accounts === 'object' && !Array.isArray(accounts))
            ? Object.entries(accounts).map(([id, name]) => `<option value="${id}">${name}</option>`).join('')
            : '';
        
        const newRow = `
            <tr class="expense-row">
                <td class="px-3 py-2">
                    <select name="expenses[${expenseRowCount}][expense_category_id]" class="expense-category-select w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                        <option value="">Select Category</option>
                        ${categoryOptions}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="number" step="0.01" name="expenses[${expenseRowCount}][amount]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                </td>
                <td class="px-3 py-2">
                    <input type="date" name="expenses[${expenseRowCount}][date]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" value="{{ now()->format('Y-m-d') }}" required>
                </td>
                <td class="px-3 py-2">
                    <select name="expenses[${expenseRowCount}][channel]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                        <option value="">Select Channel</option>
                        ${channelOptions}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <select name="expenses[${expenseRowCount}][account_id]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                        <option value="">Select Account</option>
                        ${accountOptions}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="expenses[${expenseRowCount}][notes]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Optional">
                </td>
                <td class="px-3 py-2">
                    <button type="button" class="remove-expense-row-btn text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#expenseTableBody').append(newRow);
        expenseRowCount++;
        updateExpenseRemoveButtons();
    });

    // Remove Expense Row
    $(document).on('click', '.remove-expense-row-btn', function() {
        const rowCount = $('.expense-row').length;
        if (rowCount > 1) {
            $(this).closest('tr').remove();
            updateExpenseRemoveButtons();
        }
    });

    function updateExpenseRemoveButtons() {
        const rowCount = $('.expense-row').length;
        $('.remove-expense-row-btn').toggle(rowCount > 1);
    }

    // Submit Expense Form
    $('#expenseForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("expenses.store") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#expenseModal').addClass('hidden');
                    $('#expenseForm')[0].reset();
                    // Reload page to show new expense
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMsg = 'Please fix the errors: ';
                    for (let field in errors) {
                        errorMsg += errors[field][0] + ' ';
                    }
                    showAlert('error', errorMsg);
                } else {
                    showAlert('error', 'An error occurred. Please try again.');
                }
            }
        });
    });

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
        const alertHtml = `
            <div class="${alertClass} border px-4 py-3 rounded mb-4">
                ${message}
            </div>
        `;
        $('#alertContainer').html(alertHtml);
        setTimeout(function() {
            $('#alertContainer').html('');
        }, 5000);
    }
    } catch(e) {
        console.error('Error in expense page initialization:', e);
    }
});

// Always set up modal handlers outside try-catch to ensure they work
$(document).ready(function() {
    // Show Expense Category Modal
    $(document).off('click', '#addExpenseCategoryBtn').on('click', '#addExpenseCategoryBtn', function() {
        $('#expenseCategoryModal').removeClass('hidden');
        $('#category_name').val('');
        $('#category_name_error').addClass('hidden');
    });

    // Close Expense Category Modal
    $(document).off('click', '#closeExpenseCategoryModal, #cancelExpenseCategoryBtn').on('click', '#closeExpenseCategoryModal, #cancelExpenseCategoryBtn', function() {
        $('#expenseCategoryModal').addClass('hidden');
    });

    // Show Expense Modal
    $(document).off('click', '#addExpenseBtn').on('click', '#addExpenseBtn', function() {
        $('#expenseModal').removeClass('hidden');
        if (typeof expenseRowCount !== 'undefined') {
            expenseRowCount = 1;
        }
        if (typeof updateExpenseRemoveButtons === 'function') {
            updateExpenseRemoveButtons();
        }
    });

    // Close Expense Modal
    $(document).off('click', '#closeExpenseModal, #cancelExpenseBtn').on('click', '#closeExpenseModal, #cancelExpenseBtn', function() {
        $('#expenseModal').addClass('hidden');
        $('#expenseForm')[0].reset();
        $('.expense-row').not(':first').remove();
        if (typeof expenseRowCount !== 'undefined') {
            expenseRowCount = 1;
        }
    });
});
</script>
@endpush
@endsection
