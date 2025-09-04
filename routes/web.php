<?php

use App\Controllers\TestController;

// Closure simple
$router->get('/', function () {
    return 'Hello world from Nucleus 🚀';
});

// Controller@method
$router->get('/home', [TestController::class, 'index']);
$router->get('/about', [TestController::class, 'about']);
