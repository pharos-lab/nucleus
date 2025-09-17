<?php

declare(strict_types=1);

namespace Nucleus\Exceptions;

use Nucleus\Config\Environment;
use Nucleus\Container\Container;
use Nucleus\Contracts\NucleusLoggerInterface;
use Nucleus\Http\Response;
use Nucleus\View\View;

/**
 * Global error and exception handler for the framework.
 *
 * Handles:
 * - PHP errors converted into exceptions
 * - Uncaught exceptions
 * - Environment-aware rendering (local vs production)
 * - Logging via PSR-3 Logger
 */
class ErrorHandler
{
    protected Container $container;
    protected View $view;
    protected $logger;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->view = $container->make(View::class);
        $this->logger = $container->make(NucleusLoggerInterface::class);
    }

    /**
     * Handle an uncaught exception.
     * Returns an HTTP response with appropriate content based on environment.
     * Logs the exception details.
     * @param \Throwable $e The uncaught exception
     * @return Response The HTTP response to send to the client
     */
    public function handleException(\Throwable $e): Response
    {
        $isLocal = Environment::get('APP_ENV', 'production') === 'local';

        // Log exception
        $this->logger->error(
            $e->getMessage(),
            [
                'exception' => $e,
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
            ]
        );

        return $this->renderHtml($e, $isLocal);
    }

    /**
     * Handle a PHP error by converting it into an ErrorException.
     * Logs the error before throwing.
     * @param int $severity The severity level of the error
     * @param string $message The error message
     * @param string $file The filename where the error occurred
     * @param int $line The line number where the error occurred
     * @return bool Always returns false to allow normal error handling to continue
     * @throws \ErrorException
     * 
     */
    public function handleError(int $severity, string $message, string $file, int $line): bool
    {
        // Log PHP error before throwing
        $this->logger->warning($message, [
            'severity' => $severity,
            'file'     => $file,
            'line'     => $line,
        ]);

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Render exception in HTML.
     * In local environment, shows detailed error page.
     * In production, shows generic error page.
     * @param \Throwable $e The exception to render
     * @param bool $isLocal Whether the environment is local (development)
     * @return Response The HTTP response with rendered HTML
     * 
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