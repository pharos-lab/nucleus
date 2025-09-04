<?php

namespace Nucleus;

use Nucleus\Routing\Router; // on suppose qu'il existera plus tard

class Application
{
    protected Router $router;

    public function __construct()
    {
        $this->router = new Router();

        // Charger les routes par défaut
        $this->loadRoutes(__DIR__ . '/../routes/web.php');
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

        $this->router->dispatch($uri, $method);
    }
}