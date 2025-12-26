<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VAPID (Voluntary Application Server Identification) Keys
    |--------------------------------------------------------------------------
    |
    | VAPID keys are used to identify your application server to push services.
    | Generate these using: vendor/bin/web-push generate-vapid-keys
    | Or use the setup-push-notifications.sh script
    |
    */

    'vapid' => [
        'subject' => env('APP_URL', 'https://storeflow.com.au'),
        'public_key' => env('VAPID_PUBLIC_KEY'),
        'private_key' => env('VAPID_PRIVATE_KEY'),
        'pem_file' => env('VAPID_PEM_FILE'), // Optional
    ],

    /*
    |--------------------------------------------------------------------------
    | Push Notification Settings
    |--------------------------------------------------------------------------
    */

    'timeout' => 30, // Timeout in seconds for push requests
    'TTL' => 3600, // Time to live in seconds
    'urgency' => 'normal', // Can be 'very-low', 'low', 'normal', or 'high'
    'topic' => 'storeflow-notifications',
];
