<?php

declare(strict_types=1);

namespace Tests\Traits;

trait ErrorHandlerIsolation
{
    private $previousErrorHandler;
    private $previousExceptionHandler;

    protected function setUp(): void
    {
        parent::setUp();

        // On capture les handlers existants avant chaque test
        $this->previousErrorHandler = set_error_handler(fn() => false);
        restore_error_handler(); // on restaure direct pour pas casser

        $this->previousExceptionHandler = set_exception_handler(fn() => false);
        restore_exception_handler();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        restore_error_handler();
    }
}