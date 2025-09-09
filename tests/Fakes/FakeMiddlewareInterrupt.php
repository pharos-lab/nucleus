<?php

namespace Tests\Fakes;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Contracts\MiddlewareInterface;

class FakeMiddlewareInterrupt implements MiddlewareInterface
{
    public function handle(Request $request, Callable $next): Response
    {
        // Ne jamais appeler $next
        return new Response('Blocked by middleware', 403);
    }
}