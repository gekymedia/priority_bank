@extends('layouts.app')

@section('title', 'Payment Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Payment Settings</h1>
        <p class="text-gray-600 mt-1">Configure Hubtel and Paystack. When both are configured, <strong>Hubtel takes precedence</strong>. Values saved here override .env when set.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(isset($activeGateway) && $activeGateway)
        <div class="mb-6 p-4 bg-white rounded-lg shadow-md flex items-center gap-3">
            <span class="text-sm text-gray-600">Active payment gateway:</span>
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">{{ strtoupper($activeGateway) }}</span>
        </div>
    @endif

    <form action="{{ route('admin.payment-settings.update') }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- Hubtel --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center"><i class="fas fa-mobile-alt"></i></div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Hubtel</h2>
                        <p class="text-sm text-gray-500">Client ID / Client Secret or API Key / API Secret from Hubtel.</p>
                    </div>
                </div>
                @if($channels['hubtel']['configured'])
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Configured</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Not configured</span>
                @endif
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="hubtel_client_id" class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                    <input type="text" name="hubtel_client_id" id="hubtel_client_id" value="{{ old('hubtel_client_id', $settings['hubtel_client_id'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="From Hubtel dashboard">
                </div>
                <div>
                    <label for="hubtel_client_secret" class="block text-sm font-medium text-gray-700 mb-1">Client secret</label>
                    <div class="relative">
                        <input type="password" name="hubtel_client_secret" id="hubtel_client_secret" value="{{ old('hubtel_client_secret', $settings['hubtel_client_secret'] ?? '') }}"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Leave blank to keep current">
                        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 toggle-secret" data-target="hubtel_client_secret"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div>
                    <label for="hubtel_api_key" class="block text-sm font-medium text-gray-700 mb-1">API key (alternative)</label>
                    <input type="text" name="hubtel_api_key" id="hubtel_api_key" value="{{ old('hubtel_api_key', $settings['hubtel_api_key'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="hubtel_api_secret" class="block text-sm font-medium text-gray-700 mb-1">API secret (alternative)</label>
                    <div class="relative">
                        <input type="password" name="hubtel_api_secret" id="hubtel_api_secret" value="{{ old('hubtel_api_secret', $settings['hubtel_api_secret'] ?? '') }}"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Leave blank to keep current">
                        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 toggle-secret" data-target="hubtel_api_secret"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label for="hubtel_merchant_account_number" class="block text-sm font-medium text-gray-700 mb-1">Merchant account number (optional)</label>
                    <input type="text" name="hubtel_merchant_account_number" id="hubtel_merchant_account_number" value="{{ old('hubtel_merchant_account_number', $settings['hubtel_merchant_account_number'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Optional">
                </div>
            </div>
        </div>

        {{-- Paystack --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center"><i class="fas fa-credit-card"></i></div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Paystack</h2>
                        <p class="text-sm text-gray-500">Public and secret keys from Paystack dashboard.</p>
                    </div>
                </div>
                @if($channels['paystack']['configured'])
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Configured</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Not configured</span>
                @endif
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="paystack_public_key" class="block text-sm font-medium text-gray-700 mb-1">Public key</label>
                    <input type="text" name="paystack_public_key" id="paystack_public_key" value="{{ old('paystack_public_key', $settings['paystack_public_key'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="pk_live_... or pk_test_...">
                </div>
                <div>
                    <label for="paystack_secret_key" class="block text-sm font-medium text-gray-700 mb-1">Secret key</label>
                    <div class="relative">
                        <input type="password" name="paystack_secret_key" id="paystack_secret_key" value="{{ old('paystack_secret_key', $settings['paystack_secret_key'] ?? '') }}"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Leave blank to keep current">
                        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 toggle-secret" data-target="paystack_secret_key"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-save mr-2"></i> Save payment settings
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.querySelectorAll('.toggle-secret').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-target');
        var input = document.getElementById(id);
        var icon = this.querySelector('i');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});
</script>
@endpush
@endsection
