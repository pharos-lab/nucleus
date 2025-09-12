<?php

declare(strict_types=1);

namespace Nucleus\Controller;

use Nucleus\Http\Response;
use Nucleus\View\View;

/**
 * Class BaseController
 *
 * Provides basic helper methods for controllers:
 * - view rendering
 * - JSON responses
 * - generic HTTP responses
 *
 * @package Nucleus\Controller
 */
abstract class BaseController
{
    protected View $view;

    /**
     * BaseController constructor.
     *
     * @param View $view The view service used for rendering templates.
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Render a view template with optional data.
     *
     * @param string $view The view path (dot notation allowed).
     * @param array $data Data to be passed to the view.
     * @return Response
     */
    protected function view(string $view, array $data = []): Response
    {
        return $this->view->make($view, $data);
    }

    /**
     * Return a JSON response.
     *
     * @param array $data Data to encode as JSON.
     * @param int $status HTTP status code.
     * @return Response
     */
    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    /**
     * Return a generic HTTP response.
     *
     * @param string $content Response content.
     * @param int $status HTTP status code.
     * @param array $headers Additional headers.
     * @return Response
     */
    protected function response(string $content, int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }
}