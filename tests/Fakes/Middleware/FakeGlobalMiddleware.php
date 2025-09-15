<?php

namespace Tests\Fakes\Middleware;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;

class FakeGlobalMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, $next): Response
    {
        MiddlewareLog::add('global action before');
        $response = $next($request);
        return $response->withHeader('X-Global', 'true');
    }
}