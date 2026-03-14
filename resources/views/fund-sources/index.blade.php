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
                
                @if($fund['type'] === 'api' && empty($fund['has_linked_user']))
                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <p class="text-amber-800 text-sm font-medium">
                        <i class="fas fa-exclamation-circle mr-1"></i> This source does not have an account linked yet.
                    </p>
                    <p class="text-amber-700 text-xs mt-1">Link a user account in API Keys / Source Keys so balances and transactions apply to this source.</p>
                </div>
                @else
                <div class="mt-4">
                    <div class="text-3xl font-bold 
                        @if($fund['balance'] >= 0) text-green-600 @else text-red-600 @endif">
                        @if($fund['type'] === 'api' && $fund['balance'] === null)
                            —
                        @else
                            GHS {{ number_format($fund['balance'], 2) }}
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Current Balance</p>
                </div>

                @if($fund['type'] === 'api' && !empty($fund['has_linked_user']))
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Savings:</span>
                        <span class="font-semibold text-green-600">GHS {{ number_format($fund['savings_balance'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm mt-2">
                        <span class="text-gray-600">Loans:</span>
                        <span class="font-semibold text-red-600">GHS {{ number_format($fund['loan_balance'], 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm mt-2">
                        <span class="text-gray-600">Net:</span>
                        <span class="font-semibold {{ ($fund['net_balance'] ?? 0) >= 0 ? 'text-gray-900' : 'text-red-600' }}">GHS {{ number_format($fund['net_balance'], 2) }}</span>
                    </div>
                    @if(!empty($fund['linked_user_name']))
                    <div class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-user mr-1"></i> Account: {{ $fund['linked_user_name'] }}
                        @if(!empty($fund['linked_user_id']))
                            <a href="{{ route('admin.users.show', $fund['linked_user_id']) }}" class="text-indigo-600 hover:underline ml-1">View</a>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
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
                    @if($fund['type'] === 'api' && !empty($fund['has_linked_user']))
                    <div class="mt-2 flex gap-4 text-xs">
                        <span class="text-gray-500">Savings: <span class="font-semibold text-green-600">GHS {{ number_format($fund['savings_balance'], 2) }}</span></span>
                        <span class="text-gray-500">Loans: <span class="font-semibold text-red-600">GHS {{ number_format($fund['loan_balance'], 2) }}</span></span>
                        <span class="text-gray-500">Net: <span class="font-semibold">GHS {{ number_format($fund['net_balance'], 2) }}</span></span>
                    </div>
                    @elseif($fund['type'] === 'api' && empty($fund['has_linked_user']))
                    <div class="mt-2 text-xs text-amber-600">
                        <i class="fas fa-exclamation-circle mr-1"></i> This source does not have an account linked yet.
                    </div>
                    @endif
                </div>
                <div class="text-right">
                    @if($fund['type'] === 'api' && empty($fund['has_linked_user']))
                    <div class="text-sm font-medium text-amber-600">
                        No account linked
                    </div>
                    @else
                    <div class="text-2xl font-bold 
                        @if($fund['balance'] >= 0) 
                            @if($fund['type'] === 'savings') text-blue-600
                            @elseif($fund['color'] === 'green') text-green-600
                            @elseif($fund['color'] === 'purple') text-purple-600
                            @else text-gray-600
                            @endif
                        @else text-red-600 @endif">
                        @if($fund['type'] === 'api' && $fund['balance'] === null)
                            —
                        @else
                            GHS {{ number_format($fund['balance'], 2) }}
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
