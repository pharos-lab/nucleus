<?php

declare(strict_types=1);

namespace Nucleus\Core;

use Nucleus\Config\Config;
use Nucleus\Container\Container;
use Nucleus\Core\Bootstrap\NucleusProvider;
use Nucleus\Http\Request;
use Nucleus\Routing\Router;

/**
 * The main application kernel.
 *
 * Responsible for bootstrapping the framework:
 * - Loading configuration
 * - Registering core and user providers
 * - Initializing the router
 * - Registering routes and global middlewares
 * - Running the HTTP kernel and sending the response
 */
class Application
{
    protected Router $router;
    protected Config $config;
    protected string $basePath;
    protected Container $container;
    protected array $middlewares = [];

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->container = new Container();

        $this->bootstrap();
    }

    /**
     * Bootstrap the application:
     * - Load config
     * - Register core bindings
     * - Register user providers
     * - Initialize router, routes, and global middleware
     */
    public function bootstrap(): void
    {
        $this->loadConfig();
        $this->registerCoreBindings();
        $this->registerUserBindings();

        $this->router = $this->container->make(Router::class);

        $this->registerRoutes();
        $this->middlewares = $this->registerGlobalMiddlewares();
    }

    /**
     * Load configuration using the new Config class
     */
    protected function loadConfig(): void
    {
        $configPath = $this->basePath . '/config';
        $this->config = new Config($configPath);
    }

    protected function registerCoreBindings(): void
    {
        $provider = new NucleusProvider($this);
        $provider->register();
    }

    protected function registerUserBindings(): void
    {
        $providers = $this->config->get('app.providers', []);

        foreach ($providers as $providerClass) {
            if (!class_exists($providerClass)) {
                continue;
            }

            $provider = new $providerClass($this);
            $provider->register();
        }
    }

    protected function registerRoutes(): void
    {
        $routesPath = $this->config->get('app.routes_path', $this->basePath . '/routes/web.php');

        if (file_exists($routesPath)) {
            $router = $this->router;
            require $routesPath;
        }
    }

    protected function registerGlobalMiddlewares(): array
    {
        return $this->config->get('middleware', []);
    }

    public function run(): void
    {
        $request = $this->container->make(Request::class);
        $kernel = new Nucleus($this);
        $response = $kernel->handle($request);

        $response->send();
    }

    // --- Getters ---

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

    public function addGlobalMiddleware(string $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }
}