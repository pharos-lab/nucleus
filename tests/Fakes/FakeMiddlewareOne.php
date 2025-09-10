<?php

namespace Tests\Fakes;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;
use Nucleus\Http\Stream;

class FakeMiddlewareOne implements MiddlewareInterface
{
    public function handle(Request $request, Callable $next): Response
    {
        $response = $next($request);
        return $response->withBody(new Stream('[one]' . (string) $response->getBody()));
    }
}