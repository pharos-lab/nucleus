<?php

declare(strict_types=1);

namespace Nucleus\Routing;

use Nucleus\Contracts\Http\NucleusRequestInterface;
use Nucleus\Contracts\Http\NucleusResponseInterface;
use Nucleus\Contracts\RouterInterface;
use Nucleus\Exceptions\RouteNamedNotFindException;
use Nucleus\Exceptions\RouteNamedParametersException;

class Router implements RouterInterface
{
    protected RouteResolver $resolver;

    public function __construct(RouteResolver $resolver)
    {
        $this->resolver = $resolver;
    }   

    protected array $routes = [
        "GET" => [],
        "POST" => []
    ];

    /**
     * Register GET route
     */
    public function get(string $uri, $action): Route
    {
        $route = new Route('GET', $uri, $action);
        $this->routes['GET'][] = $route;
        return $route;
    }

    /**
     * Register POST route
     */
    public function post(string $uri, $action): Route
    {
        $route = new Route('POST', $uri, $action);
        $this->routes['POST'][] = $route;
        return $route;
    }

    /**
     * Dispatch PSR-7 request to matching route
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

    public function resolve($action, NucleusRequestInterface $request, array $params = []): NucleusResponseInterface
    {
        return $this->resolver->resolve($action, $request, $params);
    }

    protected function getRoutesForMethod(string $method): array
    {
        return $this->routes[$method] ?? [];
    }

    protected function match(Route $route, string $path): bool
    {
        $pattern = $this->getRegexFromPath($route->path);
        return preg_match($pattern, $path) === 1;
    }

    protected function extractParams(Route $route, string $path): array
    {
        $pattern = $this->getRegexFromPath($route->path);
        preg_match($pattern, $path, $matches);
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    protected function validateConstraints(Route $route, array $params): bool
    {
        foreach ($route->constraints as $param => $regex) {
            if (isset($params[$param]) && !preg_match("#$regex#", $params[$param])) {
                return false;
            }
        }
        return true;
    }

    protected function getRegexFromPath(string $path): string
    {
        $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

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
