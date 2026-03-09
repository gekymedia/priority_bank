@extends('layouts.app')

@section('title', 'Queue Jobs')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold">Queue Jobs Management</h1>
            <p class="text-gray-500 mt-1">View pending and failed jobs, retry or clear them</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if(isset($queueDriver) && $queueDriver !== 'database')
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-800 p-4 mb-4 rounded">
            <strong>Queue Driver:</strong> {{ $queueDriver }} ({{ $queueConnection ?? 'unknown' }})
            <br><small>Queue jobs management currently supports database queues only. Redis and other drivers are not fully supported for viewing pending jobs.</small>
        </div>
    @endif

    @if(isset($debugInfo) && auth()->user()->isAdmin())
        <div class="bg-gray-100 rounded-lg p-4 mb-4 text-sm">
            <h6 class="font-semibold mb-2">Debug Information</h6>
            <strong>Queue Driver:</strong> {{ $debugInfo['queue_driver'] ?? 'unknown' }}<br>
            <strong>Queue Connection:</strong> {{ $debugInfo['queue_connection'] ?? 'unknown' }}<br>
            <strong>Jobs Table:</strong> {{ $debugInfo['jobs_table_name'] ?? 'jobs' }}
            ({{ ($debugInfo['jobs_table_exists'] ?? false) ? 'EXISTS' : 'DOES NOT EXIST' }})<br>
            <strong>Failed Jobs Table:</strong> {{ ($debugInfo['failed_jobs_table_exists'] ?? false) ? 'EXISTS' : 'DOES NOT EXIST' }}<br>
            @if(isset($debugInfo['direct_pending_count']))
                <strong>Direct DB (Pending):</strong> {{ $debugInfo['direct_pending_count'] }}<br>
            @endif
            @if(isset($debugInfo['direct_failed_count']))
                <strong>Direct DB (Failed):</strong> {{ $debugInfo['direct_failed_count'] }}<br>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6 text-center border-l-4 border-blue-500">
            <h5 class="text-gray-500 text-sm uppercase">Pending Jobs</h5>
            <p class="text-2xl font-bold text-blue-600">{{ $pendingCount ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 text-center border-l-4 border-red-500">
            <h5 class="text-gray-500 text-sm uppercase">Failed Jobs</h5>
            <p class="text-2xl font-bold text-red-600">{{ $failedCount ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h6 class="font-semibold mb-2">Quick Actions</h6>
            <div class="flex flex-wrap gap-2">
                <form action="{{ route('admin.queue-jobs.clear') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="type" value="pending">
                    <button type="submit" class="px-3 py-1.5 bg-amber-500 text-white rounded text-sm hover:bg-amber-600"
                            onclick="return confirm('Clear all pending jobs? This cannot be undone.')">
                        Clear Pending
                    </button>
                </form>
                @if(($failedCount ?? 0) > 0)
                    <form action="{{ route('admin.queue-jobs.retry-all') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded text-sm hover:bg-green-700">Retry All Failed</button>
                    </form>
                    <form action="{{ route('admin.queue-jobs.clear') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="type" value="failed">
                        <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded text-sm hover:bg-red-700"
                                onclick="return confirm('Clear all failed jobs? This cannot be undone.')">
                            Clear Failed
                        </button>
                    </form>
                    <form action="{{ route('admin.queue-jobs.clear') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="type" value="all">
                        <button type="submit" class="px-3 py-1.5 bg-gray-700 text-white rounded text-sm hover:bg-gray-800"
                                onclick="return confirm('Clear ALL pending and failed jobs? This cannot be undone.')">
                            Clear All
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Pending Jobs --}}
    <div class="bg-white rounded-lg shadow-md mb-6 overflow-hidden">
        <div class="bg-indigo-600 text-white px-4 py-3 flex justify-between items-center">
            <h5 class="font-semibold">Pending Jobs ({{ $pendingCount ?? 0 }})</h5>
            <button type="button" class="text-white hover:bg-indigo-500 px-2 py-1 rounded text-sm" onclick="location.reload()">Refresh</button>
        </div>
        <div class="p-4">
            @if(($pendingJobs ?? collect())->isEmpty())
                <p class="text-gray-500">No pending jobs in the queue.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Job Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Queue</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Attempts</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Available At</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($pendingJobs ?? [] as $job)
                                <tr>
                                    <td class="px-4 py-2"><code class="text-sm">{{ $job['id'] }}</code></td>
                                    <td class="px-4 py-2">
                                        <span class="font-medium">{{ $job['displayName'] }}</span>
                                        @if(!empty($job['job']))
                                            <br><small class="text-gray-500">{{ class_basename($job['job']) }}</small>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2"><span class="px-2 py-0.5 bg-gray-200 rounded text-xs">{{ $job['queue'] }}</span></td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-0.5 rounded text-xs {{ $job['attempts'] > 0 ? 'bg-amber-200' : 'bg-blue-100' }}">{{ $job['attempts'] }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $job['available_at'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $job['created_at'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Failed Jobs --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-red-600 text-white px-4 py-3">
            <h5 class="font-semibold">Failed Jobs ({{ $failedCount ?? 0 }})</h5>
        </div>
        <div class="p-4">
            @if(($failedJobs ?? collect())->isEmpty())
                <p class="text-gray-600">No failed jobs. All good!</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Job Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Queue</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Exception</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Failed At</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($failedJobs ?? [] as $job)
                                <tr>
                                    <td class="px-4 py-2"><code class="text-sm">{{ $job['id'] }}</code></td>
                                    <td class="px-4 py-2">
                                        <span class="font-medium">{{ $job['displayName'] }}</span>
                                        <br><small class="text-gray-500">{{ $job['connection'] }}</small>
                                    </td>
                                    <td class="px-4 py-2"><span class="px-2 py-0.5 bg-gray-200 rounded text-xs">{{ $job['queue'] }}</span></td>
                                    <td class="px-4 py-2 text-red-600 text-sm max-w-md truncate" title="{{ $job['exception'] }}">{{ $job['exception'] }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $job['failed_at'] }}</td>
                                    <td class="px-4 py-2">
                                        <div class="flex gap-1">
                                            <form action="{{ route('admin.queue-jobs.retry', $job['id']) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700" title="Retry">Retry</button>
                                            </form>
                                            <form action="{{ route('admin.queue-jobs.delete', $job['id']) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700"
                                                        onclick="return confirm('Delete this failed job?')" title="Delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
