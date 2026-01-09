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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Used</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tokens as $token)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $token->name }}</div>
                            <div class="text-xs text-gray-500">Token ID: {{ substr($token->token, 0, 20) }}...</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $token->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $token->last_used_at ? $token->last_used_at->format('M d, Y H:i') : 'Never' }}
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
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
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
@endsection
