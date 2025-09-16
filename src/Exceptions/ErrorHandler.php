<?php

declare(strict_types=1);

namespace Nucleus\Exceptions;

use Nucleus\Config\Environment;
use Nucleus\Container\Container;
use Nucleus\Http\Response;
use Nucleus\View\View;

/**
 * Global error and exception handler for the framework.
 *
 * The ErrorHandler centralizes how errors and exceptions
 * are processed and displayed, depending on the environment
 * (local, production, etc.).
 *
 * - In "local" mode, full details and stack traces are shown.
 * - In "production" mode, a generic message is displayed to the user.
 *
 * This class can also transform PHP errors into exceptions
 * so that they are handled consistently by the application.
 *
 */
class ErrorHandler
{

    /**
     * Service container instance.
     *
     * @var Container
     */
    protected Container $container;

    /**
     * View renderer instance.
     *
     * @var View
     */
    protected View $view;

    /**
     * Constructor.
     *
     * @param Container $container The service container.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->view = $container->make(View::class);
    }


    /**
     * Handle an uncaught exception.
     *
     * @param \Throwable $e The exception to handle.
     * @return Response The HTTP response to send to the client.
     */
    public function handleException(\Throwable $e): Response
    {
        $isLocal = Environment::get('APP_ENV', 'production') === 'local';

        return $this->renderHtml($e, $isLocal);
    }

    /**
     * Handle a PHP error by converting it into an ErrorException.
     *
     * This allows all errors to be handled uniformly
     * by the exception handler.
     *
     * @param int    $severity   The level of the error (E_NOTICE, E_WARNING, etc.)
     * @param string $message    The error message.
     * @param string $file       The filename where the error occurred.
     * @param int    $line       The line number where the error occurred.
     * @return bool  Always returns true to indicate the error was handled.
     *
     * @throws \ErrorException
     */
    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Render exception in HTML.
     * 
     * In local environment, show full details.
     * In production, show a generic error message.
     * 
     * @param \Throwable $e The exception to render.
     * @param bool $isLocal Whether the environment is local.
     * @return Response The HTTP response with rendered content.
     */
    protected function renderHtml(\Throwable $e, bool $isLocal): Response
    {
        if ($isLocal) {
            $content = $this->view->render('errors.local', ['exception' => $e]);
        } else {
            $content = $this->view->render('errors.production');
        }

        return new Response($content, 500, ['Content-Type' => 'text/html']);
    }
}