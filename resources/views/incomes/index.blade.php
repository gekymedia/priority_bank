@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Income Records</h1>
        <div class="flex gap-3">
            <button type="button" id="addIncomeCategoryBtn" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                Add Income Category
            </button>
            <button type="button" id="addIncomeBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Add New Income
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
            <tbody class="bg-white divide-y divide-gray-200" id="incomesTableBody">
                @foreach($incomes as $income)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ optional($income->category)->name ?? 'Uncategorised' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">GHS {{ number_format($income->amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $income->date?->format('M d, Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap capitalize">{{ $income->channel }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ optional($income->account)->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ \Illuminate\Support\Str::limit($income->notes, 50) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('incomes.edit', $income->id) }}" class="text-blue-500 hover:text-blue-700 mr-3">Edit</a>
                        <form action="{{ route('incomes.destroy', $income->id) }}" method="POST" class="inline">
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
        {{ $incomes->links() }}
    </div>
</div>

<!-- Add Income Category Modal -->
<div id="incomeCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50" x-ignore>
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Add Income Category</h3>
                <button type="button" id="closeIncomeCategoryModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="incomeCategoryForm">
                @csrf
                <div class="mb-4">
                    <label for="income_category_name" class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
                    <input type="text" name="name" id="income_category_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <div id="income_category_name_error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" id="cancelIncomeCategoryBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Income Modal -->
<div id="incomeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50" x-ignore>
    <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white my-10">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Add New Income</h3>
                <button type="button" id="closeIncomeModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="incomeForm">
                @csrf
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-md font-semibold">Income Details</h4>
                        <button type="button" id="addIncomeRowBtn" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                            <i class="fas fa-plus mr-1"></i> Add Row
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="incomeTable">
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
                            <tbody class="bg-white divide-y divide-gray-200" id="incomeTableBody">
                                <tr class="income-row">
                                    <td class="px-3 py-2">
                                        <select name="incomes[0][income_category_id]" class="income-category-select w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" step="0.01" name="incomes[0][amount]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="date" name="incomes[0][date]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" value="{{ now()->format('Y-m-d') }}" required>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select name="incomes[0][channel]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                                            <option value="">Select Channel</option>
                                            @foreach($channels as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <select name="incomes[0][account_id]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                                            <option value="">Select Account</option>
                                            @foreach($accounts as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" name="incomes[0][notes]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Optional">
                                    </td>
                                    <td class="px-3 py-2">
                                        <button type="button" class="remove-income-row-btn text-red-500 hover:text-red-700 hidden">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" id="cancelIncomeBtn" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
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
        
        let incomeRowCount = 1;

    // Show Income Category Modal
    $('#addIncomeCategoryBtn').on('click', function() {
        $('#incomeCategoryModal').removeClass('hidden');
        $('#income_category_name').val('');
        $('#income_category_name_error').addClass('hidden');
    });

    // Close Income Category Modal
    $('#closeIncomeCategoryModal, #cancelIncomeCategoryBtn').on('click', function() {
        $('#incomeCategoryModal').addClass('hidden');
    });

    // Submit Income Category Form
    $('#incomeCategoryForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("income-categories.store") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#incomeCategoryModal').addClass('hidden');
                    // Update categories object and dropdowns
                    categories[response.category.id] = response.category.name;
                    updateCategoryDropdowns();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.name) {
                        $('#income_category_name_error').text(errors.name[0]).removeClass('hidden');
                    }
                } else {
                    showAlert('error', 'An error occurred. Please try again.');
                }
            }
        });
    });

    function updateCategoryDropdowns() {
        // Update all category dropdowns
        $('.income-category-select').each(function() {
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

    // Show Income Modal
    $('#addIncomeBtn').on('click', function() {
        $('#incomeModal').removeClass('hidden');
        incomeRowCount = 1;
        updateIncomeRemoveButtons();
    });

    // Close Income Modal
    $('#closeIncomeModal, #cancelIncomeBtn').on('click', function() {
        $('#incomeModal').addClass('hidden');
        $('#incomeForm')[0].reset();
        // Reset to single row
        $('.income-row').not(':first').remove();
        incomeRowCount = 1;
    });

    // Add Income Row
    $('#addIncomeRowBtn').on('click', function() {
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
            <tr class="income-row">
                <td class="px-3 py-2">
                    <select name="incomes[${incomeRowCount}][income_category_id]" class="income-category-select w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                        <option value="">Select Category</option>
                        ${categoryOptions}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="number" step="0.01" name="incomes[${incomeRowCount}][amount]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                </td>
                <td class="px-3 py-2">
                    <input type="date" name="incomes[${incomeRowCount}][date]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" value="{{ now()->format('Y-m-d') }}" required>
                </td>
                <td class="px-3 py-2">
                    <select name="incomes[${incomeRowCount}][channel]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                        <option value="">Select Channel</option>
                        ${channelOptions}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <select name="incomes[${incomeRowCount}][account_id]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" required>
                        <option value="">Select Account</option>
                        ${accountOptions}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="incomes[${incomeRowCount}][notes]" class="w-full px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Optional">
                </td>
                <td class="px-3 py-2">
                    <button type="button" class="remove-income-row-btn text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#incomeTableBody').append(newRow);
        incomeRowCount++;
        updateIncomeRemoveButtons();
    });

    // Remove Income Row
    $(document).on('click', '.remove-income-row-btn', function() {
        const rowCount = $('.income-row').length;
        if (rowCount > 1) {
            $(this).closest('tr').remove();
            updateIncomeRemoveButtons();
        }
    });

    function updateIncomeRemoveButtons() {
        const rowCount = $('.income-row').length;
        $('.remove-income-row-btn').toggle(rowCount > 1);
    }

    // Submit Income Form
    $('#incomeForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ route("incomes.store") }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    $('#incomeModal').addClass('hidden');
                    $('#incomeForm')[0].reset();
                    // Reload page to show new income
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
        console.error('Error in income page initialization:', e);
    }
});

// Always set up modal handlers outside try-catch to ensure they work
$(document).ready(function() {
    // Show Income Category Modal
    $(document).off('click', '#addIncomeCategoryBtn').on('click', '#addIncomeCategoryBtn', function() {
        $('#incomeCategoryModal').removeClass('hidden');
        $('#income_category_name').val('');
        $('#income_category_name_error').addClass('hidden');
    });

    // Close Income Category Modal
    $(document).off('click', '#closeIncomeCategoryModal, #cancelIncomeCategoryBtn').on('click', '#closeIncomeCategoryModal, #cancelIncomeCategoryBtn', function() {
        $('#incomeCategoryModal').addClass('hidden');
    });

    // Show Income Modal
    $(document).off('click', '#addIncomeBtn').on('click', '#addIncomeBtn', function() {
        $('#incomeModal').removeClass('hidden');
        if (typeof incomeRowCount !== 'undefined') {
            incomeRowCount = 1;
        }
        if (typeof updateIncomeRemoveButtons === 'function') {
            updateIncomeRemoveButtons();
        }
    });

    // Close Income Modal
    $(document).off('click', '#closeIncomeModal, #cancelIncomeBtn').on('click', '#closeIncomeModal, #cancelIncomeBtn', function() {
        $('#incomeModal').addClass('hidden');
        $('#incomeForm')[0].reset();
        $('.income-row').not(':first').remove();
        if (typeof incomeRowCount !== 'undefined') {
            incomeRowCount = 1;
        }
    });
});
</script>
@endpush
@endsection
