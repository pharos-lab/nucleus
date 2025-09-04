<?php

namespace Nucleus;

use Nucleus\Http\Response;
use Nucleus\Routing\Router;

class Application
{
    protected Router $router;

    public function __construct()
    {
        $this->router = new Router();

        // Load default routes
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

        $output = $this->router->dispatch($uri, $method);

        // Send the output if Response or create it
        if ($output instanceof Response) {
            $output->send();
        } else {
            (new Response((string) $output))->send();
        }
    }
}