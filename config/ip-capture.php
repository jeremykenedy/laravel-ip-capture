<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | IP Capture Enabled
    |--------------------------------------------------------------------------
    |
    | The master switch. When disabled, every capture resolves to the null IP
    | below and no model column is ever written to.
    |
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
        'signup_ip_address'              => true,
        'signup_confirmation_ip_address' => true,
        'signup_sm_ip_address'           => true,
        'admin_ip_address'               => true,
        'updated_ip_address'             => true,
        'deleted_ip_address'             => true,
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
    | Proxy Headers
    |--------------------------------------------------------------------------
    |
    | The server keys inspected, in order, when Laravel cannot determine the
    | client IP itself. The first key holding a valid address wins.
    |
    | Only headers set by a proxy you control should be listed here. Anything
    | in this list can be spoofed by a client talking to your app directly.
    |
    */

    'headers' => [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    ],

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

    /*
    |--------------------------------------------------------------------------
    | Hash Salt
    |--------------------------------------------------------------------------
    |
    | Prepended to the address before hashing so the digests in your database
    | cannot be reversed with a rainbow table of the whole IPv4 space.
    |
    | Changing the salt changes every digest produced from then on, so set it
    | once before you start storing hashes.
    |
    */

    'hash_salt' => env('IP_CAPTURE_HASH_SALT', ''),

    /*
    |--------------------------------------------------------------------------
    | Anonymize
    |--------------------------------------------------------------------------
    |
    | Mask the host part of the address before it is stored or hashed, the way
    | GDPR guidance and Google Analytics IP anonymization do it. IPv4 keeps
    | its first three octets, IPv6 keeps its first four groups.
    |
    */

    'anonymize' => env('IP_CAPTURE_ANONYMIZE', false),

    /*
    |--------------------------------------------------------------------------
    | Automatic Capture
    |--------------------------------------------------------------------------
    |
    | Map Eloquent model events to IP columns. The whole feature is off by
    | default so adding the trait never writes a column on its own. Set a
    | value to false to ignore that event, or name a different column.
    |
    | The deleting event is deliberately absent. A soft delete writes only
    | its own columns and a hard delete drops the row, so an address captured
    | there never reaches the database. Call setDeletedIp() and save the model
    | yourself when you need that column.
    |
    */

    'auto_capture' => [
        'enabled' => env('IP_CAPTURE_AUTO', false),

        'events' => [
            'creating' => 'signup_ip_address',
            'updating' => 'updated_ip_address',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    |
    | The table the bundled migration adds the IP columns to, the column it
    | places them after, and the column length. A hashed address needs more
    | room than an address: sha256 is 64 characters, sha512 is 128.
    |
    */

    'table' => env('IP_CAPTURE_TABLE', 'users'),

    'after_column' => env('IP_CAPTURE_AFTER_COLUMN', 'password'),

    'column_length' => (int) env('IP_CAPTURE_COLUMN_LENGTH', 64),

    /*
    |--------------------------------------------------------------------------
    | CSS Framework
    |--------------------------------------------------------------------------
    |
    | Which CSS framework the published Blade views are rendered with.
    | Supported: "tailwind", "bootstrap5", "bootstrap4"
    |
    */

    // Left unset when neither variable is present, so the accessor can fall
    // back to a laravel-ui-kit installation configured in config rather than
    // in the environment.
    'css_framework' => env('IP_CAPTURE_CSS', env('UI_KIT_CSS')),

    /*
    |--------------------------------------------------------------------------
    | Frontend
    |--------------------------------------------------------------------------
    |
    | Which frontend the IP table component is rendered with.
    | Supported: "blade", "livewire", "vue", "react", "svelte"
    |
    */

    'frontend' => env('IP_CAPTURE_FRONTEND', env('UI_KIT_FRONTEND')),

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | Registers the "ip-capture" view namespace and the Blade components. This
    | adds no routes and renders nothing until you place a component in one of
    | your own views. Set to false to leave the namespace unregistered.
    |
    */

    'views' => [
        'enabled' => env('IP_CAPTURE_VIEWS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Display
    |--------------------------------------------------------------------------
    |
    | How captured addresses are presented by the shipped components. Masking
    | shows an address as 203.0.113.xxx in the UI without changing what is
    | stored, which is useful for support screens.
    |
    */

    'display' => [
        'mask'        => env('IP_CAPTURE_DISPLAY_MASK', false),
        'empty_label' => env('IP_CAPTURE_DISPLAY_EMPTY', '-'),
    ],

];
