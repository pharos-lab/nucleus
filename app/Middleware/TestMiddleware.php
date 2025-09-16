<?php

namespace App\Middleware;

use Nucleus\Contracts\MiddlewareInterface;
use Nucleus\Http\Request;
use Nucleus\Http\Response;

class TestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        // Action before passing to the next middleware/router
        // Example: logging, authentication, request modification

        $response = $next($request);

        // Action after the response is generated
        // Example: add headers, transform response

        //$response->setHeader('X-Example', 'Middleware executed');

        return $response;
    }
}
