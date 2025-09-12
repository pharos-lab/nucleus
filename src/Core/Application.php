<?php

declare(strict_types=1);

namespace Nucleus\Core;

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
    /** @var Router */
    protected Router $router;

    /** @var array<string, mixed> */
    protected array $config = [];

    /** @var string */
    protected string $basePath;

    /** @var Container */
    protected Container $container;

    /** @var string[] */
    protected array $middlewares = [];

    /**
     * Create a new Application instance.
     *
     * @param string $basePath The base path of the application (usually project root).
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->container = new Container();
        
        $this->bootstrap();
    }

    /**
     * Bootstrap the application by:
     * - Loading configuration
     * - Registering core bindings
     * - Registering user providers
     * - Initializing the router
     * - Registering routes
     * - Registering global middlewares
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
     * Load application configuration from config/app.php.
     */
    protected function loadConfig(): void
    {
        $configFile = $this->basePath . '/config/app.php';
        $this->config = file_exists($configFile) ? require $configFile : [];
    }

    /**
     * Register the core framework bindings via the NucleusProvider.
     */
    protected function registerCoreBindings(): void
    {
        $provider = new NucleusProvider($this);
        $provider->register();
    }

    /**
     * Register user-defined service providers from the config file.
     */
    protected function registerUserBindings(): void
    {
        $providers = $this->config['providers'] ?? [];

        foreach ($providers as $providerClass) {
            if (!class_exists($providerClass)) {
                continue; // Skip if provider class does not exist
            }

            $provider = new $providerClass($this);

            $provider->register();
        }
    }

    /**
     * Register application routes from the configured routes file.
     */
    protected function registerRoutes(): void
    {
        $routesPath = $this->config['routes_path'] ?? $this->basePath . '/routes/web.php';

        if (file_exists($routesPath)) {
            $router = $this->router; // Make router available inside the required file
            require $routesPath;
        }
    }

    /**
     * Register global middlewares from config/middleware.php.
     *
     * @return string[] List of global middleware class names
     */
    protected function registerGlobalMiddlewares(): array
    {
        $middlewareConfig = $this->basePath . '/config/middleware.php';
        return file_exists($middlewareConfig) ? require $middlewareConfig : [];
    }

    /**
     * Run the application by handling the current request through the kernel
     * and sending the response back to the client.
     */
    public function run(): void
    {
        $request = $this->container->make(Request::class);
        $kernel = new Nucleus($this);
        $response = $kernel->handle($request);

        $response->send();
    }

    // --- Getters ---

    /**
     * Get the service container instance.
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get the router instance.
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Get the global middlewares registered for the application.
     *
     * @return string[]
     */
    public function getGlobalMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Get the application configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get the base path of the application.
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }
}