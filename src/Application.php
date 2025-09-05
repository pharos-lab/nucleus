<?php

namespace Nucleus;

use Nucleus\Container\Container;
use Nucleus\Http\Request;
use Nucleus\Http\Response;
use Nucleus\Routing\Dispatcher;
use Nucleus\Routing\Router;
use Nucleus\View\View;

class Application
{
    protected Router $router;
    protected $config;
    protected string $basePath;
    protected Container $container;

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
        Dispatcher::setContainer($this->container);
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
        $request = new Request();

        $output = $this->router->dispatch($request);

        // Send the output if Response or create it
        if ($output instanceof Response) {
            $output->send();
        } else {
            (new Response((string) $output))->send();
        }
    }
}