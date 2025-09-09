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
    protected $config;
    protected string $basePath;
    protected Container $container;
    protected array $middlewares = [];

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->router = new Router();
        $this->config = file_exists($basePath . '/config/app.php') 
            ? require $basePath . '/config/app.php'
            : [];

        // Load default routes
        $this->loadRoutes($this->config['routes_path']);
        View::setBasePath($basePath);
        
        $this->container = new Container();
        RouteResolver::setContainer($this->container);

        // Load global middlewares from config if exists
        $middlewareConfig = $basePath . '/config/middleware.php';
        $this->middlewares = file_exists($middlewareConfig)
            ? require $middlewareConfig
            : [];
            var_dump($this->middlewares); // --- IGNORE ---
    }

    protected function loadRoutes(string $path): void
    {
        if (file_exists($path)) {
            $router = $this->router;
            require $path;
        }
    }

    public function run(): void
    {
        $request = Request::capture();

        // Create kernel using router + middlewares
        $kernel = new Nucleus($this->router, $this->middlewares);

        // Handle request through middleware pipeline
        $response = $kernel->handle($request);

        $response->send();
    }
}