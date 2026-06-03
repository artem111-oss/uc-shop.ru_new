<?php

return [
    // API URL
    'api_url' => env('PLATIMA_API_URL', 'https://platima.com/api/v1'),
    
    // API Key проекта (для подписи запросов)
    'api_key' => env('PLATIMA_API_KEY'),
    
    // Project ID (ID вашего проекта в Platima)
    'project_id' => env('PLATIMA_PROJECT_ID'),
    
    // Legacy параметры (не используются в новом API)
    'secret_key' => env('PLATIMA_SECRET_KEY', ''),
    'merchant_id' => env('PLATIMA_MERCHANT_ID', env('PLATIMA_PROJECT_ID')),
    'webhook_secret' => env('PLATIMA_WEBHOOK_SECRET', ''),
    
    // Валюта по умолчанию
    'currency' => env('PLATIMA_CURRENCY', 'RUB'),
    
    // Таймауты и повторные попытки
    'timeout' => env('PLATIMA_TIMEOUT', 30),
    'retry_attempts' => env('PLATIMA_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('PLATIMA_RETRY_DELAY', 1000),
];
