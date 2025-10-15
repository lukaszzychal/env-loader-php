<?php

declare(strict_types=1);

namespace LukaszZychal\EnvLoader;

/**
 * Simple and lightweight .env file loader utility for PHP applications.
 *
 * This package provides a straightforward way to load environment variables
 * from .env files with support for environment-specific and local override files.
 *
 * @author Lukasz Zychal <lukasz.zychal.dev@gmail.com>
 * @license MIT
 */
class EnvLoader
{
    /**
     * Comment character used in .env files.
     */
    private const COMMENT_CHAR = '#';

    /**
     * Key-value separator used in .env files.
     */
    private const KEY_VALUE_SEPARATOR = '=';

    /**
     * Quote characters supported for values.
     */
    private const QUOTE_CHARS = ['"', "'"];

    /**
     * Load environment variables from .env files with environment-specific support.
     *
     * @param string $filePath Path to the base .env file
     * @param string|null $environment Environment name (e.g., 'dev', 'prod', 'staging')
     * @param bool $loadLocalOverrides Whether to load .local override files
     * @return bool True if any file was loaded successfully, false otherwise
     */
    public static function load(string $filePath, ?string $environment = null, bool $loadLocalOverrides = true): bool
    {
        $filesToLoad = self::buildFileList($filePath, $environment, $loadLocalOverrides);
        $originalEnvironment = $_ENV;

        return self::loadFilesIntoEnvironment($filesToLoad, $originalEnvironment);
    }

    /**
     * Load environment variables and return them as an array.
     *
     * @param string $filePath Path to the base .env file
     * @param string|null $environment Environment name (e.g., 'dev', 'prod', 'staging')
     * @param bool $loadLocalOverrides Whether to load .local override files
     * @return array Array of loaded environment variables
     */
    public static function loadAndReturn(
        string $filePath,
        ?string $environment = null,
        bool $loadLocalOverrides = true
    ): array {
        $filesToLoad = self::buildFileList($filePath, $environment, $loadLocalOverrides);
        $loadedVariables = [];

        foreach ($filesToLoad as $file) {
            if (file_exists($file)) {
                $fileVariables = self::parseFileIntoArray($file);
                $loadedVariables = array_merge($loadedVariables, $fileVariables);
            }
        }

        return $loadedVariables;
    }

    /**
     * Get an environment variable value with optional default.
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
     * Check if an environment variable exists.
     *
     * @param string $key Environment variable key
     * @return bool True if variable exists, false otherwise
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, $_ENV) || getenv($key) !== false;
    }

    /**
     * Build the list of files to load in priority order.
     *
     * @param string $filePath Base .env file path
     * @param string|null $environment Environment name
     * @param bool $loadLocalOverrides Whether to load .local override files
     * @return array Array of file paths in load order
     */
    private static function buildFileList(string $filePath, ?string $environment, bool $loadLocalOverrides): array
    {
        $files = [$filePath];
        $directory = dirname($filePath);
        $baseName = basename($filePath);

        if ($environment !== null) {
            $files[] = self::buildEnvironmentFilePath($directory, $baseName, $environment);
        }

        if ($loadLocalOverrides) {
            $files[] = self::buildLocalFilePath($directory, $baseName);

            if ($environment !== null) {
                $files[] = self::buildEnvironmentLocalFilePath($directory, $baseName, $environment);
            }
        }

        return $files;
    }

    /**
     * Build environment-specific file path.
     */
    private static function buildEnvironmentFilePath(string $directory, string $baseName, string $environment): string
    {
        $environmentFileName = str_replace('.env', ".env.$environment", $baseName);
        return $directory . '/' . $environmentFileName;
    }

    /**
     * Build local override file path.
     */
    private static function buildLocalFilePath(string $directory, string $baseName): string
    {
        $localFileName = str_replace('.env', '.env.local', $baseName);
        return $directory . '/' . $localFileName;
    }

    /**
     * Build environment-specific local override file path.
     */
    private static function buildEnvironmentLocalFilePath(
        string $directory,
        string $baseName,
        string $environment
    ): string {
        $environmentLocalFileName = str_replace('.env', ".env.$environment.local", $baseName);
        return $directory . '/' . $environmentLocalFileName;
    }

