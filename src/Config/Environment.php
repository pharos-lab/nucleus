<?php

declare(strict_types=1);

namespace Nucleus\Config;

class Environment
{
    /** @var array<string, mixed> */
    protected static array $variables = [];

    /**
     * Load environment variables from a .env file.
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
     * Get an environment variable, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$variables[$key] ?? $default;
    }

    /**
     * Set a variable at runtime.
     */
    public static function set(string $key, mixed $value): void
    {
        self::$variables[$key] = self::cast(trim($value));
    }

    /**
     * Reset all variables (for testing isolation)
     */
    public static function reset(): void
    {
        self::$variables = [];
    }

    /**
     * Attempt to cast string values to bool/int/float automatically
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