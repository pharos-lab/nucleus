<?php

declare(strict_types=1);

namespace Nucleus\Core;

use Nucleus\Container\Container;
use Nucleus\Core\Bootstrap\NucleusProvider;
use Nucleus\Http\Request;
use Nucleus\Routing\Router;

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
        $provider = new NucleusProvider($this->container, $this->basePath);
        $provider->register();
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
        $kernel = new Nucleus($this);

        // Handle request
        $response = $kernel->handle($request);

        // Send response
        $response->send();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getGlobalMiddlewares(): array
    {
        return $this->middlewares;
    }
}