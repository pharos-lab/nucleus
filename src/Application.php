<?php

namespace Nucleus;

use Nucleus\Http\Response;
use Nucleus\Routing\Router;

class Application
{
    protected Router $router;
    protected $config;
    protected string $basePath;

    public function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->router = new Router();
        $this->config = file_exists($basePath . '/config/app.php') 
            ? require $basePath . '/config/app.php'
            : [];

        // Load default routes
        $this->loadRoutes($this->config['routes_path']);
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
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $output = $this->router->dispatch($uri, $method);

        // Send the output if Response or create it
        if ($output instanceof Response) {
            $output->send();
        } else {
            (new Response((string) $output))->send();
        }
    }
}