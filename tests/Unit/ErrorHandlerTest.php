<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Nucleus\Config\Environment;
use Nucleus\Exceptions\ErrorHandler;

final class ErrorHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        Environment::reset();
        ob_start(); // capture toute sortie
    }

    protected function tearDown(): void
    {
        ob_end_clean();
    }

    public function testHandleExceptionInLocalEnvironment(): void
    {
        Environment::set('APP_ENV', 'local');
        $exception = new \Exception("Boom Local");

        ErrorHandler::handleException($exception);

        $output = ob_get_contents();

        $this->assertStringContainsString("Boom Local", $output, "Should show the real error message in local env.");
        $this->assertStringContainsString("File:", $output, "Should include the file name.");
        $this->assertStringContainsString("Trace", $output, "Should include the stack trace.");
    }

    public function testHandleExceptionInProductionEnvironment(): void
    {
        Environment::set('APP_ENV', 'production');
        $exception = new \Exception("Boom Prod");

        ErrorHandler::handleException($exception);

        $output = ob_get_contents();

        $this->assertStringContainsString("An error occurred. Please try again later.", $output, "Should show a generic safe message.");
        $this->assertStringNotContainsString("Boom Prod", $output, "Should NOT expose the internal message.");
        $this->assertStringNotContainsString("Trace", $output, "Should not leak stack trace.");
    }

    public function testHandleErrorTransformsToException(): void
    {
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage("Test warning");

        ErrorHandler::handleError(E_USER_WARNING, "Test warning", __FILE__, __LINE__);
    }

    public function testCliRenderingShowsPlainText(): void
    {
        Environment::set('APP_ENV', 'local');
        $exception = new \RuntimeException("CLI Boom");

        // Simule un environnement CLI
        $originalSapi = \PHP_SAPI;
        // ⚠️ hack : on remplace temporairement la fonction php_sapi_name()
        // mais pour PHPUnit on peut directement tester renderCli()
        $refMethod = new ReflectionMethod(ErrorHandler::class, 'renderCli');
        $refMethod->setAccessible(true);

        $refMethod->invoke(null, $exception, true);

        $output = ob_get_contents();

        $this->assertStringContainsString("CLI Boom", $output, "Should display exception message in CLI mode.");
        $this->assertStringContainsString("Trace", $output, "Should include stack trace in CLI mode.");
    }
}