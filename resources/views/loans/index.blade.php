@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Loan Records</h1>
        <a href="{{ route('loans.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Add New Loan
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Borrower</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Given On</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Returned</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($loans as $loan)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $loan->borrower_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">GHS {{ number_format($loan->amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusClasses = [
                                'borrowed' => 'bg-yellow-100 text-yellow-800',
                                'returned' => 'bg-green-100 text-green-800',
                                'lost' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $statusClasses[$loan->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($loan->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ optional($loan->date_given)->format('M d, Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ optional($loan->expected_return_date)->format('M d, Y') ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">GHS {{ number_format($loan->returned_amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap space-x-2">
                        <a href="{{ route('loans.edit', $loan->id) }}" class="text-blue-500 hover:text-blue-700">Edit</a>
                        <form action="{{ route('loans.destroy', $loan->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                        @if($loan->status === 'borrowed')
                            <!-- Mark returned form -->
                            <form action="{{ route('loans.return', $loan->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="returned_amount" value="{{ $loan->amount }}">
                                <input type="hidden" name="date" value="{{ now()->format('Y-m-d') }}">
                                <input type="hidden" name="account_id" value="{{ $loan->account_id }}">
                                <input type="hidden" name="channel" value="{{ $loan->channel }}">
                                <button type="submit" class="text-green-500 hover:text-green-700" onclick="return confirm('Mark this loan as returned?')">Mark Returned</button>
                            </form>
                            <!-- Mark lost form -->
                            <form action="{{ route('loans.lost', $loan->id) }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="date" value="{{ now()->format('Y-m-d') }}">
                                <input type="hidden" name="account_id" value="{{ $loan->account_id }}">
                                <input type="hidden" name="channel" value="{{ $loan->channel }}">
                                <button type="submit" class="text-yellow-700 hover:text-yellow-800" onclick="return confirm('Mark this loan as lost?')">Mark Lost</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $loans->links() }}
    </div>
</div>
@endsection