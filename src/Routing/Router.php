<?php

namespace Nucleus\Routing;

use Nucleus\Http\Request;
use Nucleus\Http\Response;

class Router 
{
     protected $routes = [
        "GET" => [],
        "POST" => []
    ];

    /**
     * Enregistrer une route GET
     */
    public function get(string $uri, $action): Route
    {
        $route = new Route('GET', $uri, $action);
        $this->routes['GET'][] = $route;
        return $route; 
    }

    /**
     * Enregistrer une route POST
     */
    public function post(string $uri, $action): Route
    {
        $route = new Route('POST', $uri, $action);
        $this->routes['POST'][] = $route;
        return $route;
    }

    /**
     * Résoudre et exécuter la route
     */
    public function dispatch(Request $request)
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes[$method] as $route) {
            // Transform path into regex
            $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $route->path);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                // extract parameters from route
                $route->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return $route;
            }
        }
    }
}