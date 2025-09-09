<?php

declare(strict_types=1);

namespace Nucleus\View;

use Nucleus\Http\Response;

class View
{
    protected static string $basePath = '';

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }

    public static function make(string $view, array $data = []): Response
    {
        $viewPath = self::$basePath . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("[NUCLEUS] View [$view] not found at [$viewPath]");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $viewPath;
        $content =  ob_get_clean();

        return new Response($content);
    }
}