<?php

use App\Controllers\TestController;
use Nucleus\Http\Response;

// Closure simple
// Controller@method
$router->get('/home', [TestController::class, 'index']);
$router->get('/api', [TestController::class, 'api']);

$router->get('/{id}', function ($id) {
    return 'Hello world from Nucleus 🚀' . $id;
})->middleware([App\Middleware\RouteMiddleware::class])
->where(['id' => '[0-9]+']);


$router->get('/json', function () {
    return Response::json(['message' => 'Hello JSON 🚀']);
});

$router->get('/text', function () {
    return 'Simple texte';
});

$router->get('/text/{id}/test/{post}', [TestController::class, 'param']);