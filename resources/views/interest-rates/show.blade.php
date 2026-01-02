@extends('layouts.app')

@section('title', 'Interest Rate Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Interest Rate Details</h1>
                <p class="text-gray-600 mt-1">{{ $interestRate->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('interest-rates.edit', $interestRate->id) }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md">
                    Edit Rate
                </a>
                <a href="{{ route('interest-rates.index') }}" class="text-gray-600 hover:text-gray-900">
                    ← Back to Rates
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Rate Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Rate Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rate Name</label>
                            <p class="mt-1 text-lg font-medium">{{ $interestRate->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Interest Rate</label>
                            <p class="mt-1 text-2xl font-bold text-green-600">{{ $interestRate->rate_percentage }}%</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                    @if($interestRate->type === 'loan_interest') bg-blue-100 text-blue-800
                                    @else bg-green-100 text-green-800 @endif">
                                    {{ $interestRate->type === 'loan_interest' ? 'Loan Interest' : 'Savings Interest' }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                    @if($interestRate->is_active && $interestRate->isCurrentlyActive()) bg-green-100 text-green-800
                                    @elseif($interestRate->is_active) bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @if($interestRate->is_active && $interestRate->isCurrentlyActive())
                                        Active
                                    @elseif($interestRate->is_active)
                                        Scheduled
                                    @else
                                        Inactive
                                    @endif
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Effective From</label>
                            <p class="mt-1 text-lg">{{ $interestRate->effective_from->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Effective To</label>
                            <p class="mt-1 text-lg">
                                @if($interestRate->effective_to)
                                    {{ $interestRate->effective_to->format('F d, Y') }}
                                @else
                                    <span class="text-gray-500">Ongoing</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($interestRate->description)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <p class="mt-1 text-gray-700">{{ $interestRate->description }}</p>
                    </div>
                    @endif
                </div>

                <!-- Usage Statistics -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Usage Statistics</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $interestRate->loans()->count() }}</div>
                            <div class="text-sm text-gray-600">Loans Using This Rate</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">GHS {{ number_format($interestRate->loans()->sum('amount'), 2) }}</div>
                            <div class="text-sm text-gray-600">Total Loan Amount</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-600">GHS {{ number_format($interestRate->loans()->sum('remaining_balance'), 2) }}</div>
                            <div class="text-sm text-gray-600">Outstanding Balance</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Loans Using This Rate -->
                @if($interestRate->loans()->count() > 0)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Recent Loans Using This Rate</h2>
                    <div class="space-y-4">
                        @foreach($interestRate->loans()->latest()->take(5)->get() as $loan)
                        <div class="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-medium">{{ $loan->borrower_name }}</div>
                                <div class="text-sm text-gray-600">Loan #{{ $loan->id }} • {{ $loan->disbursement_date?->format('M d, Y') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-green-600">GHS {{ number_format($loan->amount, 2) }}</div>
                                <div class="text-sm text-gray-600">{{ ucfirst($loan->status) }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($interestRate->loans()->count() > 5)
                    <div class="mt-4 text-center">
                        <a href="{{ route('loans.index') }}?interest_rate={{ $interestRate->id }}" class="text-blue-500 hover:text-blue-700 text-sm">
                            View all {{ $interestRate->loans()->count() }} loans
                        </a>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('interest-rates.edit', $interestRate->id) }}" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md text-center block">
                            Edit Rate
                        </a>
                        @if($interestRate->is_active)
                            <button onclick="toggleStatus(false)" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md">
                                Deactivate Rate
                            </button>
                        @else
                            <button onclick="toggleStatus(true)" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                                Activate Rate
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Rate Summary -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Rate Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Rate ID</span>
                            <span class="font-medium">#{{ $interestRate->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Created</span>
                            <span class="font-medium">{{ $interestRate->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Last Updated</span>
                            <span class="font-medium">{{ $interestRate->updated_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Currently Active</span>
                            <span class="font-medium {{ $interestRate->isCurrentlyActive() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $interestRate->isCurrentlyActive() ? 'Yes' : 'No' }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($interestRate->loans()->count() === 0)
                <!-- Danger Zone -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4 text-red-600">Danger Zone</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Deleting this rate will permanently remove it from the system. This action cannot be undone.
                    </p>
                    <form action="{{ route('interest-rates.destroy', $interestRate->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this interest rate? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">
                            Delete Rate
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->isAdmin())
<script>
function toggleStatus(newStatus) {
    const action = newStatus ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this interest rate?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('interest-rates.update', $interestRate->id) }}`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'PUT';

        const status = document.createElement('input');
        status.type = 'hidden';
        status.name = 'is_active';
        status.value = newStatus ? '1' : '0';

        // Include current values to avoid validation errors
        const name = document.createElement('input');
        name.type = 'hidden';
        name.name = 'name';
        name.value = '{{ $interestRate->name }}';

        const rate = document.createElement('input');
        rate.type = 'hidden';
        rate.name = 'rate_percentage';
        rate.value = '{{ $interestRate->rate_percentage }}';

        const type = document.createElement('input');
        type.type = 'hidden';
        type.name = 'type';
        type.value = '{{ $interestRate->type }}';

        const effectiveFrom = document.createElement('input');
        effectiveFrom.type = 'hidden';
        effectiveFrom.name = 'effective_from';
        effectiveFrom.value = '{{ $interestRate->effective_from->format('Y-m-d') }}';

        form.appendChild(csrf);
        form.appendChild(method);
        form.appendChild(status);
        form.appendChild(name);
        form.appendChild(rate);
        form.appendChild(type);
        form.appendChild(effectiveFrom);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endif
@endsection
