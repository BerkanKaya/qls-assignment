<?php

return [
    'base_url' => env('QLS_API_BASE_URL', 'https://api.pakketdienstqls.nl'),
    'username' => env('QLS_API_USERNAME'),
    'password' => env('QLS_API_PASSWORD'),
    'company_id' => env('QLS_COMPANY_ID'),
    'brand_id' => env('QLS_BRAND_ID'),
    'product_combination_id' => env('QLS_DEFAULT_PRODUCT_COMBINATION_ID'),
    'timeout' => (int) env('QLS_API_TIMEOUT', 10),
];
