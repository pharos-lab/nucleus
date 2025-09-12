<?php

declare(strict_types=1);

namespace Nucleus\Routing;

use Nucleus\Contracts\Http\NucleusRequestInterface;
use Nucleus\Contracts\Http\NucleusResponseInterface;
use Nucleus\Contracts\RouterInterface;
use Nucleus\Exceptions\RouteNamedNotFindException;
use Nucleus\Exceptions\RouteNamedParametersException;

/**
 * Router class.
 *
 * Responsible for:
 * - Registering routes (GET/POST)
 * - Matching incoming requests against defined routes
 * - Extracting route parameters
 * - Validating parameter constraints
 * - Resolving actions
 * - Generating URLs from named routes
 */
class Router implements RouterInterface
{
    /** @var RouteResolver The resolver to execute route actions */
    protected RouteResolver $resolver;

    /** @var array<string, Route[]> Registered routes grouped by HTTP method */
    protected array $routes = [
        "GET" => [],
        "POST" => []
    ];

    /**
     * Create a new Router instance.
     */
    public function __construct(RouteResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Register a GET route.
     *
     * @param string $uri    The URI pattern
     * @param mixed  $action The route handler (closure, controller@method, etc.)
     * @return Route
     */
    public function get(string $uri, $action): Route
    {
        $route = new Route('GET', $uri, $action);
        $this->routes['GET'][] = $route;
        return $route;
    }

    /**
     * Register a POST route.
     *
     * @param string $uri    The URI pattern
     * @param mixed  $action The route handler
     * @return Route
     */
    public function post(string $uri, $action): Route
    {
        $route = new Route('POST', $uri, $action);
        $this->routes['POST'][] = $route;
        return $route;
    }

    /**
     * Dispatch a PSR-7 request and return the matched route.
     *
     * @param NucleusRequestInterface $request
     * @return Route|null Matched route or null if not found
     */
    public function dispatch(NucleusRequestInterface $request): ?Route
    {
        $method = strtoupper($request->getMethod());
        $path = $request->getUri()->getPath();

        foreach ($this->getRoutesForMethod($method) as $route) {
            if (!$this->match($route, $path)) {
                continue;
            }

            $params = $this->extractParams($route, $path);
            if (!$this->validateConstraints($route, $params)) {
                continue;
            }

            $route->params = $params;
            return $route;
        }

        return null;
    }

    /**
     * Resolve and execute a route action.
     */
    public function resolve($action, NucleusRequestInterface $request, array $params = []): NucleusResponseInterface
    {
        return $this->resolver->resolve($action, $request, $params);
    }

    /**
     * Get all routes for a given HTTP method.
     *
     * @return Route[]
     */
    protected function getRoutesForMethod(string $method): array
    {
        return $this->routes[$method] ?? [];
    }

    /**
     * Check if the route matches the request path.
     */
    protected function match(Route $route, string $path): bool
    {
        $pattern = $this->getRegexFromPath($route->path);
        return preg_match($pattern, $path) === 1;
    }

    /**
     * Extract parameters from the path based on placeholders.
     *
     * Example:
     * Route path: /user/{id}
     * Request path: /user/42
     * => ['id' => '42']
     */
    protected function extractParams(Route $route, string $path): array
    {
        $pattern = $this->getRegexFromPath($route->path);
        preg_match($pattern, $path, $matches);
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Validate route parameter constraints (e.g. {id} must be numeric).
     */
    protected function validateConstraints(Route $route, array $params): bool
    {
        foreach ($route->constraints as $param => $regex) {
            if (isset($params[$param]) && !preg_match("#$regex#", $params[$param])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Convert a route path with placeholders into a regex pattern.
     *
     * Example:
     * /user/{id} => #^/user/(?P<id>[^/]+)$#
     */
    protected function getRegexFromPath(string $path): string
    {
        $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Generate a URL from a named route.
     *
     * @param string $name   The route name
     * @param array  $params Parameters to replace in the route path
     * @throws RouteNamedNotFindException
     * @throws RouteNamedParametersException
     */
    public function routeUrl(string $name, array $params = []): string
    {
        $route = $this->findRouteByName($name);
        $this->validateRouteParameters($route, $params);
        return $this->buildUrlFromRoute($route, $params);
    }

    /**
     * Find a route by its name.
     *
     * @throws RouteNamedNotFindException
     */
    protected function findRouteByName(string $name): Route
    {
        foreach ($this->routes as $methodRoutes) {
            foreach ($methodRoutes as $route) {
                if ($route->name === $name) {
                    return $route;
                }
            }
        }

        throw new RouteNamedNotFindException("Route with name '{$name}' not found.");
    }

    /**
     * Validate provided parameters against expected placeholders.
     *
     * @throws RouteNamedParametersException
     */
    protected function validateRouteParameters(Route $route, array $params): void
    {
        preg_match_all('#\{(\w+)\}#', $route->path, $matches);
        $expectedParams = $matches[1];

        // Missing parameters
        $missing = array_diff($expectedParams, array_keys($params));
        if ($missing) {
            throw new RouteNamedParametersException(
                "Missing parameters for route '{$route->name}': " . implode(', ', $missing)
            );
        }

        // Extra parameters
        $extra = array_diff(array_keys($params), $expectedParams);
        if ($extra) {
            throw new RouteNamedParametersException(
                "Extra parameters provided for route '{$route->name}': " . implode(', ', $extra)
            );
        }
    }

    /**
     * Replace placeholders in the route path with actual values.
     */
    protected function buildUrlFromRoute(Route $route, array $params): string
    {
        $url = $route->path;
        foreach ($params as $key => $value) {
            $url = str_replace("{" . $key . "}", (string)$value, $url);
        }
        return $url;
    }
}