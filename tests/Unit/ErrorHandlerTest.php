<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Nucleus\Config\Environment;
use Nucleus\Exceptions\ErrorHandler;
use Nucleus\Container\Container;
use Nucleus\Contracts\NucleusLoggerInterface;
use Nucleus\View\View;
use Nucleus\Http\Response;
use Nucleus\Logging\FileLogger;

final class ErrorHandlerTest extends TestCase
{
    protected Container $container;
    protected ErrorHandler $handler;

    protected function setUp(): void
    {
        Environment::reset();

        // Container simulé avec une vue simple
        $this->container = new Container();
            // handler will need to make View and Logger
        $this->container->bind(View::class, fn() => new View(__DIR__ . '/../Fakes'));
        $this->container->bind(NucleusLoggerInterface::class, fn() => new FileLogger(__DIR__ . '/../Fakes/temp/handler.log'));
        $this->handler = new ErrorHandler($this->container);
    }

    public function testHandleExceptionReturnsResponseInLocalEnvironment(): void
    {
        Environment::set('APP_ENV', 'local');
        $exception = new \Exception("Boom Local");

        // On force le rendu HTTP
        $response = $this->handler->handleException($exception);

        // Si HTTP, handleException renvoie un Response
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString("Boom Local", (string) $response->getBody());
    }

    public function testHandleExceptionReturnsGenericResponseInProduction(): void
    {
        Environment::set('APP_ENV', 'production');
        $exception = new \Exception("Boom Prod");

        $response = $this->handler->handleException($exception);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(500, $response->getStatusCode());
        $this->assertStringContainsString("Something went wrong", (string) $response->getBody());
        $this->assertStringNotContainsString("Boom Prod", (string) $response->getBody());
    }

    public function testHandleErrorTransformsToException(): void
    {
        $this->expectException(\ErrorException::class);
        $this->expectExceptionMessage("Test warning");

        $this->handler->handleError(E_USER_WARNING, "Test warning", __FILE__, __LINE__);
    }
}