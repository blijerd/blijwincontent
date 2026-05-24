<?php

$settings = require __DIR__.'/settings.php';

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => null,
    ],

    'resend' => [
        'key' => null,
    ],

    'ses' => [
        'key' => null,
        'secret' => null,
        'region' => $settings['services']['aws_region'],
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => null,
            'channel' => null,
        ],
    ],

];
