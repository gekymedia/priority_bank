@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6 flex-wrap gap-3">
        <h1 class="text-2xl font-bold">Accounts API keys Management</h1>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('api-keys.documentation') }}" 
               class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fas fa-book mr-2"></i>
                View API Documentation →
            </a>
            <button type="button" onclick="openSourceModal()" 
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Add new source
            </button>
            <button type="button" onclick="document.getElementById('createTokenModal').classList.remove('hidden')" 
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition-colors">
                <i class="fas fa-key mr-2"></i>
                Create New API Key
            </button>
        </div>
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

    <!-- All System Accounts -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">All System Accounts</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API Keys</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone / Account Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Protected</th> --}}
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Callback URL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($systems as $system)
                    @php
                        $linkedTokens = $tokens->filter(fn($t) => ($system->metadata['api_token_id'] ?? null) == $t->id);
                        $apiKeysCount = $linkedTokens->count();
                        $tokensForModal = $linkedTokens->map(fn($t) => [
                            'id' => $t->id,
                            'name' => $t->name,
                            'created_at' => $t->created_at->format('M d, Y H:i'),
                            'last_used_at' => $t->last_used_at ? $t->last_used_at->format('M d, Y H:i') : 'Never',
                            'expires_at' => $t->expires_at ? $t->expires_at->format('M d, Y') : null,
                        ])->values();
                    @endphp
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
                            <button type="button"
                                    onclick="openApiKeysModal('{{ addslashes(e($system->name)) }}', {{ json_encode($tokensForModal) }})"
                                    class="inline-flex items-center px-2.5 py-1 rounded text-sm font-medium {{ $apiKeysCount > 0 ? 'bg-indigo-100 text-indigo-800 hover:bg-indigo-200' : 'bg-gray-100 text-gray-500' }}"
                                    title="View API keys for this account">
                                <i class="fas fa-key mr-1"></i>{{ $apiKeysCount }}
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-700">{{ $system->user?->phone ?? $system->account_number ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst($system->type) }}
                            </span>
                        </td>
                        {{-- Status --}}
                        {{-- <td class="px-6 py-4 whitespace-nowrap">
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
                        </td> --}}
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
                                    <button type="button" class="link-account-btn text-indigo-600 hover:text-indigo-900 whitespace-nowrap" title="Link an account to this source"
                                            data-system-name="{{ $system->name }}"
                                            data-create-url="{{ route('admin.sources.create-user', $system) }}"
                                            data-link-url="{{ route('admin.sources.link-user', $system) }}">
                                        <i class="fas fa-user-plus mr-1"></i>Create user & link
                                    </button>
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
                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                            No system accounts found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- API Keys for Account Modal -->
<div id="apiKeysModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900" id="apiKeysModalTitle">API keys</h3>
                <button type="button" onclick="closeApiKeysModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="apiKeysModalBody" class="space-y-3 max-h-96 overflow-y-auto">
                <!-- Filled by JS -->
            </div>
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-xs text-gray-500">The secret key is only shown once when the key is created. You can delete and create a new key if needed.</p>
            </div>
        </div>
    </div>
</div>

<!-- Link Account Modal (create new user or select existing) -->
<div id="linkAccountModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900" id="linkAccountModalTitle">Link account</h3>
                <button type="button" onclick="closeLinkAccountModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-4">Choose how to link an account to this source:</p>
            <div class="space-y-4">
                <div class="flex rounded-lg border border-gray-200 overflow-hidden">
                    <label class="flex-1 flex items-center justify-center gap-2 py-3 px-4 cursor-pointer border-r border-gray-200 hover:bg-gray-50 link-account-choice" data-panel="create">
                        <input type="radio" name="link_account_choice" value="create" class="link-account-radio" checked>
                        <span class="text-sm font-medium">Create new user</span>
                    </label>
                    <label class="flex-1 flex items-center justify-center gap-2 py-3 px-4 cursor-pointer hover:bg-gray-50 link-account-choice" data-panel="select">
                        <input type="radio" name="link_account_choice" value="select" class="link-account-radio">
                        <span class="text-sm font-medium">Select existing user</span>
                    </label>
                </div>
                <div id="linkAccountPanelCreate" class="link-account-panel border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <p class="text-sm text-gray-700 mb-3">A new system user account will be created and linked to this source.</p>
                    <form id="linkAccountCreateForm" method="POST" action="">
                        @csrf
                        <button type="submit" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                            Create new user & link
                        </button>
                    </form>
                </div>
                <div id="linkAccountPanelSelect" class="link-account-panel hidden border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <p class="text-sm text-gray-700 mb-3">Choose an approved user to link as the account owner for this source.</p>
                    <form id="linkAccountLinkForm" method="POST" action="">
                        @csrf
                        <div class="mb-3">
                            <label for="link_account_user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                            <select name="user_id" id="link_account_user_id" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">Select user...</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                            Link selected user
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Token Modal -->
<div id="createTokenModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Create New API Key</h3>
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
                    <label for="system_id" class="block text-sm font-medium text-gray-700 mb-2">Account (Optional)</label>
                    <select name="system_id" id="system_id" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            onchange="toggleCallbackUrl(this.value)">
                        <option value="">Select an account...</option>
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
                        Create API Key
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
window.apiKeyDestroyUrl = "{{ url('api-keys') }}";
window.apiKeyCsrf = "{{ csrf_token() }}";

