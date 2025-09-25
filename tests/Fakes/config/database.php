<?php

use Nucleus\Config\Environment;

return [
    'connections' => [
        'mysql' => [
            'host' => Environment::get('DB_HOST', '168.148.2.254'),
            'user' => Environment::get('DB_USER', 'root'),
            'password' => Environment::get('DB_PASS', 'root'),
        ],
    ],
];