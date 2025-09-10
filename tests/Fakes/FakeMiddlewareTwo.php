<?php

namespace Tests\Fakes;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;
use Nucleus\Http\Stream;

class FakeMiddlewareTwo implements MiddlewareInterface
{
    public function handle(Request $request, Callable $next):Response
    {
        $response = $next($request);
        $response = $next($request);
        return $response->withBody(new Stream('[two]' . (string) $response->getBody()));
    }
}