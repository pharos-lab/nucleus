<?php

namespace Tests\Fakes;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;

class FakeMiddlewareOne implements MiddlewareInterface
{
    public function handle(Request $request, Callable $next): Response
    {
        $response = $next($request);
        $response->setBody('[one]' . $response->getBody());
        return $response;
    }
}