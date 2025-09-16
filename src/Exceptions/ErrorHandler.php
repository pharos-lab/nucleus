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
        $isLocal = Environment::get('APP_ENV', 'production') === 'local';
        $isCli   = php_sapi_name() === 'cli';

        if ($isCli) {
            self::renderCli($e, $isLocal);
        } else {
            self::renderHtml($e, $isLocal);
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

    /**
     * Render exception in CLI.
     */
    protected static function renderCli(\Throwable $e, bool $isLocal): void
    {
        if ($isLocal) {
            echo "Exception: {$e->getMessage()}" . PHP_EOL;
            echo "File: {$e->getFile()} on line {$e->getLine()}" . PHP_EOL;
            echo "Trace:" . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
        } else {
            echo "An error occurred. Please try again later." . PHP_EOL;
        }
    }

    /**
     * Render exception in HTML.
     */
    protected static function renderHtml(\Throwable $e, bool $isLocal): void
    {
        if ($isLocal) {
            echo "<h1 style='color:#c00;'>Exception: {$e->getMessage()}</h1>";
            echo "<p><strong>File:</strong> {$e->getFile()} on line {$e->getLine()}</p>";
            echo "<p><strong>Trace:</strong></p>";
            echo "<pre style='background:#f5f5f5;padding:10px;border:1px solid #ddd;'>";
            echo htmlspecialchars($e->getTraceAsString());
            echo "</pre>";
        } else {
            echo "<h1>Something went wrong</h1>";
            echo "<p>Please try again later.</p>";
        }
    }
}