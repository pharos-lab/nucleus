<?php

declare(strict_types=1);

namespace Nucleus\Routing;

use Nucleus\Contracts\Http\NucleusRequestInterface;
use Nucleus\Contracts\Http\NucleusResponseInterface;
use Nucleus\Contracts\RouterInterface;

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
}
