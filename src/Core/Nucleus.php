<?php

declare(strict_types=1);

namespace Nucleus\Core;

use Nucleus\Contracts\Http\NucleusResponseInterface;
use Nucleus\Http\Response;
use Nucleus\Routing\Router;
use Nucleus\Routing\RouteResolver;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The Nucleus kernel.
 *
 * Responsible for handling incoming requests:
 * - Dispatching the request to the router
 * - Running global and route-specific middlewares
 * - Resolving the final route action
 * - Returning a PSR-7 compatible response
 */
class Nucleus
{
    /** @var Application The application instance */
    protected Application $app;

    /** @var Router The router instance */
    protected Router $router;

    /** @var RouteResolver|null Used internally by the router to resolve actions */
    protected ?RouteResolver $resolver = null;

    /** @var array<string> Global middleware list */
    protected array $middlewares = [];

    /**
     * Create a new kernel instance.
     *
     * @param Application $app The main application instance
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->router = $app->getRouter();
        $this->middlewares = $app->getGlobalMiddlewares();
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param ServerRequestInterface $request The PSR-7 request
     * @return NucleusResponseInterface The framework response
     */
    public function handle(ServerRequestInterface $request): NucleusResponseInterface
    {
        // Ask router to dispatch the request and find a matching route
        $route = $this->router->dispatch($request);

        if (!$route) {
            // No route found → return a 404 response
            return Response::notFound();
        }

        // Combine global middlewares with route-specific ones
        $middlewares = array_merge($this->middlewares, $route->middlewares);

        // Build the middleware pipeline (LIFO order)
        $pipeline = array_reduce(
            array_reverse($middlewares),
            fn($next, $middleware) => fn($req) => (new $middleware())->handle($req, $next),
            fn($req) => $this->router->resolve($route->action, $req, $route->params)
        );

        // Execute the pipeline with the incoming request
        return $pipeline($request);
    }
}