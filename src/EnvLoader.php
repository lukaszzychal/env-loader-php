<?php

declare(strict_types=1);

namespace LukaszZychal\EnvLoader;

/**
 * Simple .env file loader utility.
 *
 * This is a basic implementation for loading environment variables from .env files.
 * For production applications, consider using a dedicated package like vlucas/phpdotenv.
 */
class EnvLoader
{
    /**
     * Load environment variables from .env file.
     *
     * @param string $filePath Path to the .env file
     * @return bool True if file was loaded successfully, false otherwise
     */
    public static function load(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return false;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $name = trim($parts[0]);
                    $value = trim($parts[1]);

                    // Remove quotes if present
                    if (($value[0] ?? '') === '"' && ($value[-1] ?? '') === '"') {
                        $value = substr($value, 1, -1);
                    } elseif (($value[0] ?? '') === "'" && ($value[-1] ?? '') === "'") {
                        $value = substr($value, 1, -1);
                    }

                    // Set environment variable if not already set
                    if (!array_key_exists($name, $_ENV) && getenv($name) === false) {
                        $_ENV[$name] = $value;
                        putenv("$name=$value");
                    }
                }
            }
        }

        return true;
    }

    /**
     * Get environment variable with fallback.
     *
     * @param string $key Environment variable key
     * @param mixed $default Default value if key is not found
     * @return mixed Environment variable value or default
     */
    public static function get(string $key, $default = null)
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * Check if environment variable exists.
     *
     * @param string $key Environment variable key
     * @return bool True if variable exists, false otherwise
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, $_ENV) || getenv($key) !== false;
    }

    /**
     * Load environment variables from .env file and return loaded variables.
     *
     * @param string $filePath Path to the .env file
     * @return array Array of loaded environment variables
     */
    public static function loadAndReturn(string $filePath): array
    {
        $loaded = [];

        if (!file_exists($filePath)) {
            return $loaded;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return $loaded;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $name = trim($parts[0]);
                    $value = trim($parts[1]);

                    // Remove quotes if present
                    if (($value[0] ?? '') === '"' && ($value[-1] ?? '') === '"') {
                        $value = substr($value, 1, -1);
                    } elseif (($value[0] ?? '') === "'" && ($value[-1] ?? '') === "'") {
                        $value = substr($value, 1, -1);
                    }

                    $loaded[$name] = $value;
                }
            }
        }

        return $loaded;
    }
}
