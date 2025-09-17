<?php

use Nucleus\Config\Environment;

return [
    'driver' => Environment::get('LOG_DRIVER', 'file'),
    'path' => Environment::get('LOG_PATH', 'storage/logs/app.log'),
    'level' => Environment::get('LOG_LEVEL', 'debug'),
];