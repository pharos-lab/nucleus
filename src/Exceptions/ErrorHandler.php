<?php

declare(strict_types=1);

namespace Nucleus\Exceptions;

use Nucleus\Config\Environment;

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
     * Handle an uncaught exception.
     *
     * @param \Throwable $e The exception to handle.
     * @return void
     */
    public static function handleException(\Throwable $e): void
    {
        $env = Environment::get('APP_ENV', 'production');

        if ($env === 'local') {
            // Developer-friendly output
            echo "Uncaught exception: " . $e->getMessage() . PHP_EOL;
            echo "Stack trace: " . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
        } else {
            // Production-safe message
            echo "An unexpected error occurred. Please try again later.";
        }
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
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
}