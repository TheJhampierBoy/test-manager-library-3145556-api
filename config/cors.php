<?php

/*
|--------------------------------------------------------------------------
| CORS Configuration
|--------------------------------------------------------------------------
| CORS_ORIGIN must be set to the frontend URL (e.g. http://localhost:5173).
| Only the configured origin is allowed — spec §4 (api-contract.md).
*/

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter([env('CORS_ORIGIN')]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
