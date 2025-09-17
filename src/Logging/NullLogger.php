<?php

declare(strict_types=1);

namespace Nucleus\Logging;

use Psr\Log\NullLogger as PsrNullLogger;

/**
 * Null logger in the Nucleus namespace.
 *
 * Thin wrapper around PSR's NullLogger so tests can reference
 * Nucleus\Logging\NullLogger directly.
 */
class NullLogger extends PsrNullLogger
{
    // Nothing to add — inherits noop behavior from Psr\Log\NullLogger
}