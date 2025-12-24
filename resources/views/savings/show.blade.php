@extends('layouts.app')

@section('title', 'Savings Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold">Savings Deposit Details</h1>
                <p class="text-gray-600 mt-1">Deposit made on {{ $saving->deposit_date->format('F d, Y') }}</p>
            </div>
            <div class="flex space-x-3">
                @can('update', $saving)
                    <a href="{{ route('savings.edit', $saving->id) }}" class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md">
                        Edit Deposit
                    </a>
                @endcan
                <a href="{{ route('savings.index') }}" class="text-gray-600 hover:text-gray-900">
                    ← Back to Savings
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Deposit Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Deposit Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deposit Amount</label>
                            <p class="mt-1 text-2xl font-bold text-green-600">GHS {{ number_format($saving->amount, 2) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deposit Date</label>
                            <p class="mt-1 text-lg">{{ $saving->deposit_date->format('F d, Y') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <p class="mt-1">
                                <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                                    @if($saving->status === 'available') bg-green-100 text-green-800
                                    @elseif($saving->status === 'locked') bg-yellow-100 text-yellow-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($saving->status) }}
                                </span>
                            </p>
                        </div>
                        @if(Auth::user()->isAdmin())
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Member</label>
                            <p class="mt-1 text-lg">{{ $saving->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $saving->user->email }}</p>
                        </div>
                        @endif
                    </div>

                    @if($saving->notes)
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <p class="mt-1 text-gray-700">{{ $saving->notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Status Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Status Information</h2>
                    @if($saving->status === 'available')
                        <div class="bg-green-50 border-l-4 border-green-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">
                                        This deposit is <strong>available</strong> and can be used for lending to group members who need loans.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($saving->status === 'locked')
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        This deposit is <strong>locked</strong> and temporarily unavailable for lending.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 border-l-4 border-gray-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-700">
                                        This deposit has been <strong>withdrawn</strong> and is no longer available.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                @can('update', $saving)
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <a href="{{ route('savings.edit', $saving->id) }}" class="w-full bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-md text-center block">
                            Edit Deposit
                        </a>
                        @if($saving->status === 'available')
                            <button onclick="changeStatus('locked')" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md">
                                Lock Deposit
                            </button>
                        @elseif($saving->status === 'locked')
                            <button onclick="changeStatus('available')" class="w-full bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                                Make Available
                            </button>
                        @endif
                    </div>
                </div>
                @endcan

                <!-- Deposit Summary -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Deposit Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Deposit ID</span>
                            <span class="font-medium">#{{ $saving->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Created</span>
                            <span class="font-medium">{{ $saving->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Last Updated</span>
                            <span class="font-medium">{{ $saving->updated_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>

                @can('delete', $saving)
                <!-- Danger Zone -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4 text-red-600">Danger Zone</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Deleting this deposit will permanently remove it from the system and affect group fund calculations.
                    </p>
                    <form action="{{ route('savings.destroy', $saving->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this savings deposit? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">
                            Delete Deposit
                        </button>
                    </form>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->isAdmin())
<script>
function changeStatus(newStatus) {
    if (confirm(`Are you sure you want to change the status to "${newStatus}"?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ route('savings.update', $saving->id) }}`;

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
        status.name = 'status';
        status.value = newStatus;

        // Include current values to avoid validation errors
        const amount = document.createElement('input');
        amount.type = 'hidden';
        amount.name = 'amount';
        amount.value = '{{ $saving->amount }}';

        const depositDate = document.createElement('input');
        depositDate.type = 'hidden';
        depositDate.name = 'deposit_date';
        depositDate.value = '{{ $saving->deposit_date->format('Y-m-d') }}';

        form.appendChild(csrf);
        form.appendChild(method);
        form.appendChild(status);
        form.appendChild(amount);
        form.appendChild(depositDate);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endif
@endsection

