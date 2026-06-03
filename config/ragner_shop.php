<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ragner Shop API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Ragner Shop automation service
    |
    */

    'api_url' => env('RAGNER_SHOP_API_URL', 'https://api.ragner-shop.com'),
    'api_key' => env('RAGNER_SHOP_API_KEY'),
    'timeout' => env('RAGNER_SHOP_TIMEOUT', 30),
    
    // Автоматически выполнять заказ после успешной оплаты
    'auto_fulfill' => env('RAGNER_SHOP_AUTO_FULFILL', true),
];
