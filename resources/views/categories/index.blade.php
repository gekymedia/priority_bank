@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Categories</h1>
        <button id="createCategoryBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">
            <i class="fas fa-plus mr-2"></i>Create Category
        </button>
    </div>

    <!-- Categories Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($categories as $category)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $category->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $category->type === 'income' ? 'bg-green-100 text-green-800' : 
                                   ($category->type === 'expense' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ ucfirst($category->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->type }}')" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create/Edit Category Modal -->
<div id="categoryModal" class="transaction-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; border-radius: var(--border-radius-lg); width: 90%; max-width: 500px; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div class="modal-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--dark);" id="modalTitle">Create Category</h3>
            <button id="closeCategoryModalBtn" style="background: none; border: none; font-size: 24px; color: var(--gray-500); cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: var(--transition);">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
            <form id="categoryForm">
                @csrf
                <input type="hidden" id="category_id" name="category_id">
                
                <div class="mb-4">
                    <label for="category_name" class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                    <input type="text" name="name" id="category_name" required
                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm py-2 px-3">
                    <span class="error-message text-red-600 text-sm mt-1" id="error_name" style="display: none;"></span>
                </div>

                <div class="mb-4">
                    <label for="category_type" class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
                    <select name="type" id="category_type" required
                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                        <option value="both" selected>Both</option>
                    </select>
                    <span class="error-message text-red-600 text-sm mt-1" id="error_type" style="display: none;"></span>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" id="cancelCategoryBtn" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" id="submitCategoryBtn" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md">
                        <span id="submitCategoryBtnText">Save Category</span>
                        <span id="submitCategoryBtnLoader" style="display: none;"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const categoryModal = document.getElementById('categoryModal');
    const createCategoryBtn = document.getElementById('createCategoryBtn');
    const closeCategoryModalBtn = document.getElementById('closeCategoryModalBtn');
    const cancelCategoryBtn = document.getElementById('cancelCategoryBtn');
    const categoryForm = document.getElementById('categoryForm');
    const modalTitle = document.getElementById('modalTitle');

    function openCategoryModal() {
        categoryModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        categoryForm.reset();
        document.getElementById('category_id').value = '';
        modalTitle.textContent = 'Create Category';
        document.querySelectorAll('.error-message').forEach(el => {
            el.style.display = 'none';
        });
    }

    function closeCategoryModal() {
        categoryModal.style.display = 'none';
        document.body.style.overflow = '';
        categoryForm.reset();
        document.getElementById('category_id').value = '';
    }

    function editCategory(id, name, type) {
        document.getElementById('category_id').value = id;
        document.getElementById('category_name').value = name;
        document.getElementById('category_type').value = type;
        modalTitle.textContent = 'Edit Category';
        categoryModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    if (createCategoryBtn) {
        createCategoryBtn.addEventListener('click', openCategoryModal);
    }

    if (closeCategoryModalBtn) {
        closeCategoryModalBtn.addEventListener('click', closeCategoryModal);
    }

    if (cancelCategoryBtn) {
        cancelCategoryBtn.addEventListener('click', closeCategoryModal);
    }

    if (categoryModal) {
        categoryModal.addEventListener('click', function(e) {
            if (e.target === categoryModal) {
                closeCategoryModal();
            }
        });
    }

    if (categoryForm) {
        categoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            document.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
            });
            
            const submitBtn = document.getElementById('submitCategoryBtn');
            const submitBtnText = document.getElementById('submitCategoryBtnText');
            const submitBtnLoader = document.getElementById('submitCategoryBtnLoader');
            const categoryId = document.getElementById('category_id').value;
            
            submitBtn.disabled = true;
            submitBtnText.style.display = 'none';
            submitBtnLoader.style.display = 'inline';
            
            const formData = new FormData(categoryForm);
            const url = categoryId ? `/categories/${categoryId}` : '{{ route("categories.store") }}';
            const method = categoryId ? 'PUT' : 'POST';
            
            formData.append('_method', method === 'PUT' ? 'PUT' : 'POST');
            
            fetch(url, {
                method: method === 'PUT' ? 'POST' : 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw { status: response.status, data: data };
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                if (error.data && error.data.errors) {
                    Object.keys(error.data.errors).forEach(field => {
                        const errorEl = document.getElementById('error_' + field);
                        if (errorEl) {
                            errorEl.textContent = error.data.errors[field][0];
                            errorEl.style.display = 'block';
                        }
                    });
                }
                
                submitBtn.disabled = false;
                submitBtnText.style.display = 'inline';
                submitBtnLoader.style.display = 'none';
            });
        });
    }
</script>
@endpush
@endsection