    /**
     * Load multiple files into the environment.
     *
     * @param array $files Array of file paths to load
     * @param array $originalEnvironment Original environment variables
     * @return bool True if any file was loaded successfully
     */
    private static function loadFilesIntoEnvironment(array $files, array $originalEnvironment): bool
    {
        $anyFileLoaded = false;

        foreach ($files as $file) {
            if (file_exists($file)) {
                $fileLoaded = self::loadFileIntoEnvironment($file, $originalEnvironment);
                $anyFileLoaded = $fileLoaded || $anyFileLoaded;
            }
        }

        return $anyFileLoaded;
    }

    /**
     * Load a single file into the environment.
     *
     * @param string $filePath Path to the .env file
     * @param array $originalEnvironment Original environment variables
     * @return bool True if file was loaded successfully
     */
    private static function loadFileIntoEnvironment(string $filePath, array $originalEnvironment): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $lines = self::readFileLines($filePath);
        if ($lines === false) {
            return false;
        }

        foreach ($lines as $line) {
            $parsedVariable = self::parseLine($line);
            if ($parsedVariable !== null) {
                self::setEnvironmentVariableWithOverridePolicy(
                    $parsedVariable['key'],
                    $parsedVariable['value'],
                    $originalEnvironment
                );
            }
        }

        return true;
    }

    /**
     * Parse a single file and return variables as an array.
     *
     * @param string $filePath Path to the .env file
     * @return array Array of parsed variables
     */
    private static function parseFileIntoArray(string $filePath): array
    {
        $variables = [];

        if (!file_exists($filePath)) {
            return $variables;
        }

        $lines = self::readFileLines($filePath);
        if ($lines === false) {
            return $variables;
        }

        foreach ($lines as $line) {
            $parsedVariable = self::parseLine($line);
            if ($parsedVariable !== null) {
                $variables[$parsedVariable['key']] = $parsedVariable['value'];
            }
        }

        return $variables;
    }

    /**
     * Read file lines with proper error handling.
     *
     * @param string $filePath Path to the file
     * @return array|false Array of lines or false on error
     */
    private static function readFileLines(string $filePath)
    {
        return file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    /**
     * Parse a single line and extract key-value pair.
     *
     * @param string $line Line to parse
     * @return array|null Array with 'key' and 'value' or null if invalid
     */
    private static function parseLine(string $line): ?array
    {
        $line = trim($line);

        if (self::shouldSkipLine($line)) {
            return null;
        }

        if (!self::containsKeyValueSeparator($line)) {
            return null;
        }

        $keyValuePair = self::extractKeyValuePair($line);
        if ($keyValuePair === null) {
            return null;
        }

        return [
            'key' => $keyValuePair['key'],
            'value' => self::unquoteValue($keyValuePair['value'])
        ];
    }

    /**
     * Check if line should be skipped (empty or comment).
     */
    private static function shouldSkipLine(string $line): bool
    {
        return empty($line) || strpos($line, self::COMMENT_CHAR) === 0;
    }

    /**
     * Check if line contains key-value separator.
     */
    private static function containsKeyValueSeparator(string $line): bool
    {
        return strpos($line, self::KEY_VALUE_SEPARATOR) !== false;
    }

    /**
     * Extract key-value pair from line.
     *
     * @param string $line Line containing key=value
     * @return array|null Array with 'key' and 'value' or null if invalid
     */
    private static function extractKeyValuePair(string $line): ?array
    {
        $parts = explode(self::KEY_VALUE_SEPARATOR, $line, 2);

        if (count($parts) !== 2) {
            return null;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        if (empty($key)) {
            return null;
        }

        return ['key' => $key, 'value' => $value];
    }

    /**
     * Remove quotes from value if present.
     *
     * @param string $value Value to unquote
     * @return string Unquoted value
     */
    private static function unquoteValue(string $value): string
    {
        $firstChar = $value[0] ?? '';
        $lastChar = $value[-1] ?? '';

        if (in_array($firstChar, self::QUOTE_CHARS, true) && $firstChar === $lastChar) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * Set environment variable with proper override policy for file loading.
     *
     * @param string $key Variable key
     * @param string $value Variable value
     * @param array $originalEnvironment Original environment variables
     */
    private static function setEnvironmentVariableWithOverridePolicy(
        string $key,
        string $value,
        array $originalEnvironment
    ): void {
        // Don't override variables that existed before loading any files
        if (array_key_exists($key, $originalEnvironment)) {
            return;
        }

        // Allow overrides between files (but not from original environment)
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}
