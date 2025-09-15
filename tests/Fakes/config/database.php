<?php

use Nucleus\Config\Environment;

return [
    'connections' => [
        'mysql' => [
            'host' => Environment::get('DB_HOST', '127.0.0.1'),
            'user' => Environment::get('DB_USER', 'root'),
            'password' => Environment::get('DB_PASS', 'root'),
        ],
    ],
];