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

    'tracking' => [
        'routes' => [
            'consent' => '/tracking-consent',
            'collect' => '/tracking-collect',
        ],
        'field_name' => 'data[blijwin_t_info]',
        'heartbeat_seconds' => 30,
        'cookie_days' => 30,
        'session_minutes' => 30,
        'cookies' => [
            'consent' => 'tw_consent',
            'visitor' => 'tw_vid',
            'session' => 'tw_sid',
            'activity' => 'tw_last_seen',
        ],
        'consent' => [
            'cookie_days' => 180,
            'identifier_cookie_category' => 'analytics',
            'categories' => [
                'necessary' => [
                    'label' => 'Noodzakelijk',
                    'description' => 'Nodig om cookiekeuzes en server-side sessiecontext betrouwbaar te laten werken.',
                    'required' => true,
                ],
                'analytics' => [
                    'label' => 'Statistiek / analytics',
                    'description' => 'Helpt om bezoek, contactmomenten en formuliergebruik te begrijpen en te verbeteren.',
                    'required' => false,
                ],
                'marketing' => [
                    'label' => 'Marketing en externe inhoud',
                    'description' => 'Reserveert toestemming voor latere externe scripts en embeds.',
                    'required' => false,
                ],
            ],
        ],
    ],

    'downloads' => [
        'routes' => [
            'direct' => '/downloads/file',
            'secure_request' => '/downloads/api/request-email',
            'secure_delivery' => '/downloads/secure',
        ],
        'secure' => [
            'enabled' => true,
            'token_ttl_hours' => 48,
            'honeypot_field' => 'website_url',
            'form_token_session_key' => 'download_secure_form_tokens',
            'min_submit_seconds' => 4,
            'rate_limits' => [
                'per_minute' => 2,
                'per_hour' => 6,
            ],
            'mail_subject' => 'Je download van Blijwin staat klaar',
        ],
        'presentation' => [
            'open_external_in_new_tab' => true,
            'download_local_files' => true,
        ],
    ],
];
