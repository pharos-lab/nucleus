<?php

namespace Nucleus\Controller;

use Nucleus\Http\Response;
use Nucleus\View\View;

abstract class BaseController
{
    protected function view(string $view, array $data = []): response
    {
        return View::make($view, $data);
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
