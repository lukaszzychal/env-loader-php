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
     * Load environment variables from .env file with environment-specific support.
     *
     * @param string $filePath Path to the .env file
     * @param string|null $environment Environment name (e.g., 'dev', 'prod', 'staging')
     * @param bool $loadLocalOverrides Whether to load .local override files
     * @return bool True if any file was loaded successfully, false otherwise
     */
    public static function load(string $filePath, ?string $environment = null, bool $loadLocalOverrides = true): bool
    {
        $loaded = false;
        $filesToLoad = self::getFilesToLoad($filePath, $environment, $loadLocalOverrides);
        $originalEnv = $_ENV; // Store original environment

        foreach ($filesToLoad as $file) {
            if (file_exists($file)) {
                // Allow overrides between files, but not from original environment
                $loaded = self::loadFromFile($file, true) || $loaded;
            }
        }

        return $loaded;
    }

    /**
     * Get list of files to load in order of priority.
     *
     * @param string $filePath Base .env file path
     * @param string|null $environment Environment name
     * @param bool $loadLocalOverrides Whether to load .local override files
     * @return array Array of file paths in load order
     */
    private static function getFilesToLoad(string $filePath, ?string $environment = null, bool $loadLocalOverrides = true): array
    {
        $files = [];
        $dir = dirname($filePath);
        $baseName = basename($filePath);

        // 1. Base .env file
        $files[] = $filePath;

        // 2. Environment-specific file (e.g., .env.dev)
        if ($environment !== null) {
            $envFile = $dir . '/' . str_replace('.env', ".env.$environment", $baseName);
            $files[] = $envFile;
        }

        // 3. Local override files (highest priority)
        if ($loadLocalOverrides) {
            // .env.local
            $localFile = $dir . '/' . str_replace('.env', '.env.local', $baseName);
            $files[] = $localFile;

            // .env.{environment}.local
            if ($environment !== null) {
                $envLocalFile = $dir . '/' . str_replace('.env', ".env.$environment.local", $baseName);
                $files[] = $envLocalFile;
            }
        }

        return $files;
    }

    /**
     * Load environment variables from a single file.
     *
     * @param string $filePath Path to the .env file
     * @param bool $allowOverrides Whether to allow overriding existing variables
     * @return bool True if file was loaded successfully, false otherwise
     */
    private static function loadFromFile(string $filePath, bool $allowOverrides = false): bool
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

                    // Set environment variable based on override policy
                    if ($allowOverrides || (!array_key_exists($name, $_ENV) && getenv($name) === false)) {
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
     * Load environment variables from .env file and return loaded variables with environment-specific support.
     *
     * @param string $filePath Path to the .env file
     * @param string|null $environment Environment name (e.g., 'dev', 'prod', 'staging')
     * @param bool $loadLocalOverrides Whether to load .local override files
     * @return array Array of loaded environment variables
     */
    public static function loadAndReturn(string $filePath, ?string $environment = null, bool $loadLocalOverrides = true): array
    {
        $loaded = [];
        $filesToLoad = self::getFilesToLoad($filePath, $environment, $loadLocalOverrides);

        foreach ($filesToLoad as $file) {
            if (file_exists($file)) {
                $fileVars = self::loadFromFileAndReturn($file);
                $loaded = array_merge($loaded, $fileVars);
            }
        }

        return $loaded;
    }

    /**
     * Load environment variables from a single file and return them.
     *
     * @param string $filePath Path to the .env file
     * @return array Array of loaded environment variables
     */
    private static function loadFromFileAndReturn(string $filePath): array
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
