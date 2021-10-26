<?php

return
    [
        'default' => env('DB_CONNECTION', 'mysql'),
        'migrations' => 'migrations',
        'connections' => [
            'mysql' => [
                'read' => [
                    'host' => env('DB_HOST'),
                ],
                'write' => [
                    'host' => env('DB_HOST'),
                ],
                'url' => env('DATABASE_URL'),
                'driver'    => 'mysql',
                'database'  => env('DB_DATABASE'),
                'username'  => env('DB_USERNAME'),
                'password'  => env('DB_PASSWORD'),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
                'strict' => true,
                'engine' => null,
                'options' => extension_loaded('pdo_mysql') ? array_filter([
                    PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                ]) : [],
            ],
        ],
        'redis' => [

            'client' => env('REDIS_CLIENT', 'predis'),

            'default' => [
                'url' => env('REDIS_URL'),
                'host' => env('REDIS_HOST', '127.0.0.1'),
                'password' => env('REDIS_PASSWORD', null),
                'port' => env('REDIS_PORT', '6379'),
                'database' => env('REDIS_DB', '9'),
            ],
        ],
    ];

