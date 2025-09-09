<?php

declare(strict_types=1);

namespace Nucleus\Routing;

use Nucleus\Contracts\RouterInterface;
use Nucleus\Http\Request;

class Router implements RouterInterface
{
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
     * Dispatch request to matching route
     */
    public function dispatch(Request $request): ?Route
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->getRoutesForMethod($method) as $route) {
            if (!$this->match($route, $path)) continue;

            $params = $this->extractParams($route, $path);
            if (!$this->validateConstraints($route, $params)) continue;

            $route->params = $params;
            return $route;
        }

        return null;
    }

    /**
     * Get all routes for a specific HTTP method
     */
    protected function getRoutesForMethod(string $method): array
    {
        return $this->routes[$method] ?? [];
    }

    /**
     * Check if route pattern matches request path
     */
    protected function match(Route $route, string $path): bool
    {
        $pattern = $this->getRegexFromPath($route->path);
        return preg_match($pattern, $path) === 1;
    }

    /**
     * Extract named parameters from path
     */
    protected function extractParams(Route $route, string $path): array
    {
        $pattern = $this->getRegexFromPath($route->path);

        preg_match($pattern, $path, $matches);
        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Validate route parameter constraints
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
     * Convert route path with {param} to regex
     */
    protected function getRegexFromPath(string $path): string
    {
        $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
}
