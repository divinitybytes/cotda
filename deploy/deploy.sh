#!/bin/bash

# Laravel LAMP Deployment Script
# Run this script from the project root directory

set -e

echo "🚀 Starting Laravel deployment for LAMP stack..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: This script must be run from the Laravel project root directory"
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "📋 PHP Version: $PHP_VERSION"

# Install/Update Composer dependencies for production
echo "📦 Installing Composer dependencies..."
composer install --optimize-autoloader --no-dev

# Generate application key if not set
if grep -q "APP_KEY=$" .env 2>/dev/null || [ ! -f .env ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Clear and cache configuration
echo "⚙️ Optimizing configuration..."
php artisan config:clear
php artisan config:cache

# Clear and cache routes
echo "🛣️ Optimizing routes..."
php artisan route:clear
php artisan route:cache

# Clear and cache views
echo "👁️ Optimizing views..."
php artisan view:clear
php artisan view:cache

# Install NPM dependencies and build assets
if command -v npm &> /dev/null; then
    echo "🎨 Building front-end assets..."
    npm ci --only=production
    npm run build
else
    echo "⚠️ Warning: npm not found. Please build assets manually with 'npm run build'"
fi

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Create storage symlink if it doesn't exist
if [ ! -L "public/storage" ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link
fi

# Set proper file permissions
echo "🔒 Setting file permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Clear all caches one final time
echo "🧹 Final cache cleanup..."
php artisan cache:clear
php artisan config:clear

echo "✅ Deployment completed successfully!"
echo ""
echo "📝 Next steps:"
echo "1. Update your .env file with production values"
echo "2. Configure your Apache virtual host"
echo "3. Set up SSL certificates"
echo "4. Configure your database"
echo "5. Test the application" 