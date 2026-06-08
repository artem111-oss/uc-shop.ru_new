<?php

return [

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'platima' => [
        'api_key_project' => env('PLATIMA_API_KEY_PROJECT'),
        'project_id'      => env('PLATIMA_PROJECT_ID'),
        'base_url'        => env('PLATIMA_BASE_URL', 'https://platima.ru/api'),
    ],

    'pally' => [
        'shop_id'     => env('PALLY_SHOP_ID'),
        'token'       => env('PALLY_TOKEN'),
        'success_url' => env('PALLY_SUCCESS_URL'),
        'fail_url'    => env('PALLY_FAIL_URL'),
    ],

    'ragner' => [
        'key' => env('RAGNER_API_KEY'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id'   => env('TELEGRAM_CHAT_ID'),
    ],

];