function openApiKeysModal(accountName, tokens) {
    document.getElementById('apiKeysModalTitle').textContent = 'API keys for ' + accountName;
    var body = document.getElementById('apiKeysModalBody');
    if (!tokens || tokens.length === 0) {
        body.innerHTML = '<p class="text-sm text-gray-500">No API keys linked to this account.</p>';
    } else {
        body.innerHTML = tokens.map(function(t) {
            var form = '<form action="' + window.apiKeyDestroyUrl + '/' + t.id + '" method="POST" class="inline" onsubmit="return confirm(\'Are you sure you want to delete this API key?\');">' +
                '<input type="hidden" name="_token" value="' + window.apiKeyCsrf + '">' +
                '<input type="hidden" name="_method" value="DELETE">' +
                '<button type="submit" class="text-red-500 hover:text-red-700 text-sm">Delete</button>' +
                '</form>';
            return '<div class="p-3 bg-gray-50 rounded-lg border border-gray-200">' +
                '<div class="font-medium text-gray-900">' + (t.name || 'Key #' + t.id) + '</div>' +
                '<div class="text-xs text-gray-500 mt-1">Created: ' + t.created_at + '</div>' +
                '<div class="text-xs text-gray-500">Last used: ' + t.last_used_at + '</div>' +
                (t.expires_at ? '<div class="text-xs text-gray-500">Expires: ' + t.expires_at + '</div>' : '') +
                '<div class="mt-2">' + form + '</div>' +
                '</div>';
        }).join('');
    }
    document.getElementById('apiKeysModal').classList.remove('hidden');
}

function closeApiKeysModal() {
    document.getElementById('apiKeysModal').classList.add('hidden');
}

function openLinkAccountModal(button) {
    var systemName = button.getAttribute('data-system-name') || 'this source';
    var createUrl = button.getAttribute('data-create-url');
    var linkUrl = button.getAttribute('data-link-url');
    if (!createUrl || !linkUrl) return;
    document.getElementById('linkAccountModalTitle').textContent = 'Link account to ' + systemName;
    document.getElementById('linkAccountCreateForm').action = createUrl;
    document.getElementById('linkAccountLinkForm').action = linkUrl;
    document.getElementById('link_account_user_id').value = '';
    document.querySelectorAll('.link-account-radio')[0].checked = true;
    document.getElementById('linkAccountPanelCreate').classList.remove('hidden');
    document.getElementById('linkAccountPanelSelect').classList.add('hidden');
    document.getElementById('linkAccountModal').classList.remove('hidden');
}

function closeLinkAccountModal() {
    document.getElementById('linkAccountModal').classList.add('hidden');
}

document.querySelectorAll('.link-account-btn').forEach(function(btn) {
    btn.addEventListener('click', function() { openLinkAccountModal(this); });
});
document.querySelectorAll('.link-account-radio').forEach(function(radio) {
    radio.addEventListener('change', function() {
        var panelCreate = document.getElementById('linkAccountPanelCreate');
        var panelSelect = document.getElementById('linkAccountPanelSelect');
        if (this.value === 'create') {
            panelCreate.classList.remove('hidden');
            panelSelect.classList.add('hidden');
        } else {
            panelCreate.classList.add('hidden');
            panelSelect.classList.remove('hidden');
        }
    });
});

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
