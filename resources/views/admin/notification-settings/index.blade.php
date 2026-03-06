@extends('layouts.app')

@section('title', 'Notification Settings')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Multi-Channel Notification Settings</h1>
        <p class="text-gray-600 mt-1">Configure GekyChat, WhatsApp, Email, and SMS. Values saved here override .env when set.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg">{{ session('error') }}</div>
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

    <form action="{{ route('admin.notification-settings.update') }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- Channel toggles --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Enable channels</h2>
            <div class="flex flex-wrap gap-6">
                @foreach($channels as $key => $channel)
                <label class="flex items-center cursor-pointer">
                    <input type="hidden" name="channel_{{ $key }}_enabled" value="0">
                    <input type="checkbox" name="channel_{{ $key }}_enabled" value="1" {{ $channel['enabled'] ? 'checked' : '' }}
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm font-medium text-gray-700">{{ $channel['name'] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Email --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fas fa-envelope"></i></div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Email</h2>
                        <p class="text-sm text-gray-500">SMTP or log driver. Configure below and save.</p>
                    </div>
                </div>
                @if($channels['email']['configured'])
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Configured</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Not configured</span>
                @endif
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="mail_driver" class="block text-sm font-medium text-gray-700 mb-1">Mail driver</label>
                    <select name="mail_driver" id="mail_driver" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="log" {{ ($settings['mail_driver'] ?? '') == 'log' ? 'selected' : '' }}>Log (no real email)</option>
                        <option value="smtp" {{ ($settings['mail_driver'] ?? '') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                        <option value="mailgun" {{ ($settings['mail_driver'] ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                        <option value="ses" {{ ($settings['mail_driver'] ?? '') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                        <option value="sendmail" {{ ($settings['mail_driver'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                    </select>
                </div>
                <div>
                    <label for="from_email" class="block text-sm font-medium text-gray-700 mb-1">From email</label>
                    <input type="email" name="from_email" id="from_email" value="{{ old('from_email', $settings['from_email'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="noreply@example.com">
                </div>
                <div>
                    <label for="from_name" class="block text-sm font-medium text-gray-700 mb-1">From name</label>
                    <input type="text" name="from_name" id="from_name" value="{{ old('from_name', $settings['from_name'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="{{ config('app.name') }}">
                </div>
                <div>
                    <label for="smtp_host" class="block text-sm font-medium text-gray-700 mb-1">SMTP host</label>
                    <input type="text" name="smtp_host" id="smtp_host" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="smtp.mailtrap.io">
                </div>
                <div>
                    <label for="smtp_port" class="block text-sm font-medium text-gray-700 mb-1">SMTP port</label>
                    <input type="text" name="smtp_port" id="smtp_port" value="{{ old('smtp_port', $settings['smtp_port'] ?? '587') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="587">
                </div>
                <div>
                    <label for="smtp_encryption" class="block text-sm font-medium text-gray-700 mb-1">Encryption</label>
                    <select name="smtp_encryption" id="smtp_encryption" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="tls" {{ ($settings['smtp_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                        <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                        <option value="" {{ empty($settings['smtp_encryption'] ?? '') ? 'selected' : '' }}>None</option>
                    </select>
                </div>
                <div>
                    <label for="smtp_username" class="block text-sm font-medium text-gray-700 mb-1">SMTP username</label>
                    <input type="text" name="smtp_username" id="smtp_username" value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="smtp_password" class="block text-sm font-medium text-gray-700 mb-1">SMTP password</label>
                    <div class="relative">
                        <input type="password" name="smtp_password" id="smtp_password" value="{{ old('smtp_password', $settings['smtp_password'] ?? '') }}"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Leave blank to keep current">
                        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none toggle-secret" data-target="smtp_password" aria-label="Show password" title="Show / hide">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- SMS (Arkesel) --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center"><i class="fas fa-sms"></i></div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">SMS (Arkesel)</h2>
                        <p class="text-sm text-gray-500">Arkesel API for SMS. Get credentials from Arkesel dashboard.</p>
                    </div>
                </div>
                @if($channels['sms']['configured'])
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Configured</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Not configured</span>
                @endif
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label for="arkesel_api_key" class="block text-sm font-medium text-gray-700 mb-1">Arkesel API key</label>
                    <div class="relative">
                        <input type="password" name="arkesel_api_key" id="arkesel_api_key" value="{{ old('arkesel_api_key', $settings['arkesel_api_key'] ?? '') }}"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Leave blank to keep current">
                        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none toggle-secret" data-target="arkesel_api_key" aria-label="Show API key" title="Show / hide">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="arkesel_api_url" class="block text-sm font-medium text-gray-700 mb-1">API URL</label>
                    <input type="url" name="arkesel_api_url" id="arkesel_api_url" value="{{ old('arkesel_api_url', $settings['arkesel_api_url'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="https://sms.arkesel.com/sms/api">
                </div>
                <div>
                    <label for="arkesel_sender" class="block text-sm font-medium text-gray-700 mb-1">Sender ID</label>
                    <input type="text" name="arkesel_sender" id="arkesel_sender" value="{{ old('arkesel_sender', $settings['arkesel_sender'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="PriorityBank" maxlength="50">
                </div>
                <div class="md:col-span-2 flex flex-wrap items-center gap-2">
                    <span class="text-sm text-gray-600">SMS balance (Arkesel):</span>
                    <span id="sms_balance_display" class="text-sm font-medium text-gray-900">—</span>
                    <button type="button" id="sms_balance_btn" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">Refresh</button>
                </div>
            </div>
        </div>

        {{-- GekyChat --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fas fa-comments"></i></div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">GekyChat</h2>
                        <p class="text-sm text-gray-500">Client ID and Client Secret from GekyChat dashboard.</p>
                    </div>
                </div>
                @if($channels['gekychat']['configured'])
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Configured</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Not configured</span>
                @endif
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="gekychat_client_id" class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                    <input type="text" name="gekychat_client_id" id="gekychat_client_id" value="{{ old('gekychat_client_id', $settings['gekychat_client_id'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="e.g. dev_00000001_xxx">
                </div>
                <div>
                    <label for="gekychat_client_secret" class="block text-sm font-medium text-gray-700 mb-1">Client secret</label>
                    <div class="relative">
                        <input type="password" name="gekychat_client_secret" id="gekychat_client_secret" value="{{ old('gekychat_client_secret', $settings['gekychat_client_secret'] ?? '') }}"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Leave blank to keep current">
                        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none toggle-secret" data-target="gekychat_client_secret" aria-label="Show secret" title="Show / hide">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label for="gekychat_base_url" class="block text-sm font-medium text-gray-700 mb-1">Base URL (optional)</label>
                    <input type="url" name="gekychat_base_url" id="gekychat_base_url" value="{{ old('gekychat_base_url', $settings['gekychat_base_url'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="https://api.gekychat.com (or http://api.gekychat.test for local)">
                </div>
            </div>
        </div>

        {{-- WhatsApp --}}
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center"><i class="fab fa-whatsapp"></i></div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">WhatsApp</h2>
                        <p class="text-sm text-gray-500">WhatsApp Business API – Phone Number ID and Access Token from Meta.</p>
                    </div>
                </div>
                @if($channels['whatsapp']['configured'])
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Configured</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Not configured</span>
                @endif
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="whatsapp_phone_number_id" class="block text-sm font-medium text-gray-700 mb-1">Phone Number ID</label>
                    <input type="text" name="whatsapp_phone_number_id" id="whatsapp_phone_number_id" value="{{ old('whatsapp_phone_number_id', $settings['whatsapp_phone_number_id'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="From Meta Business Suite">
                </div>
                <div>
                    <label for="whatsapp_access_token" class="block text-sm font-medium text-gray-700 mb-1">Access token</label>
                    <div class="relative">
                        <input type="password" name="whatsapp_access_token" id="whatsapp_access_token" value="{{ old('whatsapp_access_token', $settings['whatsapp_access_token'] ?? '') }}"
                            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="Leave blank to keep current">
                        <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none toggle-secret" data-target="whatsapp_access_token" aria-label="Show token" title="Show / hide">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label for="whatsapp_base_url" class="block text-sm font-medium text-gray-700 mb-1">Base URL (optional)</label>
                    <input type="url" name="whatsapp_base_url" id="whatsapp_base_url" value="{{ old('whatsapp_base_url', $settings['whatsapp_base_url'] ?? '') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" placeholder="https://graph.facebook.com">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                <i class="fas fa-save mr-2"></i> Save all notification settings
            </button>
        </div>
    </form>

    {{-- Test sections (one per channel) --}}
    <div class="mt-10">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Test channels</h2>
        <p class="text-sm text-gray-600 mb-6">Enter the email or phone number to send a test message to. Configure and save above first.</p>
        <div class="grid gap-6">
            @foreach($channels as $key => $channel)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    @if($key === 'email') <i class="fas fa-envelope text-blue-500"></i>
                    @elseif($key === 'gekychat') <i class="fas fa-comments text-indigo-500"></i>
                    @elseif($key === 'whatsapp') <i class="fab fa-whatsapp text-green-500"></i>
                    @else <i class="fas fa-sms text-amber-500"></i>
                    @endif
                    {{ $channel['name'] }}
                    @if($channel['configured'])
                        <span class="text-xs font-normal text-green-600">Configured</span>
                    @else
                        <span class="text-xs font-normal text-amber-600">Configure above first</span>
                    @endif
                </h3>
                <form action="{{ route('admin.notification-settings.test') }}" method="POST" class="flex flex-wrap items-end gap-3 mt-3">
                    @csrf
                    <input type="hidden" name="channel" value="{{ $key }}">
                    @if($key === 'email')
                        <div class="flex-1 min-w-[200px]">
                            <label for="test_email_{{ $key }}" class="sr-only">Email</label>
                            <input type="email" name="test_email" id="test_email_{{ $key }}" value="{{ old('test_email') }}"
                                placeholder="Enter email address to test"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                    @else
                        <div class="flex-1 min-w-[200px]">
                            <label for="test_phone_{{ $key }}" class="sr-only">Phone</label>
                            <input type="text" name="test_phone" id="test_phone_{{ $key }}" value="{{ old('test_phone') }}"
                                placeholder="e.g. 0241234567 or 233241234567"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                    @endif
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium inline-flex items-center">
                        <i class="fas fa-paper-plane mr-2"></i> Send test
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
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
            this.setAttribute('aria-label', 'Hide');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            this.setAttribute('aria-label', 'Show');
        }
    });
});

function fetchSmsBalance() {
    var el = document.getElementById('sms_balance_display');
    var btn = document.getElementById('sms_balance_btn');
    if (!el || !btn) return;
    el.textContent = '…';
    btn.disabled = true;
    fetch('{{ route("admin.notification-settings.sms-balance") }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && typeof data.balance !== 'undefined') {
                el.textContent = data.balance.toLocaleString() + ' credits';
            } else {
                el.textContent = data.error || 'Error';
            }
        })
        .catch(function() { el.textContent = 'Error'; })
        .finally(function() { btn.disabled = false; });
}
document.getElementById('sms_balance_btn') && document.getElementById('sms_balance_btn').addEventListener('click', fetchSmsBalance);
</script>
@endpush
@endsection
