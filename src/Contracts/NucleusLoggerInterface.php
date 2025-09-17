<?php

declare(strict_types=1);

namespace Nucleus\Contracts;

use Psr\Log\LoggerInterface;

/**
 * NucleusLoggerInterface
 *
 * Extends PSR-3 LoggerInterface with framework-specific helpers.
 */
interface NucleusLoggerInterface extends LoggerInterface
{
    /**
     * Log a successful operation (alias of info, but semantic).
     *
     * @param string $message
     * @param array<string,mixed> $context
     * @return void
     */
    public function success(string $message, array $context = []): void;

    /**
     * Attach default context for all subsequent logs.
     *
     * Example: $logger->withContext(['request_id' => 'abc123']);
     *
     * @param array<string,mixed> $context
     * @return static
     */
    public function withContext(array $context): static;
}