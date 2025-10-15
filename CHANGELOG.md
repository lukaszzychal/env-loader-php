# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2024-01-XX

### Added
- Environment-specific file support (.env.dev, .env.prod, .env.staging, etc.)
- Local override file support (.env.local, .env.dev.local, etc.)
- Enhanced `load()` method with environment and local override parameters
- Enhanced `loadAndReturn()` method with environment and local override parameters
- Proper file loading order with override precedence
- Comprehensive test suite for new functionality
- Updated documentation with examples and API reference

### Changed
- `EnvLoader::load()` method now accepts optional environment and local override parameters
- `EnvLoader::loadAndReturn()` method now accepts optional environment and local override parameters
- File loading order: base -> environment -> local -> environment.local

### Features
- Backward compatible with existing code
- Professional-grade environment management
- Local developer override support
- Environment-specific configuration files

## [1.0.0] - 2024-01-XX

### Added
- Initial release of EnvLoader package
- `EnvLoader::load()` method to load environment variables from .env files
- `EnvLoader::get()` method to retrieve environment variables with default values
- `EnvLoader::has()` method to check if environment variables exist
- `EnvLoader::loadAndReturn()` method to load and return variables as array
- Support for quoted values (single and double quotes)
- Automatic comment and empty line skipping
- Non-overwriting behavior for existing environment variables
- Comprehensive test suite with PHPUnit
- CI/CD pipeline with GitHub Actions
- Static analysis with PHPStan
- Code style checking with PHP CodeSniffer
- Full documentation and examples

### Features
- PHP 8.1+ support
- No external dependencies
- Lightweight and fast
- PSR-4 autoloading
- MIT License

## [0.1.0] - 2024-01-XX

### Added
- Initial development version
- Basic .env file parsing functionality
