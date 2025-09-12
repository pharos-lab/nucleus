<?php

declare(strict_types=1);

namespace Nucleus\Routing;

use Nucleus\Contracts\RouteInterface;

/**
 * Class Route
 *
 * Represents a single route definition within the routing system.
 * A route maps an HTTP method and URI pattern to an executable action.
 *
 * Each route may include:
 *  - HTTP method (GET, POST, etc.)
 *  - Path with optional placeholders (e.g., /users/{id})
 *  - Action (controller or callback to be executed)
 *  - Middlewares attached to the route
 *  - Name (used for route generation)
 *  - Constraints on parameters (regex validation)
 *  - Parameters extracted from the matched URI
 */
class Route implements RouteInterface
{
    /** @var string HTTP method (GET, POST, etc.) */
    public string $method;

    /** @var string Route path, may contain placeholders (e.g., /users/{id}) */
    public string $path;

    /** @var mixed Route action: callable, controller@method, etc. */
    public $action;

    /** @var array<string> List of middlewares specific to this route */
    public array $middlewares = [];

    /** @var string|null Unique route name for URL generation */
    public ?string $name = null;

    /** @var array<string,string> Parameter constraints (regex patterns) */
    public array $constraints = [];

    /** @var array<string,string> Parameters extracted during dispatch */
    public array $params = [];

    /**
     * Route constructor.
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path   Path pattern (may include placeholders like {id})
     * @param mixed  $action Action to execute (callable or controller reference)
     */
    public function __construct(string $method, string $path, $action)
    {
        $this->method = $method;
        $this->path = $path;
        $this->action = $action;
    }

    /**
     * Assign middlewares to this route.
     *
     * @param array<string> $middlewares List of middleware class names
     * @return self
     */
    public function middleware(array $middlewares): self
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    /**
     * Give a unique name to this route.
     *
     * @param string $name Route name
     * @return self
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Define regex constraints for route parameters.
     * Automatically strips regex delimiters if provided (e.g., "#[0-9]+#").
     *
     * @param array<string,string> $constraints Map of parameter => regex
     * @return self
     */
    public function where(array $constraints): self
    {
        foreach ($constraints as $param => $regex) {
            // Remove delimiters if user provided any (like #...# or /.../)
            if (preg_match('/^(.)(.*)\1$/', $regex, $matches)) {
                $regex = $matches[2];
            }
            $this->constraints[$param] = $regex;
        }

        return $this;
    }
}