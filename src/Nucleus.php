<?php

namespace Nucleus;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Routing\Router;

class Nucleus
{
    protected Router $router;

    /** @var array<string> Global middleware list */
    protected array $middlewares = [];

    public function __construct(Router $router, array $middlewares = [])
    {
        $this->router = $router;
        $this->middlewares = $middlewares;
    }

    public function handle(Request $request): Response
    {
        // Build the middleware pipeline
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn($next, $middleware) => function ($req) use ($middleware, $next) {
                $instance = new $middleware();
                return $instance->handle($req, $next);
            },
            fn($req) => $this->router->dispatch($req) // Last step: router
        );

        // Run the pipeline starting with the request
        return $pipeline($request);
    }
}