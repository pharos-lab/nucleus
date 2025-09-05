<?php

namespace Nucleus\Routing;

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
    public function dispatch(string $uri, string $method)
    {
        $uri = strtok($uri, '?');

        if (isset($this->routes[$method][$uri])) {
            $action = $this->routes[$method][$uri];

            // Closure simple
            if (is_callable($action)) {
                return $action();
            }

            // Controller@method
            if (is_array($action) && count($action) === 2) {
                [$controller, $method] = $action;
                $controller = new $controller();
                return $controller->$method();
            }
        }

        // Fallback 404
        return Response::notFound();
    }
}