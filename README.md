# EnvLoader

[![CI](https://github.com/lukaszzychal/env-loader-php/workflows/CI/badge.svg)](https://github.com/lukaszzychal/env-loader-php/actions)
[![Code Coverage](https://codecov.io/gh/lukaszzychal/env-loader-php/branch/main/graph/badge.svg)](https://codecov.io/gh/lukaszzychal/env-loader-php)
[![PHP Version](https://img.shields.io/packagist/php-v/lukaszzychal/env-loader.svg)](https://packagist.org/packages/lukaszzychal/env-loader)
[![Latest Stable Version](https://img.shields.io/packagist/v/lukaszzychal/env-loader.svg)](https://packagist.org/packages/lukaszzychal/env-loader)
[![Total Downloads](https://img.shields.io/packagist/dt/lukaszzychal/env-loader.svg)](https://packagist.org/packages/lukaszzychal/env-loader)
[![License](https://img.shields.io/packagist/l/lukaszzychal/env-loader.svg)](https://packagist.org/packages/lukaszzychal/env-loader)
[![PHPStan](https://img.shields.io/badge/PHPStan-Level%208-brightgreen.svg)](https://phpstan.org)
[![Code Style](https://img.shields.io/badge/Code%20Style-PSR--12-blue.svg)](https://www.php-fig.org/psr/psr-12/)

A simple and lightweight .env file loader utility for PHP. This package provides a straightforward way to load environment variables from `.env` files without the overhead of larger packages.

## Features

- ✅ Simple and lightweight
- ✅ No external dependencies
- ✅ Supports quoted values (single and double quotes)
- ✅ Skips comments and empty lines
- ✅ Does not overwrite existing environment variables
- ✅ PHP 8.1+ support
- ✅ Full test coverage
- ✅ Static analysis compliant

## Installation

Install via Composer:

```bash
composer require lukaszzychal/env-loader
```

## Usage

### Basic Usage

```php
<?php

use LukaszZychal\EnvLoader\EnvLoader;

// Load environment variables from .env file
$success = EnvLoader::load('.env');

if ($success) {
    // Get environment variable with default value
    $dbHost = EnvLoader::get('DB_HOST', 'localhost');
    $dbPort = EnvLoader::get('DB_PORT', 3306);
    
    // Check if variable exists
    if (EnvLoader::has('API_KEY')) {
        $apiKey = EnvLoader::get('API_KEY');
    }
}
```

### Load and Return Variables

```php
<?php

use LukaszZychal\EnvLoader\EnvLoader;

// Load and return variables without setting them globally
$variables = EnvLoader::loadAndReturn('.env');

foreach ($variables as $key => $value) {
    echo "$key = $value\n";
}
```

## .env File Format

The package supports standard `.env` file format:

```env
# Database configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=myapp
DB_USER=root
DB_PASSWORD=secret

# API configuration
API_KEY="your-api-key-here"
API_URL='https://api.example.com'

# Feature flags
DEBUG=true
CACHE_ENABLED=false

# Comments are supported
# This is a comment
FEATURE_NEW_UI=true
```

### Supported Features

- **Comments**: Lines starting with `#` are ignored
- **Empty lines**: Blank lines are skipped
- **Quoted values**: Both single and double quotes are supported and automatically removed
- **No overwrite**: Existing environment variables are not overwritten

## API Reference

### `EnvLoader::load(string $filePath): bool`

Loads environment variables from a `.env` file.

**Parameters:**
- `$filePath` (string): Path to the .env file

**Returns:**
- `bool`: `true` if file was loaded successfully, `false` otherwise

### `EnvLoader::get(string $key, mixed $default = null): mixed`

Gets an environment variable value.

**Parameters:**
- `$key` (string): Environment variable key
- `$default` (mixed): Default value if key is not found

**Returns:**
- `mixed`: Environment variable value or default

### `EnvLoader::has(string $key): bool`

Checks if an environment variable exists.

**Parameters:**
- `$key` (string): Environment variable key

**Returns:**
- `bool`: `true` if variable exists, `false` otherwise

### `EnvLoader::loadAndReturn(string $filePath): array`

Loads environment variables from a `.env` file and returns them as an array without setting them globally.

**Parameters:**
- `$filePath` (string): Path to the .env file

**Returns:**
- `array`: Array of loaded environment variables

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

## Code Quality

Run static analysis:

```bash
composer phpstan
```

Check code style:

```bash
composer cs-check
```

Fix code style issues:

```bash
composer cs-fix
```

Run all quality checks:

```bash
composer quality
```

## Requirements

- PHP 8.1 or higher

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Release Management

This project uses automated releases via GitHub Actions. When you push a Git tag, the following happens automatically:

1. Tests and quality checks run
2. GitHub Release is created with changelog
3. Packagist is notified (if webhook configured)

### Creating a Release

Use the included release script:
```bash
./release.sh 1.0.0
```

Or manually:
```bash
git tag v1.0.0
git push origin v1.0.0
```

## Changelog

### 1.0.0
- Initial release
- Basic .env file loading functionality
- Support for quoted values
- Comment and empty line handling
- Non-overwriting behavior for existing environment variables
- Automated release workflow with GitHub Actions

## Author

**Lukasz Zychal**
- GitHub: [@lukaszzychal](https://github.com/lukaszzychal)

## Acknowledgments

This package is inspired by the need for a lightweight alternative to larger .env loading libraries while maintaining simplicity and performance.
