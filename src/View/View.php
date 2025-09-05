<?php

namespace Nucleus\View;

class View
{
    protected static string $basePath = '';

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }

    public static function make(string $view, array $data = []): string
    {
        $viewPath = self::$basePath . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("[NUCLEUS] View [$view] not found at [$viewPath]");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $viewPath;
        return ob_get_clean();
    }
}