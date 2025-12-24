@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Budgets for {{ \Carbon\Carbon::parse($month.'-01')->format('F Y') }}</h1>
        <a href="{{ route('budgets.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Add New Budget
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Month Selector -->
    <form method="GET" action="{{ route('budgets.index') }}" class="mb-4 flex items-center space-x-2">
        <label for="month" class="text-sm font-medium">Select Month:</label>
        <input type="month" name="month" id="month" value="{{ $month }}" class="border px-2 py-1 rounded">
        <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-sm">Go</button>
    </form>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budgeted Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Spent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($budgets as $budget)
                @php
                    $spent = \App\Models\Expense::where('user_id', auth()->id())
                        ->where('expense_category_id', $budget->expense_category_id)
                        ->whereYear('date', \Carbon\Carbon::parse($budget->month . '-01')->year)
                        ->whereMonth('date', \Carbon\Carbon::parse($budget->month . '-01')->month)
                        ->sum('amount');
                    $remaining = $budget->amount - $spent;
                    $percentage = $budget->amount > 0 ? min(100, round(($spent / $budget->amount) * 100)) : 0;
                @endphp
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ optional($budget->category)->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">GHS {{ number_format($budget->amount, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">GHS {{ number_format($spent, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">GHS {{ number_format($remaining, 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                            <div class="h-4 rounded-full {{ $percentage >= 100 ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-xs">{{ $percentage }}%</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('budgets.edit', $budget->id) }}" class="text-blue-500 hover:text-blue-700 mr-3">Edit</a>
                        <form action="{{ route('budgets.destroy', $budget->id) }}" method="POST" class="inline">
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
</div>
@endsection