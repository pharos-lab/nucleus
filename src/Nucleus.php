<?php

namespace Nucleus;

use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Routing\Dispatcher;
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
        // Dispatch route to get matched route object
        $result = $this->router->dispatch($request);

        if ($result instanceof Response) {
            return $result; // 404
        }

        $route = $result;

        // Merge global + route-specific middlewares
        $middlewares = array_merge($this->middlewares, $route->middlewares);

        // Build middleware pipeline
        $pipeline = array_reduce(
            array_reverse($middlewares),
            fn($next, $middleware) => fn($req) => (new $middleware())->handle($req, $next),
            fn($req) => Dispatcher::dispatch($route->action, $req, $route->params)
        );


        // Run the pipeline starting with the request
        return $pipeline($request);
    }
}