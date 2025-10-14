# Publishing to Packagist

This document provides instructions for publishing the EnvLoader package to Packagist.

## Prerequisites

1. A GitHub account
2. A Packagist account (sign up at https://packagist.org)
3. The repository pushed to GitHub

## Steps to Publish

### 1. Push to GitHub

First, make sure your code is pushed to GitHub:

```bash
git add .
git commit -m "Initial release of EnvLoader package"
git push origin main
```

### 2. Create Packagist Account

1. Go to https://packagist.org
2. Sign up or log in
3. Connect your GitHub account for automatic updates

### 3. Submit Package

1. Go to https://packagist.org/packages/submit
2. Enter your GitHub repository URL: `https://github.com/lukaszzychal/env-loader-php`
3. Click "Check" to validate the package
4. Click "Submit" to publish

### 4. Configure Auto-Update (Recommended)

1. Go to your package page on Packagist
2. Click "Settings"
3. Enable "Auto-update" using GitHub webhook
4. Copy the webhook URL provided by Packagist
5. Go to your GitHub repository settings → Webhooks
6. Add the webhook URL with content type "application/json"

**Webhook URL Format:**
```
https://packagist.org/api/github?username=YOUR_USERNAME&apiToken=YOUR_API_TOKEN
```

**Alternative: Manual Packagist Updates**
If you prefer manual updates, you can trigger Packagist updates by visiting:
```
https://packagist.org/packages/lukaszzychal/env-loader
```
And clicking the "Update" button after each release.

## Version Management

### Automated Releases

This project includes automated release workflows that trigger when you push Git tags:

1. **Update version in `composer.json`**
2. **Update `CHANGELOG.md`**
3. **Create and push a Git tag:**

```bash
# Create the tag
git tag v1.0.0

# Push the tag (this triggers the release workflow)
git push origin v1.0.0
```

### What Happens Automatically

When you push a tag, the following happens automatically:

1. **Tests run** to ensure code quality
2. **Static analysis** runs (PHPStan Level 8)
3. **Code style checks** run (PSR-12)
4. **GitHub Release** is created with:
   - Automatic changelog generation
   - Installation instructions
   - Asset uploads
5. **Packagist notification** (if webhook is configured)

### Manual Release Process

If you prefer manual releases:

1. Update version in `composer.json`
2. Update `CHANGELOG.md`
3. Create a Git tag:

```bash
git tag v1.0.0
git push origin v1.0.0
```

### Semantic Versioning

Follow semantic versioning (MAJOR.MINOR.PATCH):
- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

## Testing Before Release

Run all quality checks:

```bash
composer quality
composer test
```

## Package Validation

Ensure your package meets Packagist requirements:

- ✅ Valid `composer.json`
- ✅ PSR-4 autoloading
- ✅ Proper namespacing
- ✅ No syntax errors
- ✅ Includes tests
- ✅ Has documentation

## Post-Publication

After publishing:

1. Update your README with the Packagist installation command
2. Add badges to your README for build status, coverage, etc.
3. Consider adding the package to awesome-php lists
4. Share on social media or relevant communities

## Maintenance

- Keep dependencies updated
- Monitor for security vulnerabilities
- Respond to issues and pull requests
- Release updates regularly
- Maintain backward compatibility when possible
