#!/bin/bash

# EnvLoader Release Script
# Usage: ./release.sh [version]
# Example: ./release.sh 1.0.0

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if version is provided
if [ $# -eq 0 ]; then
    print_error "Please provide a version number"
    echo "Usage: $0 <version>"
    echo "Example: $0 1.0.0"
    exit 1
fi

VERSION=$1
TAG="v$VERSION"

# Validate version format (basic check)
if ! [[ $VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    print_error "Invalid version format. Use semantic versioning (e.g., 1.0.0)"
    exit 1
fi

print_status "Starting release process for version $VERSION"

# Check if we're on main branch
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ]; then
    print_warning "You're not on the main branch (current: $CURRENT_BRANCH)"
    read -p "Do you want to continue? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Check if working directory is clean
if ! git diff-index --quiet HEAD --; then
    print_error "Working directory is not clean. Please commit or stash changes."
    exit 1
fi

# Check if tag already exists
if git tag -l | grep -q "^$TAG$"; then
    print_error "Tag $TAG already exists"
    exit 1
fi

# Note: Version is managed by Git tags for Packagist
print_status "Version $VERSION will be managed by Git tag (Packagist standard)"

# Update CHANGELOG.md
print_status "Updating CHANGELOG.md"
CURRENT_DATE=$(date +"%Y-%m-%d")
CHANGELOG_ENTRY="## [$VERSION] - $CURRENT_DATE

### Added
- Release $VERSION

### Changed
- Updated dependencies and documentation

"

# Add changelog entry at the top (after the title and before ## [Unreleased])
sed -i.bak "2i\\
$CHANGELOG_ENTRY" CHANGELOG.md
rm -f CHANGELOG.md.bak

# Run tests
print_status "Running tests..."
composer test

# Run code quality checks
print_status "Running code quality checks..."
composer quality

# Commit changes
print_status "Committing release changes..."
git add CHANGELOG.md
git commit -m "Release version $VERSION"

# Create tag
print_status "Creating tag $TAG..."
git tag -a "$TAG" -m "Release version $VERSION"

# Push changes and tag
print_status "Pushing changes and tag to remote..."
git push origin main
git push origin "$TAG"

print_status "Release $VERSION completed successfully!"
print_status "GitHub Actions will now create the release automatically."
print_status "Visit: https://github.com/$(git config --get remote.origin.url | sed 's/.*github.com[:/]\([^.]*\).*/\1/')/actions"

# Instructions for Packagist
echo
print_warning "Next steps:"
echo "1. Wait for GitHub Actions to complete the release"
echo "2. Update Packagist manually or configure webhook for automatic updates"
echo "3. Visit: https://packagist.org/packages/lukaszzychal/env-loader"
