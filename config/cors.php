<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://rental.kathmandusightseeing.com',
        'https://unlimited-vehicle-rental-qkhde5.flutterflow.app',
        'https://app.vehiclerental.online',
        'https://vehiclerental.flutterflow.app',
        'http://localhost:3000',
        'http://192.168.101.59:3000'
    ],

    'allowed_headers' => ['*'],

    'supports_credentials' => false,
];
