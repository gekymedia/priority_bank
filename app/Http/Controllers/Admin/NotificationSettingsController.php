<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Services\ArkeselBalanceService;
use App\Services\GekyChatService;
use App\Jobs\SendNotificationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationSettingsController extends Controller
{
    /** Placeholder shown in secret fields when a value is stored (never save this to DB). */
    public const SECRET_MASK = '********';

    public function index()
    {
        $settings = $this->getSettingsWithFallback();
        $channels = $this->getChannelsStatus($settings);
        return view('admin.notification-settings.index', compact('channels', 'settings'));
    }

    protected function getSettingsWithFallback(): array
    {
        $fromDb = function (string $key, $configFallback) {
            $v = NotificationSetting::get($key);
            return $v !== null && $v !== '' ? $v : $configFallback;
        };
        return [
            'mail_driver' => $fromDb('mail_driver', config('mail.default', 'log')),
            'from_email' => $fromDb('from_email', config('mail.from.address', 'noreply@example.com')),
            'from_name' => $fromDb('from_name', config('mail.from.name', config('app.name'))),
            'smtp_host' => $fromDb('smtp_host', config('mail.mailers.smtp.host', '')),
            'smtp_port' => $fromDb('smtp_port', (string) (config('mail.mailers.smtp.port') ?? 587)),
            'smtp_username' => $fromDb('smtp_username', config('mail.mailers.smtp.username', '')),
            'smtp_password' => $this->secretDisplayValue('smtp_password', config('mail.mailers.smtp.password')),
            'smtp_encryption' => $fromDb('smtp_encryption', config('mail.mailers.smtp.encryption', 'tls')),
            'gekychat_client_id' => $fromDb('gekychat_client_id', config('services.gekychat.client_id', '')),
            'gekychat_client_secret' => $this->secretDisplayValue('gekychat_client_secret', config('services.gekychat.client_secret')),
            'gekychat_base_url' => $fromDb('gekychat_base_url', config('services.gekychat.base_url', 'https://api.gekychat.com')),
            'whatsapp_phone_number_id' => $fromDb('whatsapp_phone_number_id', config('services.whatsapp.phone_number_id', '')),
            'whatsapp_access_token' => $this->secretDisplayValue('whatsapp_access_token', config('services.whatsapp.access_token') ?? config('services.whatsapp.api_token')),
            'whatsapp_base_url' => $fromDb('whatsapp_base_url', config('services.whatsapp.base_url', 'https://graph.facebook.com')),
            'arkesel_api_key' => $this->secretDisplayValue('arkesel_api_key', config('services.arkesel.api_key')),
            'arkesel_api_url' => $fromDb('arkesel_api_url', config('services.arkesel.url', 'https://sms.arkesel.com/sms/api')),
            'arkesel_sender' => $fromDb('arkesel_sender', config('services.arkesel.sender', 'PriorityBank')),
        ];
    }

    /** Return mask for view when a secret is set, so user sees *** and knows "leave blank to keep". */
    protected function secretDisplayValue(string $key, $configFallback): string
    {
        $v = NotificationSetting::get($key);
        if ($v !== null && $v !== '') return self::SECRET_MASK;
        if ($configFallback !== null && $configFallback !== '') return self::SECRET_MASK;
        return '';
    }

    /** Update secret only when user entered a new value (not empty, not the mask). */
    protected function setSecretIfChanged(string $key, Request $request): void
    {
        $value = $request->input($key);
        if ($value === null) {
            return;
        }
        $value = is_string($value) ? trim($value) : '';
        if ($value === '') {
            return;
        }
        // Never save placeholder/mask (exact match or any string of only asterisks)
        if ($value === self::SECRET_MASK || preg_match('/^\*+$/', $value)) {
            return;
        }
        NotificationSetting::set($key, $value);
    }

    protected function getChannelsStatus(array $settings): array
    {
        $has = function (string $key) {
            $v = NotificationSetting::get($key);
            if ($v !== null && $v !== '') return true;
            if ($key === 'arkesel_api_key') return !empty(config('services.arkesel.api_key'));
            if ($key === 'gekychat_client_secret') return !empty(config('services.gekychat.client_secret'));
            $wa = config('services.whatsapp.access_token') ?? config('services.whatsapp.api_token');
            if ($key === 'whatsapp_access_token') return !empty($wa);
            return false;
        };
        $emailConfigured = ($settings['mail_driver'] ?? '') !== 'log' && !empty($settings['from_email'] ?? '');
        $smsConfigured = $has('arkesel_api_key') || !empty(config('services.arkesel.api_key'));
        $waToken = config('services.whatsapp.access_token') ?? config('services.whatsapp.api_token');
        $whatsappConfigured = !empty($settings['whatsapp_phone_number_id'] ?? '') && ($has('whatsapp_access_token') || !empty($waToken));
        $gekychatConfigured = !empty($settings['gekychat_client_id'] ?? '') && ($has('gekychat_client_secret') || !empty(config('services.gekychat.client_secret')));

        return [
            'email' => [
                'name' => 'Email',
                'enabled' => NotificationSetting::channelEnabled('email'),
                'configured' => $emailConfigured,
                'description' => 'Send notifications via email.',
            ],
            'sms' => [
                'name' => 'SMS (Arkesel)',
                'enabled' => NotificationSetting::channelEnabled('sms'),
                'configured' => $smsConfigured,
                'description' => 'Send SMS via Arkesel.',
            ],
            'whatsapp' => [
                'name' => 'WhatsApp',
                'enabled' => NotificationSetting::channelEnabled('whatsapp'),
                'configured' => $whatsappConfigured,
                'description' => 'Send notifications via WhatsApp Business API.',
            ],
            'gekychat' => [
                'name' => 'GekyChat',
                'enabled' => NotificationSetting::channelEnabled('gekychat'),
                'configured' => $gekychatConfigured,
                'description' => 'Send notifications via GekyChat.',
            ],
        ];
    }

    public function update(Request $request)
    {
        $request->validate([
            'channel_email_enabled' => 'nullable|boolean',
            'channel_sms_enabled' => 'nullable|boolean',
            'channel_whatsapp_enabled' => 'nullable|boolean',
            'channel_gekychat_enabled' => 'nullable|boolean',
            'mail_driver' => 'nullable|string|in:log,smtp,mailgun,ses,postmark,sendmail',
            'from_email' => 'nullable|email',
            'from_name' => 'nullable|string|max:255',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|string|max:10',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|in:tls,ssl,',
            'gekychat_client_id' => 'nullable|string|max:255',
            'gekychat_client_secret' => 'nullable|string|max:255',
            'gekychat_base_url' => 'nullable|string|url|max:255',
            'whatsapp_phone_number_id' => 'nullable|string|max:255',
            'whatsapp_access_token' => 'nullable|string|max:500',
            'whatsapp_base_url' => 'nullable|string|url|max:255',
            'arkesel_api_key' => 'nullable|string|max:255',
            'arkesel_api_url' => 'nullable|string|url|max:255',
            'arkesel_sender' => 'nullable|string|max:50',
        ]);

        NotificationSetting::setChannelEnabled('email', (bool) $request->boolean('channel_email_enabled'));
        NotificationSetting::setChannelEnabled('sms', (bool) $request->boolean('channel_sms_enabled'));
        NotificationSetting::setChannelEnabled('whatsapp', (bool) $request->boolean('channel_whatsapp_enabled'));
        NotificationSetting::setChannelEnabled('gekychat', (bool) $request->boolean('channel_gekychat_enabled'));

        $textKeys = [
            'mail_driver', 'from_email', 'from_name', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_encryption',
            'gekychat_client_id', 'gekychat_base_url', 'whatsapp_phone_number_id', 'whatsapp_base_url',
            'arkesel_api_url', 'arkesel_sender',
        ];
        foreach ($textKeys as $key) {
            NotificationSetting::set($key, $request->input($key));
        }
        $this->setSecretIfChanged('smtp_password', $request);
        $this->setSecretIfChanged('gekychat_client_secret', $request);
        $this->setSecretIfChanged('whatsapp_access_token', $request);
        $this->setSecretIfChanged('arkesel_api_key', $request);

        return redirect()->route('admin.notification-settings.index')
            ->with('success', 'Notification settings saved.');
    }

    /**
     * GET reveal a secret value for display (e.g. when user clicks "show"). Returns JSON { value } or 404.
     * Only for authenticated admins; key must be one of the allowed secret keys.
     */
    public function revealSecret(Request $request): \Illuminate\Http\JsonResponse
    {
        $allowed = ['arkesel_api_key', 'gekychat_client_secret', 'whatsapp_access_token', 'smtp_password'];
        $key = $request->query('key');
        if (! in_array($key, $allowed, true)) {
            return response()->json(['error' => 'Invalid key'], 400);
        }
        $value = NotificationSetting::get($key);
        if ($value === null || $value === '') {
            $value = match ($key) {
                'arkesel_api_key' => config('services.arkesel.api_key'),
                'gekychat_client_secret' => config('services.gekychat.client_secret'),
                'whatsapp_access_token' => config('services.whatsapp.access_token') ?? config('services.whatsapp.api_token'),
                'smtp_password' => config('mail.mailers.smtp.password'),
                default => '',
            };
        }
        if ($value === null || $value === '') {
            return response()->json(['value' => '']);
        }
        return response()->json(['value' => $value]);
    }

    /**
     * GET SMS balance (Arkesel). Returns JSON { success, balance?, error? }.
     */
    public function smsBalance(ArkeselBalanceService $arkeselBalance): \Illuminate\Http\JsonResponse
    {
        NotificationSetting::applyToConfig();
        $apiKey = NotificationSetting::get('arkesel_api_key') ?: config('services.arkesel.api_key');
        if (empty($apiKey)) {
            return response()->json(['success' => false, 'error' => 'SMS not configured. Set Arkesel API key in Notification Settings.'], 400);
        }
        $result = $arkeselBalance->getBalance();
        return response()->json($result);
    }

    public function test(Request $request)
    {
        $request->validate([
            'channel' => 'required|in:email,sms,whatsapp,gekychat',
            'test_email' => 'required_if:channel,email|nullable|email',
            'test_phone' => 'required_unless:channel,email|nullable|string|max:30',
        ]);
        $channel = $request->channel;
        $testEmail = $request->input('test_email');
        $testPhone = $request->input('test_phone');
        $message = 'This is a test notification from Priority Bank. If you received this, the channel is working.';

        NotificationSetting::applyToConfig();

        try {
            switch ($channel) {
                case 'email':
                    if (empty($testEmail)) {
                        return back()->with('error', 'Please enter an email address to send the test to.');
                    }
                    SendNotificationMessage::dispatch('email', $testEmail, $message, 'Priority Bank – Test Notification');
                    return back()->with('success', 'Test email queued to ' . $testEmail . ' (check spam folder).');
                case 'sms':
                    if (empty($testPhone)) {
                        return back()->with('error', 'Please enter a phone number to send the test to.');
                    }
                    SendNotificationMessage::dispatch('sms', $testPhone, $message);
                    return back()->with('success', 'Test SMS queued to ' . $testPhone . '.');
                case 'whatsapp':
                    if (!class_exists(\App\Services\WhatsAppService::class)) {
                        return back()->with('error', 'WhatsApp service class not found. Add it to use WhatsApp.');
                    }
                    if (empty($testPhone)) {
                        return back()->with('error', 'Please enter a phone number to send the test to.');
                    }
                    $whatsApp = app(\App\Services\WhatsAppService::class);
                    $whatsApp->sendMessage($testPhone, $message);
                    return back()->with('success', 'Test WhatsApp sent to ' . $testPhone . '.');
                case 'gekychat':
                    if (!config('services.gekychat.client_id') || !config('services.gekychat.client_secret')) {
                        return back()->with('error', 'Configure GekyChat Client ID and Client Secret above and save.');
                    }
                    if (empty($testPhone)) {
                        return back()->with('error', 'Please enter a phone number to send the test to.');
                    }
                    $geky = app(GekyChatService::class);
                    $result = $geky->sendMessageByPhone($testPhone, $message, []);
                    if ($result['success'] ?? false) {
                        return back()->with('success', 'Test GekyChat message sent to ' . $testPhone . '.');
                    }
                    return back()->with('error', $result['error'] ?? 'GekyChat test failed.');
            }
        } catch (\Throwable $e) {
            Log::error('Notification test failed', ['channel' => $channel, 'error' => $e->getMessage()]);
            return back()->with('error', 'Test failed: ' . $e->getMessage());
        }

        return back()->with('error', 'Unknown channel.');
    }
}
