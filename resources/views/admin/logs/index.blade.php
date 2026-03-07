@extends('layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold">System Logs</h1>
            <p class="text-gray-500 mt-1">Monitor application activity and debug issues</p>
        </div>
        <div class="flex flex-wrap gap-4">
            <div class="bg-white rounded-lg shadow px-4 py-3 border border-gray-200">
                <div class="text-xs text-gray-500 uppercase">Log Size</div>
                <div class="font-semibold">{{ $logStats['size'] ?? '0 MB' }}</div>
            </div>
            <div class="bg-white rounded-lg shadow px-4 py-3 border border-gray-200">
                <div class="text-xs text-gray-500 uppercase">Last Updated</div>
                <div class="font-semibold">{{ $logStats['last_modified'] ?? 'Never' }}</div>
            </div>
            <div class="bg-white rounded-lg shadow px-4 py-3 border border-gray-200">
                <div class="text-xs text-gray-500 uppercase">Log Files</div>
                <div class="font-semibold">{{ count($logFiles) }}</div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 flex-wrap">
            <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
                <input type="text" id="logSearch" placeholder="Search logs..."
                    class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm max-w-xs">
                <select id="levelFilter" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm w-auto">
                    <option value="all">All Levels</option>
                    <option value="emergency">Emergency</option>
                    <option value="alert">Alert</option>
                    <option value="critical">Critical</option>
                    <option value="error">Error</option>
                    <option value="warning">Warning</option>
                    <option value="notice">Notice</option>
                    <option value="info">Info</option>
                    <option value="debug">Debug</option>
                </select>
                <select id="logFileSelect" class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm w-auto min-w-[200px]">
                    <option value="latest">Latest Log File</option>
                    @foreach($logFilesWithSize ?? [] as $item)
                        @php
                            $isSelected = $selectedLogFile === $item['name'] || ($selectedLogFile === 'latest' && $loop->first);
                        @endphp
                        <option value="{{ $item['name'] }}" {{ $isSelected ? 'selected' : '' }}>
                            {{ $item['name'] }} ({{ $item['size'] }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" id="refreshLogs" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">
                    <i class="fas fa-sync-alt mr-1"></i> Refresh
                </button>
                <button type="button" id="clearLogs" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm">
                    <i class="fas fa-trash mr-1"></i> Clear Current
                </button>
                <button type="button" id="clearAllLogs" class="px-4 py-2 border border-red-600 text-red-600 rounded-md hover:bg-red-50 text-sm">
                    <i class="fas fa-broom mr-1"></i> Clear All
                </button>
                <a href="{{ route('admin.logs.download') }}?log_file={{ $selectedLogFile === 'latest' ? 'latest' : $selectedLogFile }}" id="downloadLogs" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm inline-flex items-center">
                    <i class="fas fa-download mr-1"></i> Download
                </a>
                <a href="{{ route('admin.logs.download-all') }}" class="px-4 py-2 border border-green-600 text-green-600 rounded-md hover:bg-green-50 text-sm inline-flex items-center">
                    <i class="fas fa-file-archive mr-1"></i> Download All
                </a>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-4 border-t border-gray-200">
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600">{{ $logStats['counts']['error'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">Errors</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-amber-600">{{ $logStats['counts']['warning'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">Warnings</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $logStats['counts']['info'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">Info</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-600">{{ $logStats['counts']['debug'] ?? 0 }}</div>
                <div class="text-sm text-gray-500">Debug</div>
            </div>
        </div>
    </div>

    <!-- Logs list -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap justify-between items-center gap-2">
            <h2 class="text-lg font-semibold">
                Recent Log Entries
                @if(isset($selectedLogFile) && $selectedLogFile !== 'latest')
                    <span class="text-gray-500 font-normal">– {{ $selectedLogFile }}</span>
                @endif
            </h2>
            <div class="flex items-center gap-3">
                <span class="text-sm text-gray-500" id="logCount">{{ count($logs) }} entries</span>
                <button type="button" id="toggleAll" class="px-3 py-1.5 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                    <i class="fas fa-expand-alt mr-1"></i> <span>Expand All</span>
                </button>
            </div>
        </div>
        <div class="overflow-auto" id="logsContainer" style="max-height: calc(100vh - 380px); min-height: 400px;">
            @if(empty($logs))
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>No log entries found in selected file</p>
                </div>
            @else
                <div class="divide-y divide-gray-200">
                    @foreach($logs as $index => $log)
                        <div class="log-entry px-6 py-4 hover:bg-gray-50" data-level="{{ $log['level'] }}" data-date="{{ $log['date'] }}">
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="log-level-badge level-{{ $log['level'] }} px-2 py-0.5 rounded text-xs font-medium text-white">
                                        {{ $log['level'] }}
                                    </span>
                                    <span class="text-sm text-gray-500 font-mono">{{ $log['timestamp'] }}</span>
                                    @if(isset($log['env']))
                                        <span class="px-2 py-0.5 rounded text-xs bg-purple-100 text-purple-800">{{ $log['env'] }}</span>
                                    @endif
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" class="copy-log-btn p-2 text-gray-500 hover:bg-gray-200 rounded" title="Copy" data-log="{{ json_encode($log) }}">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button type="button" class="expand-log-btn p-2 text-gray-500 hover:bg-gray-200 rounded" title="Expand" data-target="log-details-{{ $index }}">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="log-message text-sm text-gray-800 break-words mb-2">{{ $log['message'] }}</p>
                            <div id="log-details-{{ $index }}" class="log-details hidden mt-3 space-y-3">
                                @if(isset($log['context']) && !empty($log['context']))
                                    <div>
                                        <h6 class="text-xs font-medium text-gray-500 mb-1">Context</h6>
                                        <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto max-h-40"><code>{{ json_encode($log['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                                    </div>
                                @endif
                                @if(isset($log['stack_trace']) && !empty($log['stack_trace']))
                                    <div>
                                        <h6 class="text-xs font-medium text-gray-500 mb-1">Stack Trace</h6>
                                        <pre class="bg-gray-100 p-3 rounded text-xs overflow-auto max-h-48 whitespace-pre-wrap">{{ e($log['stack_trace']) }}</pre>
                                    </div>
                                @endif
                                @if(isset($log['extra']) && !empty($log['extra']))
                                    <div>
                                        <h6 class="text-xs font-medium text-gray-500 mb-1">Additional Info</h6>
                                        <pre class="bg-blue-50 p-3 rounded text-xs overflow-auto">{{ json_encode($log['extra'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-wrap justify-between items-center mt-2 pt-2 border-t border-gray-100 text-xs text-gray-500">
                                <div class="flex gap-3">
                                    @if(isset($log['file']))
                                        <span class="font-mono">{{ $log['file'] }}:{{ $log['line'] ?? 'N/A' }}</span>
                                    @endif
                                    @if(isset($log['user_id']))
                                        <span>User ID: {{ $log['user_id'] }}</span>
                                    @endif
                                    @if(isset($log['ip']))
                                        <span>IP: {{ $log['ip'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        @if(isset($pagination) && $pagination['total'] > 0)
            <div class="px-6 py-4 border-t border-gray-200 flex flex-wrap justify-between items-center gap-2">
                <div class="text-sm text-gray-500">
                    Showing {{ $pagination['from'] }} to {{ $pagination['to'] }} of {{ $pagination['total'] }} entries
                </div>
                <nav class="flex gap-1">
                    @if($pagination['current_page'] > 1)
                        <a href="?page={{ $pagination['current_page'] - 1 }}&log_file={{ $selectedLogFile }}" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">Previous</a>
                    @endif
                    @for($i = 1; $i <= $pagination['last_page']; $i++)
                        @if($i == $pagination['current_page'])
                            <span class="px-3 py-1 bg-indigo-600 text-white rounded">{{ $i }}</span>
                        @else
                            <a href="?page={{ $i }}&log_file={{ $selectedLogFile }}" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">{{ $i }}</a>
                        @endif
                    @endfor
                    @if($pagination['current_page'] < $pagination['last_page'])
                        <a href="?page={{ $pagination['current_page'] + 1 }}&log_file={{ $selectedLogFile }}" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">Next</a>
                    @endif
                </nav>
            </div>
        @endif
    </div>
</div>

<!-- Clear current modal -->
<div id="clearLogsModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('clearLogsModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-2">Clear Log File</h3>
            <p class="text-gray-600 mb-4" id="clearCurrentFileText">Clear current log file? This cannot be undone.</p>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('clearLogsModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button type="button" id="confirmClear" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Clear Log File</button>
            </div>
        </div>
    </div>
</div>

<!-- Clear all modal -->
<div id="clearAllLogsModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-hidden="true">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('clearAllLogsModal').classList.add('hidden')"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold mb-2">Clear All Log Files</h3>
            <p class="text-gray-600 mb-4">Clear all {{ count($logFiles) }} log files? This cannot be undone.</p>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('clearAllLogsModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                <button type="button" id="confirmClearAll" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Clear All Logs</button>
            </div>
        </div>
    </div>
</div>

<style>
.log-level-badge.level-emergency { background-color: #dc2626; }
.log-level-badge.level-alert { background-color: #ea580c; }
.log-level-badge.level-critical { background-color: #dc2626; }
.log-level-badge.level-error { background-color: #ef4444; }
.log-level-badge.level-warning { background-color: #f59e0b; }
.log-level-badge.level-notice { background-color: #3b82f6; }
.log-level-badge.level-info { background-color: #10b981; }
.log-level-badge.level-debug { background-color: #6b7280; }
#logsContainer::-webkit-scrollbar { width: 6px; }
#logsContainer::-webkit-scrollbar-track { background: #f1f5f9; }
#logsContainer::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('logSearch');
    const levelFilter = document.getElementById('levelFilter');
    const logFileSelect = document.getElementById('logFileSelect');
    const downloadLogs = document.getElementById('downloadLogs');

    function filterLogs() {
        const searchTerm = (searchInput && searchInput.value || '').toLowerCase();
        const level = levelFilter ? levelFilter.value : 'all';
        document.querySelectorAll('.log-entry').forEach(function(entry) {
            const entryLevel = entry.getAttribute('data-level');
            const msgEl = entry.querySelector('.log-message');
            const message = (msgEl && msgEl.textContent || '').toLowerCase();
            const matchSearch = !searchTerm || message.includes(searchTerm);
            const matchLevel = level === 'all' || entryLevel === level;
            entry.style.display = (matchSearch && matchLevel) ? '' : 'none';
        });
        const visible = document.querySelectorAll('.log-entry[style=""]').length;
        const countEl = document.getElementById('logCount');
        if (countEl) countEl.textContent = visible + ' entries';
    }

    if (searchInput) searchInput.addEventListener('input', filterLogs);
    if (levelFilter) levelFilter.addEventListener('change', filterLogs);

    if (logFileSelect) {
        logFileSelect.addEventListener('change', function() {
            const v = this.value;
            window.location.href = '{{ route("admin.logs.index") }}?log_file=' + encodeURIComponent(v);
        });
    }

    if (downloadLogs && logFileSelect) {
        function updateDownloadHref() {
            downloadLogs.href = '{{ route("admin.logs.download") }}?log_file=' + encodeURIComponent(logFileSelect.value);
        }
        logFileSelect.addEventListener('change', updateDownloadHref);
    }

    document.querySelectorAll('.expand-log-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-target');
            const el = document.getElementById(id);
            const icon = this.querySelector('i');
            if (!el) return;
            el.classList.toggle('hidden');
            if (icon) {
                icon.classList.toggle('fa-chevron-down');
                icon.classList.toggle('fa-chevron-up');
            }
        });
    });

    const toggleAll = document.getElementById('toggleAll');
    if (toggleAll) {
        toggleAll.addEventListener('click', function() {
            const details = document.querySelectorAll('.log-details');
            const isHidden = details.length && details[0].classList.contains('hidden');
            details.forEach(function(d) { d.classList.toggle('hidden', !isHidden); });
            document.querySelectorAll('.expand-log-btn i').forEach(function(i) {
                i.classList.toggle('fa-chevron-down', isHidden);
                i.classList.toggle('fa-chevron-up', !isHidden);
            });
            toggleAll.querySelector('span').textContent = isHidden ? 'Collapse All' : 'Expand All';
        });
    }

    document.querySelectorAll('.copy-log-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            try {
                const log = JSON.parse(this.getAttribute('data-log'));
                const text = '[' + log.timestamp + '] ' + (log.level || '').toUpperCase() + ': ' + log.message + '\n\n' +
                    (log.context ? 'Context: ' + JSON.stringify(log.context, null, 2) + '\n\n' : '') +
                    (log.stack_trace ? 'Stack Trace:\n' + log.stack_trace + '\n\n' : '') +
                    (log.extra && Object.keys(log.extra).length ? 'Extra: ' + JSON.stringify(log.extra, null, 2) : '');
                navigator.clipboard.writeText(text).then(function() {
                    showToast('Log entry copied to clipboard', 'success');
                });
            } catch (e) {
                showToast('Failed to copy', 'error');
            }
        });
    });

    document.getElementById('refreshLogs').addEventListener('click', function() {
        window.location.reload();
    });

    document.getElementById('clearLogs').addEventListener('click', function() {
        var file = logFileSelect ? logFileSelect.value : 'latest';
        document.getElementById('clearCurrentFileText').textContent = 'Clear ' + (file === 'latest' ? 'latest log file' : file) + '? This cannot be undone.';
        document.getElementById('clearLogsModal').classList.remove('hidden');
    });

    document.getElementById('confirmClear').addEventListener('click', function() {
        var file = logFileSelect ? logFileSelect.value : 'latest';
        fetch('{{ route("admin.logs.clear") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ log_file: file })
        }).then(function(r) { return r.json(); }).then(function(data) {
            document.getElementById('clearLogsModal').classList.add('hidden');
            showToast(data.success ? 'Log file cleared' : (data.message || 'Failed'), data.success ? 'success' : 'error');
            if (data.success) setTimeout(function() { location.reload(); }, 800);
        }).catch(function() {
            showToast('Failed to clear log file', 'error');
        });
    });

    document.getElementById('clearAllLogs').addEventListener('click', function() {
        document.getElementById('clearAllLogsModal').classList.remove('hidden');
    });

    document.getElementById('confirmClearAll').addEventListener('click', function() {
        fetch('{{ route("admin.logs.clear-all") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' }
        }).then(function(r) { return r.json(); }).then(function(data) {
            document.getElementById('clearAllLogsModal').classList.add('hidden');
            showToast(data.success ? data.message : (data.message || 'Failed'), data.success ? 'success' : 'error');
            if (data.success) setTimeout(function() { location.reload(); }, 800);
        }).catch(function() {
            showToast('Failed to clear all logs', 'error');
        });
    });

    function showToast(message, type) {
        type = type || 'info';
        var bg = type === 'success' ? 'bg-green-600' : (type === 'error' ? 'bg-red-600' : 'bg-indigo-600');
        var el = document.createElement('div');
        el.className = 'fixed top-4 right-4 z-[100] px-4 py-3 rounded-lg shadow-lg text-white ' + bg;
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(function() { el.remove(); }, 3000);
    }

    filterLogs();
});
</script>
@endsection
