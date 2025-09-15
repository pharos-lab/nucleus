<?php

namespace Tests\Fakes\Middleware;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;

class FakeRouteMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, $next): Response
    {
        $response = $next($request);
        return $response->withHeader('X-Route', 'true')->withBody(new \Nucleus\Http\Stream('modified'));
    }
}