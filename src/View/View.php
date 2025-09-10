<?php

declare(strict_types=1);

namespace Nucleus\View;

use Nucleus\Http\Response;

class View
{
    protected string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function make(string $view, array $data = []): Response
    {
        $viewPath = $this->basePath . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("[NUCLEUS] View [$view] not found at [$viewPath]");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        return new Response($content);
    }
}