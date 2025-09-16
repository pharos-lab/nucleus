<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Tests\Traits\ErrorHandlerIsolation;

abstract class TestCase extends BaseTestCase
{
    use ErrorHandlerIsolation;
}