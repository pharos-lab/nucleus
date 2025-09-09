<?php

namespace Tests\Fakes;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;

class FakeGlobalMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, $next): Response
    {
        $response = $next($request);
        $response->setHeader('X-Global', 'true');
        return $response;
    }
}