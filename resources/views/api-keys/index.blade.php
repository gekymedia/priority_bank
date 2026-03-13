@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Source Keys Management</h1>
        <button onclick="document.getElementById('createTokenModal').classList.remove('hidden')" 
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Create New Source Key
        </button>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('token'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            <p class="font-bold">Your new API key (copy this - it won't be shown again):</p>
            <div class="mt-2 flex items-center gap-2">
                <input type="text" id="new-api-token-value" readonly
                       value="{{ session('token') }}"
                       class="bg-yellow-200 px-2 py-1 rounded flex-1 font-mono border-0 text-yellow-900 w-full">
                <button type="button" id="copy-api-token-btn"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded flex items-center gap-1 shrink-0">
                    <i class="fas fa-copy" id="copy-api-token-icon"></i>
                    <span id="copy-api-token-label">Copy</span>
                </button>
            </div>
        </div>
        <script>
        (function() {
            var btn = document.getElementById('copy-api-token-btn');
            var input = document.getElementById('new-api-token-value');
            var icon = document.getElementById('copy-api-token-icon');
            var label = document.getElementById('copy-api-token-label');
            if (!btn || !input) return;
            btn.addEventListener('click', function() {
                var token = input.value;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(token).then(function() {
                        label.textContent = 'Copied!';
                        icon.className = 'fas fa-check';
                        setTimeout(function() { label.textContent = 'Copy'; icon.className = 'fas fa-copy'; }, 2000);
                    }).catch(function() {
                        fallbackCopy(token);
                    });
                } else {
                    fallbackCopy(token);
                }
            });
            function fallbackCopy(text) {
                input.select();
                input.setSelectionRange(0, 99999);
                try {
                    document.execCommand('copy');
                    label.textContent = 'Copied!';
                    icon.className = 'fas fa-check';
                    setTimeout(function() { label.textContent = 'Copy'; icon.className = 'fas fa-copy'; }, 2000);
                } catch (e) {
                    label.textContent = 'Select & copy';
                }
            }
        })();
        </script>
    @endif

    <!-- All Sources List -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">All Sources</h2>
            <button onclick="openSourceModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Add New Source
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone / Account Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protected</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Callback URL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($systems as $system)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $system->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $system->system_id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($system->user_id)
                                <code class="text-sm bg-gray-100 px-2 py-0.5 rounded" title="Use this as PBG_CUG_SYSTEM_USER_ID or PBG_AGENCY_SYSTEM_USER_ID in CUG .env">{{ $system->user_id }}</code>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-700">{{ $system->user?->phone ?? $system->account_number ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst($system->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($system->active_status)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($system->is_protected)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-lock mr-1"></i>Protected
                                </span>
                            @else
                                <span class="text-sm text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500">
                                @if($system->callback_url)
                                    <a href="{{ $system->callback_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 truncate block max-w-xs">
                                        {{ $system->callback_url }}
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500 max-w-xs truncate">
                                {{ $system->description ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex flex-wrap items-center gap-2">
                                @if(!$system->user_id)
                                    <form action="{{ route('admin.sources.create-user', $system) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Create a system user account and link it to this source?');">
                                        @csrf
                                        <button type="submit" class="text-indigo-600 hover:text-indigo-900 whitespace-nowrap" title="Create user account and link to this source">
                                            <i class="fas fa-user-plus mr-1"></i>Create user & link
                                        </button>
                                    </form>
                                @endif
                                {{-- Generate API Key button --}}
                                <button onclick="generateKeyForSource('{{ $system->system_id }}', '{{ $system->name }}', '{{ $system->callback_url ?? '' }}')" 
                                        class="text-green-600 hover:text-green-900" title="Generate API Key">
                                    <i class="fas fa-key"></i>
                                </button>
                                @if(!$system->is_protected)
                                    <button onclick="editSource({{ $system->id }}, '{{ addslashes(e($system->system_id)) }}', '{{ addslashes(e($system->name)) }}', '{{ $system->type }}', '{{ addslashes(e($system->callback_url ?? '')) }}', '{{ addslashes(e($system->api_base_url ?? '')) }}', {{ $system->active_status ? 'true' : 'false' }}, '{{ addslashes(e($system->description ?? '')) }}', '{{ addslashes(e($system->account_number ?? '')) }}')" 
                                            class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.sources.destroy', $system) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this source?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400" title="Protected sources cannot be edited or deleted">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                            No sources found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- API Keys Section -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">API Keys</h2>
                <p class="text-sm text-gray-500 mt-1">Bearer tokens for authenticating API requests</p>
            </div>
            <button onclick="document.getElementById('createTokenModal').classList.remove('hidden')" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
                <i class="fas fa-key mr-2"></i>
                Generate API Key
            </button>
        </div>
        @if($tokens->isEmpty())
            <div class="px-6 py-8 text-center">
                <div class="mb-4">
                    <i class="fas fa-key text-gray-300 text-5xl"></i>
                </div>
                <p class="text-gray-600 mb-4">You don't have any API keys yet.</p>
                <p class="text-sm text-gray-500 mb-4">Create an API key to connect your business systems to Priority Bank API.</p>
                <button onclick="document.getElementById('createTokenModal').classList.remove('hidden')" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg inline-flex items-center transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Generate Your First API Key
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name, System & Permissions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage & Expiration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tokens as $token)
                        @php
                            // Find system linked to this token via metadata
                            $linkedSystem = null;
                            foreach($systems as $system) {
                                $metadata = $system->metadata ?? [];
                                if (isset($metadata['api_token_id']) && $metadata['api_token_id'] == $token->id) {
                                    $linkedSystem = $system;
                                    break;
                                }
                            }
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $token->name }}</div>
                                <div class="text-xs text-gray-500">ID: #{{ $token->id }}</div>
                                @if($linkedSystem)
                                    <div class="text-xs text-green-600 mt-1">
                                        System: {{ $linkedSystem->name }} ({{ $linkedSystem->system_id }})
                                    </div>
                                    @if($linkedSystem->callback_url)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Callback: {{ $linkedSystem->callback_url }}
                                        </div>
                                    @endif
                                @endif
                                @if($token->abilities)
                                    @php
                                        $abilities = is_array($token->abilities) 
                                            ? $token->abilities 
                                            : (is_string($token->abilities) 
                                                ? json_decode($token->abilities, true) ?? [] 
                                                : []);
                                    @endphp
                                    @if(!empty($abilities))
                                        <div class="text-xs text-blue-600 mt-1">Permissions: {{ implode(', ', $abilities) }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $token->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $token->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $token->last_used_at ? $token->last_used_at->format('M d, Y H:i') : 'Never' }}</div>
                                @if($token->expires_at)
                                    <div class="text-xs {{ $token->expires_at->isPast() ? 'text-red-600' : ($token->expires_at->isFuture() && $token->expires_at->diffInDays(now()) < 7 ? 'text-yellow-600' : 'text-gray-400') }}">
                                        Expires: {{ $token->expires_at->format('M d, Y') }}
                                    </div>
                                @else
                                    <div class="text-xs text-gray-400">No expiration</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <form action="{{ route('api-keys.destroy', $token->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" 
                                            onclick="return confirm('Are you sure you want to delete this API key?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">
        <a href="{{ route('api-keys.documentation') }}" 
           class="text-blue-500 hover:text-blue-700 underline">
            View API Documentation →
        </a>
    </div>
