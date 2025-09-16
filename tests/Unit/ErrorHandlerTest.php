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
        // On empêche l'affichage direct dans la console PHPUnit
        ob_start();
    }

    protected function tearDown(): void
    {
        ob_end_clean();
    }

    public function testHandleExceptionInLocalEnvironment(): void
    {
        Environment::set('APP_ENV', 'local');
        $exception = new \Exception("Boom");

        ErrorHandler::handleException($exception);

        $output = ob_get_contents();

        $this->assertStringContainsString("Boom", $output);
        $this->assertStringContainsString("Stack trace", $output);
    }

    public function testHandleExceptionInProductionEnvironment(): void
    {
        Environment::set('APP_ENV', 'production');
        $exception = new \Exception("Boom");

        ErrorHandler::handleException($exception);

        $output = ob_get_contents();

        $this->assertStringContainsString("An unexpected error occurred", $output);
        $this->assertStringNotContainsString("Boom", $output);
    }

    public function testHandleErrorTransformsToException(): void
    {
        $this->expectException(\ErrorException::class);

        ErrorHandler::handleError(E_USER_WARNING, "Test warning", __FILE__, __LINE__);
    }
}