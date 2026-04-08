<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://rental.kathmandusightseeing.com/',
    ],

    'allowed_headers' => ['*'],

    'supports_credentials' => false,
];
