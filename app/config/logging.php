<?php

use Nucleus\Config\Environment;

return [
    'driver' => Environment::get('LOG_DRIVER', 'daily'),
    
    'drivers' => [
        'single' => [
            'path' => Environment::get('LOG_PATH', 'storage/logs/app.log'),
            'level' => Environment::get('LOG_LEVEL', 'debug'),
        ],
        'daily' => [
            'path' => Environment::get('LOG_PATH', 'storage/logs'),
            'level' => Environment::get('LOG_LEVEL', 'debug'),
            'days' => Environment::get('LOG_DAYS', 14),
        ],
    ]
    
];