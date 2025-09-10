<?php

use Tests\Fakes\Controller\TestController;
use Nucleus\Http\Response;

// Closure simple
// Controller@method
$router->get('/test-route-without-middleware', [TestController::class, 'routeWithoutMiddleware']);