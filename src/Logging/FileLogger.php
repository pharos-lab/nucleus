<?php

declare(strict_types=1);

namespace Nucleus\Logging;

use Nucleus\Contracts\NucleusLoggerInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Simple file-based logger.
 *
 * - Replaces {placeholders} in the message using the provided context.
 * - Any context keys that are not used as placeholders are appended as `key=value`.
 * - Uses PSR-3 AbstractLogger so standard level helpers are available.
 */
class FileLogger extends AbstractLogger implements NucleusLoggerInterface
{
    /**
     * Path to the log file.
     */
    protected string $filePath;

    /**
     * Minimum log level to record.
     */
    protected string $level;

    /**
     * Persistent context that will be merged into each log call when using withContext().
     *
     * @var array<string,mixed>
     */
    protected array $defaultContext = [];

    /**
     * Log levels order for filtering.
     *
     * Lower number means lower severity.
     *
     * @var array<string,int>
     */
    protected array $levelsOrder = [
        'success' => 100,
        LogLevel::DEBUG => 100,
        LogLevel::INFO => 200,
        LogLevel::NOTICE => 250,
        LogLevel::WARNING => 300,
        LogLevel::ERROR => 400,
        LogLevel::CRITICAL => 500,
        LogLevel::ALERT => 550,
        LogLevel::EMERGENCY => 600,
    ];

    /**
     * Create a new FileLogger.
     *
     * @param string $filePath Path to the log file (directory must be writable).
     */
    public function __construct(string $filePath, $level = 'debug')
    {
        $this->filePath = $filePath;
        $this->level = $level;
    }

    /**
     * PSR-3 log implementation.
     *
     * Replaces placeholders in the message using $context, then appends leftover context.
     *
     * @param mixed $level
     * @param string|\Stringable $message
     * @param array<string,mixed> $context
     */
    public function log($level, $message, array $context = []): void
    {
        // Ignore messages below configured level
        if ($this->levelsOrder[$level] < $this->levelsOrder[$this->level]) {
            return;
        }

        // Ensure level and message are strings
        $levelStr = strtoupper((string) $level);
        $messageStr = (string) $message;

        // Merge persistent context
        $context = array_merge($this->defaultContext, $context);

        // Replace placeholders {key} with context values and remove used keys
        foreach ($context as $key => $value) {
            $placeholder = '{' . $key . '}';
            if (strpos($messageStr, $placeholder) !== false) {
                $messageStr = str_replace($placeholder, $this->stringify($value), $messageStr);
                unset($context[$key]);
            }
        }

        // Format remaining context as key=value pairs (simple scalar or json for complex)
        $extra = $this->formatRemainingContext($context);

        $line = date('Y-m-d H:i:s') . ' ' . $levelStr . ' ' . $messageStr . ($extra !== '' ? ' CONTEXT: ' . $extra : '') . PHP_EOL;

        // Ensure directory exists
        $dir = dirname($this->filePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        // Append to file
        file_put_contents($this->filePath, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Add a persistent context to the logger and return a cloned instance.
     *
     * Example: $logger = $logger->withContext(['request_id' => 'abc']);
     *
     * @param array<string,mixed> $context
     * @return static
     */
    public function withContext(array $context): static
    {
        $clone = clone $this;
        $clone->defaultContext = array_merge($this->defaultContext, $context);
        return $clone;
    }

    /**
     * Convenience method for success-level logging (non standard PSR level).
     *
     * @param string $message
     * @param array<string,mixed> $context
     */
    public function success(string $message, array $context = []): void
    {
        $this->log('success', $message, $context);
    }

    /**
     * Convert a value to a string for log output.
     */
    protected function stringify(mixed $value): string
    {
        if (is_null($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        // For arrays/objects, JSON encode as a fallback
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $encoded === false ? '[[unserializable]]' : $encoded;
    }

    /**
     * Format remaining context key/value pairs into a single string.
     *
     * Example: ['id' => 42, 'foo' => 'bar'] => "id=42 foo=bar"
     *
     * @param array<string,mixed> $context
     */
    protected function formatRemainingContext(array $context): string
    {
        if (empty($context)) {
            return '';
        }

        $parts = [];
        foreach ($context as $k => $v) {
            $parts[] = $k . '=' . $this->stringify($v);
        }

        return implode(' ', $parts);
    }
}