@extends('layouts.app')
@section('title', 'Insights & Recommendations')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            Insights & Recommendations
        </h1>
        <a href="{{ route('admin.ai-insights.index', ['refresh' => 1]) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center">
            <i class="fas fa-sync-alt mr-2"></i>
            Refresh insights
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <p class="text-gray-500 font-medium">Total Income (30 days)</p>
            <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($totalIncome, 2) }}</h2>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-red-500">
            <p class="text-gray-500 font-medium">Total Expenses (30 days)</p>
            <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($totalExpenses, 2) }}</h2>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <p class="text-gray-500 font-medium">Active Loans</p>
            <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($activeLoans, 2) }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $loansCount }} outstanding</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <p class="text-gray-500 font-medium">Net Balance</p>
            <h2 class="text-2xl font-bold mt-2">GHS {{ number_format($netBalance, 2) }}</h2>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <p class="text-gray-500 font-medium">Group Fund (available for loans)</p>
            <h2 class="text-xl font-bold mt-2">GHS {{ number_format($groupFund->available_for_loans, 2) }}</h2>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
            <p class="text-gray-500 font-medium">Pending Loan Requests</p>
            <h2 class="text-xl font-bold mt-2">{{ $pendingLoanRequests }}</h2>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-teal-500">
            <p class="text-gray-500 font-medium">Pending Savings (pre-approval)</p>
            <h2 class="text-xl font-bold mt-2">{{ $pendingSavingsCount }}</h2>
        </div>
    </div>

    <!-- AI Insights -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200 bg-blue-50">
            <h3 class="text-lg font-semibold flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                </svg>
                AI Financial Insights & Recommendations
            </h3>
            <p class="text-sm text-gray-600 mt-1">Based on the last 30 days. Use “Refresh insights” to regenerate (cached for 6 hours).</p>
        </div>
        <div class="p-6">
            <div class="prose max-w-none">
                {!! $aiInsightsHtml !!}
            </div>
        </div>
    </div>
</div>
@endsection
