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
    public function get(string $uri, $action): void
    {
        $this->routes['GET'][$uri] = $action;
    }

    /**
     * Enregistrer une route POST
     */
    public function post(string $uri, $action): void
    {
        $this->routes['POST'][$uri] = $action;
    }

    /**
     * Résoudre et exécuter la route
     */
    public function dispatch(Request $request)
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes[$method] as $route => $action) {
            // Transform path into regex
            $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                // extract parameters from route
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                return Dispatcher::dispatch($action, $request, $params);
            }
        }

        // Fallback 404
        return Response::notFound();
    }
}