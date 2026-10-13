<?php

/*
|--------------------------------------------------------------------------
| Database Configuration
|--------------------------------------------------------------------------
| DATABASE_URL is parsed via parse_url() so that Docker Compose, Railway,
| and Render can all inject a single connection string without extra env vars.
| If DATABASE_URL is not set, individual DB_* vars are used as fallback.
*/

$url = env('DATABASE_URL');
$db  = $url ? parse_url($url) : [];

return [

    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        'pgsql' => [
            'driver'         => 'pgsql',
            'url'            => null,          // We parse manually — do not use Laravel's url key
            'host'           => isset($db['host'])     ? $db['host']            : env('DB_HOST', '127.0.0.1'),
            'port'           => isset($db['port'])     ? (string) $db['port']   : env('DB_PORT', '5432'),
            'database'       => isset($db['path'])     ? ltrim($db['path'], '/') : env('DB_DATABASE', 'library'),
            'username'       => isset($db['user'])     ? $db['user']            : env('DB_USERNAME', 'library'),
            'password'       => isset($db['pass'])     ? $db['pass']            : env('DB_PASSWORD', ''),
            'charset'        => 'utf8',
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => 'library',
            'sslmode'        => 'prefer',
        ],

    ],

    'migrations' => [
        'table'    => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'default' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
    ],

];
