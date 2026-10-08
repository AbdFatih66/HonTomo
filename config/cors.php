<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | Atur origin frontend yang boleh mengakses API bila dilayani dari
    | domain berbeda. Untuk sekarang SPA dilayani dari origin yang sama
    | (application.blade.php), jadi ini terutama dokumentasi + pengaman
    | bila API dikonsumsi lintas origin di masa depan.
    |
    | Set FRONTEND_URL di .env bila frontend pindah ke domain sendiri,
    | mis. FRONTEND_URL=https://app.hontomo.id
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(array_map('trim', explode(',', env('FRONTEND_URL', '')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
