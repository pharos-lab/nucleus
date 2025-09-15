<?php

namespace Tests\Fakes\Middleware;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;

class FakeMiddlewareInterrupt implements MiddlewareInterface
{
    public function handle(Request $request, Callable $next): Response
    {
        MiddlewareLog::add('interupt action before');
        // Never call the next middleware, interrupting the pipeline
        return new Response('Blocked by middleware', 403);
    }
}