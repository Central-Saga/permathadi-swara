#!/bin/bash

# Script untuk install image optimization tools di Laravel Sail container
# Jalankan dengan: ./vendor/bin/sail shell < install-image-tools.sh
# Atau: ./vendor/bin/sail exec laravel.test bash -c "$(cat install-image-tools.sh)"

echo "🚀 Installing image optimization tools..."

# Update package list
apt-get update

# Install required tools
echo "📦 Installing jpegoptim, optipng, pngquant, gifsicle..."
apt-get install -y \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle

# Install WebP tools
echo "📦 Installing WebP tools..."
apt-get install -y \
    webp

# Install AVIF tools (libavif)
echo "📦 Installing AVIF tools (libavif-bin)..."
apt-get install -y \
    libavif-bin

# Install SVG optimizer (svgo via npm - optional)
echo "📦 Installing SVGO via npm..."
npm install -g svgo

# Verify installations
echo ""
echo "✅ Verification:"
echo "=================="

if command -v jpegoptim &> /dev/null; then
    echo "✓ jpegoptim: $(jpegoptim --version 2>&1 | head -n1)"
else
    echo "✗ jpegoptim: NOT FOUND"
fi

if command -v optipng &> /dev/null; then
    echo "✓ optipng: $(optipng --version 2>&1 | head -n1)"
else
    echo "✗ optipng: NOT FOUND"
fi

if command -v pngquant &> /dev/null; then
    echo "✓ pngquant: $(pngquant --version 2>&1 | head -n1)"
else
    echo "✗ pngquant: NOT FOUND"
fi

if command -v gifsicle &> /dev/null; then
    echo "✓ gifsicle: $(gifsicle --version 2>&1 | head -n1)"
else
    echo "✗ gifsicle: NOT FOUND"
fi

if command -v cwebp &> /dev/null; then
    echo "✓ cwebp: $(cwebp -version 2>&1 | head -n1)"
else
    echo "✗ cwebp: NOT FOUND"
fi

if command -v avifenc &> /dev/null; then
    echo "✓ avifenc: $(avifenc --version 2>&1 | head -n1)"
else
    echo "✗ avifenc: NOT FOUND"
fi

if command -v svgo &> /dev/null; then
    echo "✓ svgo: $(svgo --version 2>&1 | head -n1)"
else
    echo "✗ svgo: NOT FOUND"
fi

echo ""
echo "🎉 Installation complete!"
echo ""
echo "⚠️  Note: Tools akan hilang jika container di-rebuild."
echo "   Untuk permanen, buat custom Dockerfile atau gunakan volume untuk persist."
