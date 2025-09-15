<?php

declare(strict_types=1);

namespace Nucleus\Config;

/**
 * Class Environment
 *
 * Centralized environment variable manager.
 *
 * Responsibilities:
 * - Load variables from a `.env` file
 * - Provide get/set access to environment variables at runtime
 * - Automatic casting of strings to bool, int, or float
 * - Reset variables (useful for testing)
 */
class Environment
{
    /**
     * Loaded environment variables.
     *
     * @var array<string, mixed>
     */
    protected static array $variables = [];

    /**
     * Load environment variables from a `.env` file.
     *
     * Each line should be in the format `KEY=value`.
     * Lines starting with `#` are ignored as comments.
     * Values are automatically cast to bool, int, float if applicable.
     *
     * @param string $path Path to the `.env` file
     */
    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue; // skip comments
            }

            [$key, $value] = explode('=', $line, 2) + [null, null];
            if ($key !== null) {
                self::$variables[trim($key)] = self::cast(trim($value));
            }
        }
    }

    /**
     * Get an environment variable.
     *
     * @param string $key Variable name
     * @param mixed $default Value to return if variable does not exist
     * @return mixed Variable value or $default
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$variables[$key] ?? $default;
    }

    /**
     * Set an environment variable at runtime.
     *
     * Automatically casts the value to bool/int/float if possible.
     *
     * @param string $key Variable name
     * @param mixed $value Value to set
     */
    public static function set(string $key, mixed $value): void
    {
        self::$variables[$key] = self::cast(trim((string)$value));
    }

    /**
     * Reset all loaded variables.
     *
     * Useful for unit testing to avoid contamination between tests.
     */
    public static function reset(): void
    {
        self::$variables = [];
    }

    /**
     * Attempt to cast string values to boolean, integer, or float automatically.
     *
     * @param string $value The raw string value
     * @return mixed The casted value
     */
    protected static function cast(string $value): mixed
    {
        $lower = strtolower($value);

        if ($lower === 'true') return true;
        if ($lower === 'false') return false;

        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }

        return $value;
    }
}