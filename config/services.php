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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | Configure SMS providers and settings for the Hospital Queue Management
    | System. Supports multiple providers with easy switching.
    |
    */

    'sms' => [
        'default_provider' => env('SMS_PROVIDER', 'semaphore'),
        'rate_limit_enabled' => env('SMS_RATE_LIMIT_ENABLED', false),
        'rate_limit_per_hour' => env('SMS_RATE_LIMIT_PER_HOUR', 5),
        'blacklist_enabled' => env('SMS_BLACKLIST_ENABLED', false),
        'blacklist_threshold' => env('SMS_BLACKLIST_THRESHOLD', 10),
        'blacklist_period_days' => env('SMS_BLACKLIST_PERIOD_DAYS', 30),
    ],

    'semaphore' => [
        'api_key' => env('SEMAPHORE_API_KEY'),
        'sender_name' => env('SEMAPHORE_SENDER_NAME', 'HQMS'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_FROM_NUMBER'),
    ],

    'movider' => [
        'api_key' => env('MOVIDER_API_KEY'),
        'api_secret' => env('MOVIDER_API_SECRET'),
    ],

    'm360' => [
        'username' => env('M360_USERNAME'),
        'password' => env('M360_PASSWORD'),
        'shortcode' => env('M360_SHORTCODE'),
    ],

];
