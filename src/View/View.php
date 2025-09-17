<?php

declare(strict_types=1);

namespace Nucleus\View;

use Nucleus\Http\Response;

/**
 * Class View
 *
 * Simple PHP view renderer.
 * Converts a view file into an HTTP response with optional data.
 *
 * @package Nucleus\View
 */
class View
{
    /** @var string Base path where view files are located */
    protected string $basePath;

    /**
     * View constructor.
     *
     * @param string $basePath Base path of the application.
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Render a view file and return a Response.
     *
     * @param string $view Dot-notated view name (e.g. "pages.home").
     * @param array $data Associative array of variables to pass to the view.
     *
     * @return Response HTTP response containing the rendered view.
     *
     * @throws \RuntimeException If the view file does not exist.
     */
    public function make(string $view, array $data = []): Response
    {
        $content = $this->render($view, $data);

        // Return as an HTTP response
        return new Response($content);
    }

    /**
     * Render the view file and return its content as a string.
     *
     * @param string $view Dot-notated view name (e.g. "pages.home").
     * @param array $data Associative array of variables to pass to the view.
     *
     * @return string Rendered view content.
     *
     * @throws \RuntimeException If the view file does not exist.
     */
    public function render(string $view, array $data = []): string
    {
        $viewPath = $this->basePath . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("[NUCLEUS] View [$view] not found at [$viewPath]");
        }

        // Extract variables for the view
        extract($data, EXTR_SKIP);

        // Capture the output
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }
}