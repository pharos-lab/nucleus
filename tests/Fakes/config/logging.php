<?php

use Nucleus\Config\Environment;

return [
    'driver' => Environment::get('LOG_DRIVER', 'single'),
    
    'drivers' => [
        'single' => [
            'path' => 'storage/logs/app.log',
            'level' => Environment::get('LOG_LEVEL', 'debug'),
        ],
        'daily' => [
            'path' => 'storage/logs',
            'level' => Environment::get('LOG_LEVEL', 'debug'),
            'days' => Environment::get('LOG_DAYS', 14),
        ],
    ]
    
];