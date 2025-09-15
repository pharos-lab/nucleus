<?php

namespace Tests\Fakes\Middleware;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;
use Nucleus\Http\Stream;

class FakeMiddlewareTwo implements MiddlewareInterface
{

    public function handle(Request $request, Callable $next):Response
    {
        MiddlewareLog::add('two action before');

        $response = $next($request);

        MiddlewareLog::add('two action after');

        return $response->withBody(new Stream('[two]' . (string) $response->getBody()));
    }
}