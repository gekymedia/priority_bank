@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">API Key Management</h1>
        <button onclick="document.getElementById('createTokenModal').classList.remove('hidden')" 
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            Create New API Key
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
                <code class="bg-yellow-200 px-2 py-1 rounded flex-1 font-mono">{{ session('token') }}</code>
                <button onclick="navigator.clipboard.writeText('{{ session('token') }}')" 
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                    Copy
                </button>
            </div>
        </div>
    @endif

    @if($tokens->isEmpty())
        <div class="bg-white shadow-md rounded-lg p-8 text-center">
            <p class="text-gray-600 mb-4">You don't have any API keys yet.</p>
            <p class="text-sm text-gray-500">Create an API key to connect your business systems to Priority Bank API.</p>
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
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

    <div class="mt-6">
        <a href="{{ route('api-keys.documentation') }}" 
           class="text-blue-500 hover:text-blue-700 underline">
            View API Documentation →
        </a>
    </div>
</div>

<!-- Create Token Modal -->
<div id="createTokenModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Create New API Key</h3>
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
                    <label for="system_id" class="block text-sm font-medium text-gray-700 mb-2">External System (Optional)</label>
                    <select name="system_id" id="system_id" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            onchange="toggleCallbackUrl(this.value)">
                        <option value="">Select a system...</option>
                        @foreach($systems as $system)
                            <option value="{{ $system->system_id }}" data-callback="{{ $system->callback_url ?? '' }}">
                                {{ $system->name }} ({{ $system->system_id }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Link this API key to an external system for webhook configuration.</p>
                </div>
                <div class="mb-4" id="callback_url_group" style="display: none;">
                    <label for="callback_url" class="block text-sm font-medium text-gray-700 mb-2">Callback URL</label>
                    <input type="url" name="callback_url" id="callback_url" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                           placeholder="https://example.com">
                    <p class="mt-1 text-xs text-gray-500">Webhook URL where Priority Bank will send data back to this system.</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" 
                            onclick="document.getElementById('createTokenModal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
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
</script>
@endsection
