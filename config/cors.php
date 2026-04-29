<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://rental.kathmandusightseeing.com/',
        'https://unlimited-vehicle-rental-qkhde5.flutterflow.app'
    ],

    'allowed_headers' => ['*'],

    'supports_credentials' => false,
];
