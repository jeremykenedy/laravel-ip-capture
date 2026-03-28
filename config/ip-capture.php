<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IP Capture Enabled
    |--------------------------------------------------------------------------
    */

    'enabled' => env('IP_CAPTURE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Null IP Address
    |--------------------------------------------------------------------------
    |
    | The value to store when an IP address cannot be determined.
    |
    */

    'null_ip' => env('IP_CAPTURE_NULL_IP', '0.0.0.0'),

    /*
    |--------------------------------------------------------------------------
    | IP Columns
    |--------------------------------------------------------------------------
    |
    | Define which IP columns to add to your model's table.
    | Each key is the column name, value is whether it is enabled.
    |
    */

    'columns' => [
        'signup_ip_address' => true,
        'signup_confirmation_ip_address' => true,
        'signup_sm_ip_address' => true,
        'admin_ip_address' => true,
        'updated_ip_address' => true,
        'deleted_ip_address' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | Use trusted proxy headers to determine the real client IP.
    |
    */

    'trust_proxies' => env('IP_CAPTURE_TRUST_PROXIES', true),

    /*
    |--------------------------------------------------------------------------
    | Hash IPs
    |--------------------------------------------------------------------------
    |
    | When enabled, IP addresses are hashed before storage for privacy.
    |
    */

    'hash' => env('IP_CAPTURE_HASH', false),

    /*
    |--------------------------------------------------------------------------
    | Hash Algorithm
    |--------------------------------------------------------------------------
    */

    'hash_algo' => env('IP_CAPTURE_HASH_ALGO', 'sha256'),

];
