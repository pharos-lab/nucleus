<?php

declare(strict_types=1);

namespace Nucleus;

use Nucleus\Container\Container;
use Nucleus\Http\Request;
use Nucleus\Routing\Router;
use Nucleus\Routing\RouteResolver;
use Nucleus\View\View;

class Application
{
    protected Router $router;
    protected array $config;
    protected string $basePath;
    protected Container $container;
    protected array $middlewares = [];

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->container = new Container();

        $this->config = file_exists($basePath . '/config/app.php') 
            ? require $basePath . '/config/app.php'
            : [];

        // Register core services in the container
        $this->registerCoreBindings();

        $this->router = $this->container->make(Router::class);

        // Load routes
        $this->loadRoutes($this->config['routes_path'] ?? $basePath . '/routes/web.php');

        // Load global middlewares
        $this->middlewares = $this->loadGlobalMiddlewares();
    }

    protected function registerCoreBindings(): void
    {
        // Route resolver with container
        $this->container->bind(RouteResolver::class, fn($c) => new RouteResolver($c));

        // Router
        $this->container->bind(Router::class, fn($c) => new Router(
            $c->make(RouteResolver::class)
        ));

        // View service (instance, no more static)
        $this->container->bind(View::class, fn() => new View($this->basePath));

        // Request factory
        $this->container->bind(Request::class, fn() => Request::capture());
    }

    protected function loadRoutes(string $path): void
    {
        if (file_exists($path)) {
            $router = $this->router; // extracted for `require` scope
            require $path;
        }
    }

    protected function loadGlobalMiddlewares(): array
    {
        $middlewareConfig = $this->basePath . '/config/middleware.php';
        return file_exists($middlewareConfig) ? require $middlewareConfig : [];
    }

    public function run(): void
    {
        // Resolve request from container
        $request = $this->container->make(Request::class);

        // Create kernel with router, resolver and middlewares
        $kernel = new Nucleus($this->router, $this->middlewares);

        // Handle request
        $response = $kernel->handle($request);

        // Send response
        $response->send();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}