</div>

<!-- Create Token Modal -->
<div id="createTokenModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Create New Source Key</h3>
                <button onclick="document.getElementById('createTokenModal').classList.add('hidden')" 
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('api-keys.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Key Name</label>
                    <input type="text" name="name" id="name" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                           placeholder="e.g., Production API, Development API"
                           required>
                    <p class="mt-1 text-xs text-gray-500">Give your API key a descriptive name for easy identification.</p>
                </div>
                <div class="mb-4">
                    <label for="system_id" class="block text-sm font-medium text-gray-700 mb-2">Source (Optional)</label>
                    <select name="system_id" id="system_id" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            onchange="toggleCallbackUrl(this.value)">
                        <option value="">Select a source...</option>
                        @php
                            $protectedSources = $systems->where('is_protected', true);
                            $otherSources = $systems->where('is_protected', false);
                        @endphp
                        @if($protectedSources->count() > 0)
                            <optgroup label="Default Sources">
                                @foreach($protectedSources as $system)
                                    <option value="{{ $system->system_id }}" data-callback="{{ $system->callback_url ?? '' }}">
                                        {{ $system->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if($otherSources->count() > 0)
                            <optgroup label="External Systems">
                                @foreach($otherSources as $system)
                                    <option value="{{ $system->system_id }}" data-callback="{{ $system->callback_url ?? '' }}">
                                        {{ $system->name }} ({{ $system->system_id }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Link this source key to a source for webhook configuration.</p>
                </div>
                <div class="mb-4" id="callback_url_group" style="display: none;">
                    <label for="callback_url" class="block text-sm font-medium text-gray-700 mb-2">Callback URL</label>
                    <input type="url" name="callback_url" id="callback_url" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                           placeholder="https://example.com">
                    <p class="mt-1 text-xs text-gray-500">Webhook URL where Priority Bank will send data back to this system.</p>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" 
                            onclick="document.getElementById('createTokenModal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Create Source Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Source Create/Edit Modal -->
<div id="sourceModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900" id="sourceModalTitle">Add New Source</h3>
                <button onclick="closeSourceModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="sourceForm" method="POST">
                @csrf
                <input type="hidden" id="source_id" name="source_id">
                <div id="sourceMethod" name="_method"></div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="source_system_id" class="block text-sm font-medium text-gray-700 mb-2">System ID *</label>
                        <input type="text" name="system_id" id="source_system_id" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., my_system">
                        <p class="mt-1 text-xs text-gray-500">Unique identifier for the system</p>
                        <span class="error-message text-red-600 text-sm mt-1" id="error_system_id" style="display: none;"></span>
                    </div>
                    
                    <div class="mb-4">
                        <label for="source_name" class="block text-sm font-medium text-gray-700 mb-2">Source Name *</label>
                        <input type="text" name="name" id="source_name" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., My System">
                        <span class="error-message text-red-600 text-sm mt-1" id="error_name" style="display: none;"></span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="source_account_number" class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                    <input type="text" name="account_number" id="source_account_number"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="e.g., 1234567890">
                    <p class="mt-1 text-xs text-gray-500">Bank account number for this source</p>
                    <span class="error-message text-red-600 text-sm mt-1" id="error_account_number" style="display: none;"></span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="source_type" class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
                        <select name="type" id="source_type" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="manual">Manual</option>
                            <option value="automated">Automated</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                        <span class="error-message text-red-600 text-sm mt-1" id="error_type" style="display: none;"></span>
                    </div>
                    
                    <div class="mb-4">
                        <label for="source_active_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="active_status" id="source_active_status"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="source_callback_url" class="block text-sm font-medium text-gray-700 mb-2">Callback URL</label>
                    <input type="url" name="callback_url" id="source_callback_url"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="https://example.com/webhook">
                    <span class="error-message text-red-600 text-sm mt-1" id="error_callback_url" style="display: none;"></span>
                </div>
                
                <div class="mb-4">
                    <label for="source_api_base_url" class="block text-sm font-medium text-gray-700 mb-2">API Base URL</label>
                    <input type="url" name="api_base_url" id="source_api_base_url"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="https://api.example.com">
                    <span class="error-message text-red-600 text-sm mt-1" id="error_api_base_url" style="display: none;"></span>
                </div>
                
                <div class="mb-4">
                    <label for="source_description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="source_description" rows="3"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="Description of the source..."></textarea>
                    <span class="error-message text-red-600 text-sm mt-1" id="error_description" style="display: none;"></span>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeSourceModal()"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="submitSourceBtn"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <span id="submitSourceBtnText">Save Source</span>
                        <span id="submitSourceBtnLoader" style="display: none;"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function generateKeyForSource(systemId, systemName, callbackUrl) {
    // Pre-fill the modal with source information
    document.getElementById('name').value = systemName + ' API Key';
    document.getElementById('system_id').value = systemId;
    
    // Show callback URL field and pre-fill if available
    const callbackUrlGroup = document.getElementById('callback_url_group');
    const callbackUrlInput = document.getElementById('callback_url');
    callbackUrlGroup.style.display = 'block';
    callbackUrlInput.value = callbackUrl || '';
    
    // Open the modal
    document.getElementById('createTokenModal').classList.remove('hidden');
}

function toggleCallbackUrl(systemId) {
    const callbackUrlGroup = document.getElementById('callback_url_group');
    const callbackUrlInput = document.getElementById('callback_url');
    const select = document.getElementById('system_id');
    const selectedOption = select.options[select.selectedIndex];
    
    if (systemId) {
        callbackUrlGroup.style.display = 'block';
        // Pre-fill with existing callback URL if available
        const existingCallback = selectedOption.getAttribute('data-callback');
        if (existingCallback) {
            callbackUrlInput.value = existingCallback;
        } else {
            callbackUrlInput.value = '';
        }
    } else {
        callbackUrlGroup.style.display = 'none';
        callbackUrlInput.value = '';
    }
}

function openSourceModal() {
    const modal = document.getElementById('sourceModal');
    const form = document.getElementById('sourceForm');
    const title = document.getElementById('sourceModalTitle');
    
    form.reset();
    document.getElementById('source_id').value = '';
    document.getElementById('sourceMethod').innerHTML = '';
    title.textContent = 'Add New Source';
    form.action = '{{ route("admin.sources.store") }}';
    
    // Clear errors
    document.querySelectorAll('#sourceModal .error-message').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    modal.classList.remove('hidden');
}

function closeSourceModal() {
    document.getElementById('sourceModal').classList.add('hidden');
    document.getElementById('sourceForm').reset();
    document.getElementById('source_id').value = '';
}

function editSource(id, systemId, name, type, callbackUrl, apiBaseUrl, activeStatus, description, accountNumber) {
    const modal = document.getElementById('sourceModal');
    const form = document.getElementById('sourceForm');
    const title = document.getElementById('sourceModalTitle');
    
    document.getElementById('source_id').value = id;
    document.getElementById('source_system_id').value = systemId;
    document.getElementById('source_name').value = name;
    document.getElementById('source_type').value = type;
    document.getElementById('source_callback_url').value = callbackUrl || '';
    document.getElementById('source_api_base_url').value = apiBaseUrl || '';
    document.getElementById('source_active_status').value = activeStatus ? '1' : '0';
    document.getElementById('source_description').value = description || '';
    document.getElementById('source_account_number').value = accountNumber || '';
    
    title.textContent = 'Edit Source';
    form.action = '{{ route("admin.sources.update", ":id") }}'.replace(':id', id);
    document.getElementById('sourceMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    
    // Clear errors
    document.querySelectorAll('#sourceModal .error-message').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    modal.classList.remove('hidden');
}

// Handle source form submission
document.getElementById('sourceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Clear previous errors
    document.querySelectorAll('#sourceModal .error-message').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    const submitBtn = document.getElementById('submitSourceBtn');
    const submitBtnText = document.getElementById('submitSourceBtnText');
    const submitBtnLoader = document.getElementById('submitSourceBtnLoader');
    
    submitBtn.disabled = true;
    submitBtnText.style.display = 'none';
    submitBtnLoader.style.display = 'inline';
    
    const formData = new FormData(this);
    const url = this.action;
    const method = this.querySelector('input[name="_method"]')?.value || 'POST';
    
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
</script>
@endsection
