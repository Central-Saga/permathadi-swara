#!/bin/bash

# Script untuk install Imagick extension di Laravel Sail container
# Imagick diperlukan untuk AVIF support karena GD tidak support AVIF
#
# CARA MENGGUNAKAN:
# 1. Masuk ke container: ./vendor/bin/sail shell
# 2. Di dalam container, jalankan: bash /var/www/html/install-imagick.sh
# ATAU
# 1. Jalankan dari host: cat install-imagick.sh | ./vendor/bin/sail shell
# ATAU
# 1. Jalankan langsung: ./vendor/bin/sail exec laravel.test bash /var/www/html/install-imagick.sh

echo "🚀 Installing Imagick extension for AVIF support..."

# Check if running inside container
if ! command -v php &> /dev/null; then
    echo "❌ ERROR: Script harus dijalankan di dalam Laravel Sail container!"
    echo ""
    echo "Cara menjalankan:"
    echo "  1. Masuk ke container: ./vendor/bin/sail shell"
    echo "  2. Jalankan: bash /var/www/html/install-imagick.sh"
    echo ""
    echo "ATAU dari host:"
    echo "  cat install-imagick.sh | ./vendor/bin/sail shell"
    echo ""
    exit 1
fi

# Update package list
echo "📦 Updating package list..."
apt-get update -qq

# Install ImageMagick library
echo "📦 Installing ImageMagick library..."
apt-get install -y -qq \
    libmagickwand-dev \
    imagemagick \
    pkg-config

# Install PHP Imagick extension
echo "📦 Installing PHP Imagick extension (ini mungkin memakan waktu beberapa menit)..."
pecl install imagick <<< ""

# Enable extension
echo "📦 Enabling Imagick extension..."
docker-php-ext-enable imagick

# Restart PHP-FPM (jika ada)
if command -v service &> /dev/null; then
    echo "📦 Restarting PHP-FPM..."
    service php8.4-fpm restart 2>/dev/null || true
fi

# Verify installation
echo ""
echo "✅ Verification:"
echo "=================="

if php -m | grep -i imagick > /dev/null; then
    echo "✓ Imagick extension: INSTALLED"

    # Check AVIF support
    AVIF_SUPPORT=$(php -r "echo (extension_loaded('imagick') && in_array('AVIF', Imagick::queryFormats())) ? 'YES' : 'NO';" 2>/dev/null)
    if [ "$AVIF_SUPPORT" = "YES" ]; then
        echo "✓ AVIF support: YES ✓"
    else
        echo "✗ AVIF support: NO ✗"
        echo "  (Imagick terinstall tapi AVIF format tidak terdeteksi)"
    fi
else
    echo "✗ Imagick extension: NOT FOUND"
    echo ""
    echo "⚠️  Installation mungkin gagal. Coba jalankan manual:"
    echo "  pecl install imagick"
    echo "  docker-php-ext-enable imagick"
fi

echo ""
echo "🎉 Installation complete!"
echo ""
echo "⚠️  Note: Extension akan hilang jika container di-rebuild."
echo "   Untuk permanen, buat custom Dockerfile."
