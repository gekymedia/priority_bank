<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'priority_bank' => [
        'api_url' => env('PRIORITY_BANK_API_URL', 'http://localhost:8000'),
        'api_token' => env('PRIORITY_BANK_API_TOKEN'),
        'timeout' => env('PRIORITY_BANK_API_TIMEOUT', 10),
        'max_retries' => env('PRIORITY_BANK_API_MAX_RETRIES', 3),
    ],

    'hubtel' => [
        'client_id' => env('HUBTEL_CLIENT_ID'),
        'client_secret' => env('HUBTEL_CLIENT_SECRET'),
        'api_key' => env('HUBTEL_API_KEY'),
        'api_secret' => env('HUBTEL_API_SECRET'),
        'from' => env('HUBTEL_FROM', 'PriorityBank'),
        'merchant_account_number' => env('HUBTEL_MERCHANT_ACCOUNT_NUMBER'),
    ],

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
    ],

    'gekychat' => [
        // Platform API is on api subdomain, not chat subdomain
        // Routes are at: api.gekychat.test/platform/oauth/token
        // So base_url should be just the domain (no /api prefix)
        'base_url' => env('GEKYCHAT_API_URL', env('APP_ENV') === 'local' ? 'http://api.gekychat.test' : 'https://api.gekychat.com'),
        'client_id' => env('GEKYCHAT_CLIENT_ID'),
        'client_secret' => env('GEKYCHAT_CLIENT_SECRET'),
        'system_bot_user_id' => (int) env('GEKYCHAT_SYSTEM_BOT_USER_ID', 0),
        // Merchant account - Priority Bank user ID that receives Sika coin purchase funds
        'merchant_user_id' => (int) env('GEKYCHAT_MERCHANT_USER_ID', 1),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),
        'project' => env('OPENAI_PROJECT'),
    ],

    'whatsapp' => [
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'api_token' => env('WHATSAPP_API_TOKEN'),
        'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com'),
    ],

    'arkesel' => [
        'api_key' => env('ARKESEL_API_KEY'),
        'url' => env('ARKESEL_API_URL', 'https://sms.arkesel.com/sms/api'),
        'sender' => env('ARKESEL_SENDER', 'PriorityBank'),
        'balance_url' => env('ARKESEL_BALANCE_URL', 'https://sms.arkesel.com/api/v2/clients/balance-details'),
    ],

];
