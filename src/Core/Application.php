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
    protected array $config = [];
    protected string $basePath;
    protected Container $container;
    protected array $middlewares = [];

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->container = new Container();
        
        $this->bootstrap();
    }

    public function bootstrap(): void
    {
        $this->loadConfig();
        $this->registerCoreBindings();
        $this->registerUserBindings();

        $this->router = $this->container->make(Router::class);

        $this->registerRoutes();
        $this->middlewares = $this->registerGlobalMiddlewares();
    }

    protected function loadConfig(): void
    {
        $configFile = $this->basePath . '/config/app.php';
        $this->config = file_exists($configFile) ? require $configFile : [];
    }

    protected function registerCoreBindings(): void
    {
        $provider = new NucleusProvider($this);
        $provider->register();
    }

    protected function registerUserBindings(): void
    {
        $providers = $this->config['providers'] ?? [];

        foreach ($providers as $providerClass) {
            if (!class_exists($providerClass)) {
                continue; // provider inexistant → on ignore
            }

            $provider = new $providerClass($this);

            $provider->register();
        }
    }

    protected function registerRoutes(): void
    {
        $routesPath = $this->config['routes_path'] ?? $this->basePath . '/routes/web.php';

        if (file_exists($routesPath)) {
            $router = $this->router; // disponible dans la closure require
            require $routesPath;
        }
    }

    protected function registerGlobalMiddlewares(): array
    {
        $middlewareConfig = $this->basePath . '/config/middleware.php';
        return file_exists($middlewareConfig) ? require $middlewareConfig : [];
    }

    public function run(): void
    {
        $request = $this->container->make(Request::class);
        $kernel = new Nucleus($this);
        $response = $kernel->handle($request);

        $response->send();
    }

    // --- getters ---
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

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }
}