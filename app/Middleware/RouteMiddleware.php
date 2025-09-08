<?php

namespace App\Middleware;

use Nucleus\Http\Request;
use Nucleus\Http\Response;

class RouteMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Action before passing to the next middleware/router
        // Example: logging, authentication, request modification
        var_dump('route Middleware executed');

        $response = $next($request);

        // Action after the response is generated
        // Example: add headers, transform response

        //$response->setHeader('X-Example', 'Middleware executed');

        return $response;
    }
}
