<?php

// Autoload all classes via Composer
require __DIR__ . '/../../vendor/autoload.php';

use Nucleus\Core\Application;

// Initialize the application with the base path
$app = new Application(dirname(__DIR__));

// Run the application (handles the incoming HTTP request)
$app->run();