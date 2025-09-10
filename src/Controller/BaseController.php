<?php

declare(strict_types=1);

namespace Nucleus\Controller;

use Nucleus\Http\Response;
use Nucleus\View\View;

abstract class BaseController
{
    protected View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    protected function view(string $view, array $data = []): Response
    {
        return $this->view->make($view, $data);
    }

    protected function json(array $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function response(string $content, int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }
}