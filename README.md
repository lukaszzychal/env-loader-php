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
- ✅ Environment-specific files (.env.dev, .env.prod, etc.)
- ✅ Local override files (.env.local, .env.dev.local, etc.)
- ✅ PHP 8.1+ support
- ✅ Full test coverage
- ✅ Static analysis compliant
- ✅ Automated dependency updates with Dependabot

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

### Environment-Specific Files

```php
<?php

use LukaszZychal\EnvLoader\EnvLoader;

// Load with environment-specific files
EnvLoader::load('.env', 'dev'); // Loads .env and .env.dev

// File loading order (later files override earlier ones):
// 1. .env
// 2. .env.dev
// 3. .env.local
// 4. .env.dev.local
```

### Local Override Files

```php
<?php

use LukaszZychal\EnvLoader\EnvLoader;

// Load with local overrides (default behavior)
EnvLoader::load('.env', 'dev', true);

// Disable local overrides
EnvLoader::load('.env', 'dev', false);
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

The package supports standard `.env` file format with environment-specific and local override files:

### File Structure Example

```
project/
├── .env                 # Base configuration
├── .env.dev            # Development environment
├── .env.prod           # Production environment
├── .env.staging        # Staging environment
├── .env.local          # Local overrides (not in git)
├── .env.dev.local      # Local dev overrides (not in git)
└── .env.prod.local     # Local prod overrides (not in git)
```

### Base .env File

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
DEBUG=false
CACHE_ENABLED=true

# Comments are supported
# This is a comment
FEATURE_NEW_UI=false
```

### Environment-Specific Files

**.env.dev** (Development):
```env
# Override for development
DEBUG=true
CACHE_ENABLED=false
DB_NAME=myapp_dev
API_URL='https://dev-api.example.com'
```

**.env.prod** (Production):
```env
# Production overrides
DEBUG=false
CACHE_ENABLED=true
DB_HOST=prod-db.example.com
API_URL='https://api.example.com'
```

### Local Override Files

**.env.local** (Local developer overrides):
```env
# Personal local settings (not committed to git)
DB_PASSWORD=my_local_password
API_KEY=my_local_api_key
DEBUG=true
```

**.env.dev.local** (Local dev overrides):
```env
# Personal dev settings
DB_PASSWORD=dev_password
FEATURE_NEW_UI=true
```

### Supported Features

- **Comments**: Lines starting with `#` are ignored
- **Empty lines**: Blank lines are skipped
- **Quoted values**: Both single and double quotes are supported and automatically removed
- **No overwrite**: Existing environment variables are not overwritten

## API Reference

### `EnvLoader::load(string $filePath, ?string $environment = null, bool $loadLocalOverrides = true): bool`

Loads environment variables from `.env` files with environment-specific and local override support.

**Parameters:**
- `$filePath` (string): Path to the base .env file
- `$environment` (string|null): Environment name (e.g., 'dev', 'prod', 'staging')
- `$loadLocalOverrides` (bool): Whether to load .local override files

**Returns:**
- `bool`: `true` if any file was loaded successfully, `false` otherwise

**File Loading Order:**
1. Base file (e.g., `.env`)
2. Environment-specific file (e.g., `.env.dev`)
3. Local override file (e.g., `.env.local`)
4. Environment-specific local override (e.g., `.env.dev.local`)

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

### `EnvLoader::loadAndReturn(string $filePath, ?string $environment = null, bool $loadLocalOverrides = true): array`

Loads environment variables from `.env` files and returns them as an array with environment-specific and local override support.

**Parameters:**
- `$filePath` (string): Path to the base .env file
- `$environment` (string|null): Environment name (e.g., 'dev', 'prod', 'staging')
- `$loadLocalOverrides` (bool): Whether to load .local override files

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

## Dependency Management

This project uses [Dependabot](https://dependabot.com/) to automatically:

- 🔄 **Monitor dependencies** for security vulnerabilities
- 📦 **Update Composer packages** weekly
- ⚙️ **Update GitHub Actions** weekly
- 🛡️ **Create security PRs** for critical vulnerabilities
- 📋 **Group minor/patch updates** to reduce PR noise

Dependabot will create pull requests for dependency updates. Review and merge them to keep your dependencies secure and up-to-date.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Community & Support

- Join the conversation on [GitHub Discussions](https://github.com/lukaszzychal/env-loader-php/discussions) to ask questions, share ideas, or get help from the community.
- Report bugs or request features via [GitHub Issues](https://github.com/lukaszzychal/env-loader-php/issues).

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
