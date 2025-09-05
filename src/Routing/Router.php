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

        if (isset($this->routes[$method][$path])) {
            $action = $this->routes[$method][$path];

            // Closure simple → injection possible
            if (is_callable($action)) {
                return $action($request);
            }

            // Controller + méthode
            if (is_array($action) && count($action) === 2) {
                [$controller, $method] = $action;
                $controller = new $controller();
                return $controller->$method($request);
            }
        }

        // Fallback 404
        return Response::notFound();
    }
}