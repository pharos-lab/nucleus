<?php

use App\Providers\AppProvider;

return [
    // Path to the routes file
    'routes_path' => __DIR__ . '/../routes/web.php',

    // List of user-defined service providers
    'providers' => [
        AppProvider::class,
    ],

    // Other configuration options (future use)
    'env' => 'dev',
    'timezone' => 'UTC',
];