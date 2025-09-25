<?php

use Tests\Fakes\FakeUserProvider;
use Nucleus\Config\Environment;

return [
    'routes_path' => __DIR__ . '/../routes/web.php',

    'providers' => [
        FakeUserProvider::class,
    ],

    'env' => Environment::get('APP_ENV', 'local'),
    'timezone' => Environment::get('APP_TIMEZONE', 'UTC'),
];