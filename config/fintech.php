<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Drivers
    |--------------------------------------------------------------------------
    | Supported: "paystack", "flutterwave", "monnify"
    */
    'payment' => [
        'default'  => env('FINTECH_PAYMENT_DRIVER', 'paystack'),
        'fallback' => env('FINTECH_PAYMENT_FALLBACK', null),

        'drivers' => [
            'paystack' => [
                'enabled'    => env('PAYSTACK_ENABLED', true),
                'secret_key' => env('PAYSTACK_SECRET_KEY', ''),
                'public_key' => env('PAYSTACK_PUBLIC_KEY', ''),
                'base_url'   => 'https://api.paystack.co',
            ],

            'flutterwave' => [
                'enabled'    => env('FLUTTERWAVE_ENABLED', false),
                'secret_key' => env('FLW_SECRET_KEY', ''),
                'public_key' => env('FLW_PUBLIC_KEY', ''),
                'base_url'   => 'https://api.flutterwave.com/v3',
            ],

            'monnify' => [
                'enabled'     => env('MONNIFY_ENABLED', false),
                'api_key'     => env('MONNIFY_API_KEY', ''),
                'secret_key'  => env('MONNIFY_SECRET_KEY', ''),
                'contract_code' => env('MONNIFY_CONTRACT_CODE', ''),
                'base_url'    => env('MONNIFY_BASE_URL', 'https://sandbox.monnify.com'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Airtime & Data Drivers
    |--------------------------------------------------------------------------
    | Supported: "vtpass", "clubkonnect", "nellobytes"
    */
    'airtime' => [
        'default' => env('FINTECH_AIRTIME_DRIVER', 'vtpass'),

        'drivers' => [
            'vtpass' => [
                'enabled'  => env('VTPASS_ENABLED', false),
                'email'    => env('VTPASS_EMAIL', ''),
                'password' => env('VTPASS_PASSWORD', ''),
                'base_url' => env('VTPASS_BASE_URL', 'https://sandbox.vtpass.com/api'),
            ],

            'clubkonnect' => [
                'enabled'  => env('CLUBKONNECT_ENABLED', false),
                'user_id'  => env('CLUBKONNECT_USER_ID', ''),
                'api_key'  => env('CLUBKONNECT_API_KEY', ''),
                'base_url' => 'https://www.clubkonnect.com/api/v1',
            ],

            'nellobytes' => [
                'enabled'  => env('NELLOBYTES_ENABLED', false),
                'api_key'  => env('NELLOBYTES_API_KEY', ''),
                'base_url' => 'https://nellobytes.com/api',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bills — Electricity, Cable TV
    |--------------------------------------------------------------------------
    | Supported: "vtpass", "buypower", "baxi"
    */
    'bills' => [
        'default' => env('FINTECH_BILLS_DRIVER', 'vtpass'),

        'drivers' => [
            'vtpass' => [
                'enabled'  => env('VTPASS_ENABLED', false),
                'email'    => env('VTPASS_EMAIL', ''),
                'password' => env('VTPASS_PASSWORD', ''),
                'base_url' => env('VTPASS_BASE_URL', 'https://sandbox.vtpass.com/api'),
            ],

            'buypower' => [
                'enabled'  => env('BUYPOWER_ENABLED', false),
                'api_key'  => env('BUYPOWER_API_KEY', ''),
                'base_url' => env('BUYPOWER_BASE_URL', 'https://api.buypower.ng/v1'),
            ],

            'baxi' => [
                'enabled'  => env('BAXI_ENABLED', false),
                'api_key'  => env('BAXI_API_KEY', ''),
                'base_url' => 'https://services.baxipay.com.ng/baxi',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Identity & KYC Drivers
    |--------------------------------------------------------------------------
    | Supported: "mono", "okra", "prembly"
    */
    'identity' => [
        'default' => env('FINTECH_IDENTITY_DRIVER', 'mono'),

        'drivers' => [
            'mono' => [
                'enabled'    => env('MONO_ENABLED', false),
                'secret_key' => env('MONO_SECRET_KEY', ''),
                'base_url'   => 'https://api.withmono.com',
            ],

            'okra' => [
                'enabled'  => env('OKRA_ENABLED', false),
                'token'    => env('OKRA_TOKEN', ''),
                'base_url' => 'https://api.okra.ng/v2',
            ],

            'prembly' => [
                'enabled'  => env('PREMBLY_ENABLED', false),
                'api_key'  => env('PREMBLY_API_KEY', ''),
                'app_id'   => env('PREMBLY_APP_ID', ''),
                'base_url' => 'https://api.prembly.com',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Banking Drivers
    |--------------------------------------------------------------------------
    */
    'banking' => [
        'default' => env('FINTECH_BANKING_DRIVER', 'paystack'),
    ],

];