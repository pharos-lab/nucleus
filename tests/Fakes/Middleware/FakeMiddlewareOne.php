<?php

namespace Tests\Fakes\Middleware;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;
use Nucleus\Http\Stream;

class FakeMiddlewareOne implements MiddlewareInterface
{
    public function handle(Request $request, Callable $next): Response
    {
        MiddlewareLog::add('one action before');

        $response = $next($request);

        MiddlewareLog::add('one action after');

        return $response->withBody(new Stream('[one]' . (string) $response->getBody()));
    }
}