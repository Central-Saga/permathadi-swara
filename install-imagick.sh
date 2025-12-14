#!/bin/bash

# Script untuk install Imagick extension di Laravel Sail container
# Imagick diperlukan untuk AVIF support karena GD tidak support AVIF

echo "🚀 Installing Imagick extension for AVIF support..."

# Update package list
apt-get update

# Install ImageMagick library
echo "📦 Installing ImageMagick library..."
apt-get install -y \
    libmagickwand-dev \
    imagemagick

# Install PHP Imagick extension
echo "📦 Installing PHP Imagick extension..."
pecl install imagick

# Enable extension
echo "📦 Enabling Imagick extension..."
docker-php-ext-enable imagick

# Verify installation
echo ""
echo "✅ Verification:"
echo "=================="

php -m | grep -i imagick && echo "✓ Imagick extension: INSTALLED" || echo "✗ Imagick extension: NOT FOUND"

# Check AVIF support
php -r "echo 'Imagick AVIF support: '; echo (extension_loaded('imagick') && in_array('AVIF', Imagick::queryFormats())) ? 'YES ✓' : 'NO ✗'; echo PHP_EOL;"

echo ""
echo "🎉 Installation complete!"
echo ""
echo "⚠️  Note: Extension akan hilang jika container di-rebuild."
echo "   Untuk permanen, buat custom Dockerfile."
