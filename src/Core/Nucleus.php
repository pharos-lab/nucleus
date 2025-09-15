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
 * Responsible for handling incoming HTTP requests:
 * - Dispatching requests to the router
 * - Running global and route-specific middlewares in correct order
 * - Resolving the final route action
 * - Returning a PSR-7 compatible response
 */
class Nucleus
{
    /**
     * Application instance.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Router instance.
     *
     * @var Router
     */
    protected Router $router;

    /**
     * Route resolver (used internally by the router to resolve actions).
     *
     * @var RouteResolver|null
     */
    protected ?RouteResolver $resolver = null;

    /**
     * Global middlewares registered for the application.
     *
     * @var string[]
     */
    protected array $middlewares = [];

    /**
     * Nucleus constructor.
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
     * This method:
     * 1. Asks the router to dispatch the request and find a matching route
     * 2. Returns a 404 response if no route is found
     * 3. Combines global and route-specific middlewares
     * 4. Builds a middleware pipeline (LIFO order)
     * 5. Resolves the final route action
     * 6. Executes the pipeline and returns the response
     *
     * @param ServerRequestInterface $request The PSR-7 HTTP request
     * @return NucleusResponseInterface PSR-7 compatible response
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
            /**
             * @param callable $next The next middleware/action in the pipeline
             * @param string $middleware The middleware class name
             * @return callable Middleware wrapped around next callable
             */
            fn($next, $middleware) => fn($req) => (new $middleware())->handle($req, $next),
            fn($req) => $this->router->resolve($route->action, $req, $route->params)
        );

        // Execute the pipeline with the incoming request
        return $pipeline($request);
    }
}