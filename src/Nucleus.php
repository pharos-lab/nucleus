<?php

declare(strict_types=1);

namespace Nucleus;

use Nucleus\Contracts\Http\NucleusResponseInterface;
use Nucleus\Http\Response;
use Nucleus\Routing\Router;
use Nucleus\Routing\RouteResolver;
use Psr\Http\Message\ServerRequestInterface;

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

    public function handle(ServerRequestInterface $request): NucleusResponseInterface
    {
        // Dispatch route to get matched route object
        $route = $this->router->dispatch($request);

        if (!$route) {
            return Response::notFound(); // 404
        }

        // Merge global + route-specific middlewares
        $middlewares = array_merge($this->middlewares, $route->middlewares);

        // Build middleware pipeline
        $pipeline = array_reduce(
            array_reverse($middlewares),
            fn($next, $middleware) => fn($req) => (new $middleware())->handle($req, $next),
            fn($req) => RouteResolver::resolve($route->action, $req, $route->params)
        );

        // Run the pipeline starting with the request
        return $pipeline($request);
    }
}