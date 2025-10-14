<?php

require_once 'vendor/autoload.php';

use LukaszZychal\EnvLoader\EnvLoader;

// Example .env file content
$envContent = <<<ENV
# Database configuration
DB_HOST=localhost
DB_PORT=3306
DB_NAME=myapp
DB_USER=root
DB_PASSWORD="secret password"

# API configuration
API_KEY='your-api-key-here'
API_URL=https://api.example.com

# Feature flags
DEBUG=true
CACHE_ENABLED=false

# Comments are supported
FEATURE_NEW_UI=true
ENV;

// Create a temporary .env file for demonstration
$envFile = sys_get_temp_dir() . '/example.env';
file_put_contents($envFile, $envContent);

echo "=== EnvLoader Example ===\n\n";

// Load environment variables
echo "Loading environment variables from: $envFile\n";
$success = EnvLoader::load($envFile);

if ($success) {
    echo "✅ Environment variables loaded successfully!\n\n";
    
    // Get individual variables
    echo "Database Configuration:\n";
    echo "  Host: " . EnvLoader::get('DB_HOST', 'localhost') . "\n";
    echo "  Port: " . EnvLoader::get('DB_PORT', 3306) . "\n";
    echo "  Name: " . EnvLoader::get('DB_NAME') . "\n";
    echo "  User: " . EnvLoader::get('DB_USER') . "\n";
    echo "  Password: " . EnvLoader::get('DB_PASSWORD') . "\n\n";
    
    echo "API Configuration:\n";
    echo "  Key: " . EnvLoader::get('API_KEY') . "\n";
    echo "  URL: " . EnvLoader::get('API_URL') . "\n\n";
    
    echo "Feature Flags:\n";
    echo "  Debug: " . (EnvLoader::get('DEBUG') === 'true' ? 'Enabled' : 'Disabled') . "\n";
    echo "  Cache: " . (EnvLoader::get('CACHE_ENABLED') === 'true' ? 'Enabled' : 'Disabled') . "\n";
    echo "  New UI: " . (EnvLoader::get('FEATURE_NEW_UI') === 'true' ? 'Enabled' : 'Disabled') . "\n\n";
    
    // Check if variables exist
    echo "Variable Existence Check:\n";
    echo "  DB_HOST exists: " . (EnvLoader::has('DB_HOST') ? 'Yes' : 'No') . "\n";
    echo "  NON_EXISTENT exists: " . (EnvLoader::has('NON_EXISTENT') ? 'Yes' : 'No') . "\n\n";
    
    // Demonstrate loadAndReturn method
    echo "Load and Return Method:\n";
    $variables = EnvLoader::loadAndReturn($envFile);
    foreach ($variables as $key => $value) {
        echo "  $key = $value\n";
    }
} else {
    echo "❌ Failed to load environment variables!\n";
}

// Clean up
unlink($envFile);

echo "\n=== Example Complete ===\n";
