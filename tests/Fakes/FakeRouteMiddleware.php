<?php

namespace Tests\Fakes;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;

class FakeRouteMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, $next): Response
    {
        $response = $next($request);
        $response->setBody('modified');
        return $response;
    }
}