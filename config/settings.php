<?php

$isTesting = ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? null) === 'testing';

return [
    'app' => [
        'name' => 'Blijwin Content CMS',
        'url' => 'http://localhost',
        'locale' => 'nl',
        'fallback_locale' => 'nl',
        'faker_locale' => 'nl_NL',
        'maintenance_driver' => 'file',
        'maintenance_store' => 'database',
    ],

    'auth' => [
        'guard' => 'web',
        'password_broker' => 'users',
        'password_timeout' => 10800,
    ],

    'cache' => [
        'default_store' => $isTesting ? 'array' : 'database',
        'prefix' => 'blijwincontent-cache-',
    ],

    'filesystem' => [
        'default_disk' => 'local',
        'public_url_path' => '/storage',
    ],

    'logging' => [
        'default_channel' => 'stack',
        'stack' => ['single'],
        'level' => 'debug',
        'daily_days' => 14,
        'deprecations_channel' => 'null',
        'deprecations_trace' => false,
    ],

    'mail' => [
        'default_mailer' => 'log',
        'from_address' => 'hello@example.com',
        'from_name' => 'Blijwin Content CMS',
        'smtp' => [
            'scheme' => null,
            'url' => null,
            'host' => '127.0.0.1',
            'port' => 2525,
            'username' => null,
            'password' => null,
            'local_domain' => 'localhost',
        ],
        'sendmail_path' => '/usr/sbin/sendmail -bs -i',
    ],

    'queue' => [
        'default_connection' => 'database',
        'default_queue' => 'default',
        'retry_after' => 90,
        'failed_driver' => 'database-uuids',
    ],

    'session' => [
        'driver' => 'database',
        'lifetime' => 120,
        'expire_on_close' => false,
        'encrypt' => false,
        'table' => 'sessions',
        'path' => '/',
        'domain' => null,
        'secure_cookie' => null,
        'http_only' => true,
        'same_site' => 'lax',
        'partitioned' => false,
    ],

    'services' => [
        'aws_region' => 'us-east-1',
    ],
];
