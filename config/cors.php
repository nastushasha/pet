<?php

$origins = array_values(array_filter(array_map(
    static fn (string $s): string => trim($s),
    explode(',', env(
        'FRONTEND_URLS',
        'http://localhost:5173,http://127.0.0.1:5173'
    ))
)));

return [

    'paths' => ['*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $origins ?: ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
