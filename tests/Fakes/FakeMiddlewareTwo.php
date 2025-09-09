<?php

namespace Tests\Fakes;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;

class FakeMiddlewareTwo implements MiddlewareInterface
{
    public function handle(Request $request, Callable $next):Response
    {
        $response = $next($request);
        $response->setBody('[two]' . $response->getBody());
        return $response;
    }
}