<?php

declare(strict_types=1);

namespace Nucleus\Core;

use Nucleus\Config\Config;
use Nucleus\Config\Environment;
use Nucleus\Container\Container;
use Nucleus\Core\Bootstrap\NucleusProvider;
use Nucleus\Exceptions\ErrorHandler;
use Nucleus\Http\Request;
use Nucleus\Routing\Router;

/**
 * Main application kernel for Nucleus framework.
 *
 * Responsible for bootstrapping the framework:
 * - Loading environment variables and configuration
 * - Registering core and user service providers
 * - Initializing router, routes, and global middleware
 * - Running the HTTP kernel and sending responses
 */
class Application
{
    /**
     * Router instance.
     *
     * @var Router
     */
    protected Router $router;

    /**
     * Application configuration object.
     *
     * @var Config
     */
    protected Config $config;

    /**
     * Base path of the project (usually project root).
     *
     * @var string
     */
    protected string $basePath;

    /**
     * Service container instance.
     *
     * @var Container
     */
    protected Container $container;

    /**
     * Global middlewares registered for the application.
     *
     * @var string[]
     */
    protected array $middlewares = [];

    /**
     * Application constructor.
     *
     * @param string $basePath The root path of the project.
     */
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->container = new Container();

        $this->bootstrap();
    }

    /**
     * Bootstrap the application.
     *
     * Steps:
     * 1. Load environment variables
     * 2. Load configuration files
     * 3. Register core service bindings
     * 4. Register user-defined service providers
     * 5. Initialize the router
     * 6. Register routes and global middlewares
     *
     * @return void
     */
    public function bootstrap(): void
    {
        //Load environment variables from .env
        Environment::load($this->basePath . '/.env');
        $this->registerErrorHandling();
        $this->loadConfig();
        $this->registerCoreBindings();
        $this->registerUserBindings();

        $this->router = $this->container->make(Router::class);

        $this->registerRoutes();
        $this->middlewares = $this->registerGlobalMiddlewares();
    }

    /**
     * Register global error and exception handling.
     */
    protected function registerErrorHandling(): void
    {
        set_exception_handler([ErrorHandler::class, 'handleException']);
        set_error_handler([ErrorHandler::class, 'handleError']);
    }


    /**
     * Load configuration files from the config directory using Config class.
     *
     * @return void
     */
    protected function loadConfig(): void
    {
        $configPath = $this->basePath . '/config';
        $this->config = new Config($configPath);
    }

    /**
     * Register core framework bindings through NucleusProvider.
     *
     * @return void
     */
    protected function registerCoreBindings(): void
    {
        $provider = new NucleusProvider($this);
        $provider->register();
    }

    /**
     * Register user-defined service providers.
     *
     * Providers are read from config 'app.providers' array.
     *
     * @return void
     */
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

    /**
     * Register application routes from configured routes file.
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        $routesPath = $this->config->get('app.routes_path', $this->basePath . '/routes/web.php');

        if (file_exists($routesPath)) {
            $router = $this->router;
            require $routesPath;
        }
    }

    /**
     * Load global middlewares from config.
     *
     * @return string[] Array of middleware class names
     */
    protected function registerGlobalMiddlewares(): array
    {
        return $this->config->get('middleware', []);
    }

    /**
     * Run the application: handle request and send response.
     *
     * @return void
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
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * Get the router instance.
     *
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Get the list of global middlewares.
     *
     * @return string[] Array of middleware class names
     */
    public function getGlobalMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Add a global middleware to the application.
     *
     * @param string $middleware Fully qualified class name of middleware
     * @return void
     */
    public function addGlobalMiddleware(string $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Get the configuration instance.
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Get the base path of the application.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }
}