#!/bin/bash

# Script untuk menjalankan development environment dengan Docker dan Caddy
# Usage: ./dev.sh

set -e

DOMAIN="permathadi-swara.test"
CADDYFILE="Caddyfile"
ENV_FILE=".env"

echo "🚀 Starting development environment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored messages
print_info() {
    echo -e "${GREEN}ℹ️  $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# 1. Check Caddy installation
print_info "Checking Caddy installation..."
if ! command -v caddy &> /dev/null; then
    print_error "Caddy tidak ditemukan!"
    echo ""
    echo "Silakan install Caddy terlebih dahulu:"
    echo "  brew install caddy"
    echo ""
    echo "Atau download dari: https://caddyserver.com/download"
    exit 1
fi
print_info "Caddy sudah terinstall ✓"

# 2. Check /etc/hosts entry
print_info "Checking /etc/hosts entry..."
if ! grep -q "$DOMAIN" /etc/hosts 2>/dev/null; then
    print_warning "Entry untuk $DOMAIN belum ada di /etc/hosts"
    echo ""
    echo "Silakan tambahkan entry berikut ke /etc/hosts:"
    echo "  127.0.0.1 $DOMAIN www.$DOMAIN"
    echo ""
    echo "Atau jalankan dengan sudo untuk menambahkan otomatis:"
    echo "  sudo ./dev.sh"
    echo ""

    # Try to add with sudo if running as root or with sudo
    if [ "$EUID" -eq 0 ]; then
        echo "127.0.0.1 $DOMAIN www.$DOMAIN" >> /etc/hosts
        print_info "Entry berhasil ditambahkan ke /etc/hosts ✓"
    else
        print_warning "Tidak bisa menambahkan entry tanpa sudo. Silakan tambahkan manual."
        read -p "Tekan Enter untuk melanjutkan (pastikan sudah menambahkan entry) atau Ctrl+C untuk keluar..."
    fi
else
    print_info "Entry untuk $DOMAIN sudah ada di /etc/hosts ✓"
fi

# 3. Check and update APP_PORT in .env
print_info "Checking APP_PORT configuration..."
if [ ! -f "$ENV_FILE" ]; then
    print_warning "File .env tidak ditemukan. Pastikan sudah copy dari .env.example"
    exit 1
fi

# Check if APP_PORT is set to 80, change to 8080
if grep -q "^APP_PORT=80" "$ENV_FILE" 2>/dev/null || ! grep -q "^APP_PORT=" "$ENV_FILE" 2>/dev/null; then
    print_warning "APP_PORT perlu diubah ke 8080 (Caddy akan menggunakan port 80)"

    if grep -q "^APP_PORT=" "$ENV_FILE" 2>/dev/null; then
        # Replace existing APP_PORT
        if [[ "$OSTYPE" == "darwin"* ]]; then
            # macOS
            sed -i '' "s/^APP_PORT=.*/APP_PORT=8080/" "$ENV_FILE"
        else
            # Linux
            sed -i "s/^APP_PORT=.*/APP_PORT=8080/" "$ENV_FILE"
        fi
    else
        # Add APP_PORT if not exists
        echo "APP_PORT=8080" >> "$ENV_FILE"
    fi
    print_info "APP_PORT sudah diubah ke 8080 ✓"
else
    print_info "APP_PORT sudah dikonfigurasi dengan benar ✓"
fi

# 4. Validate Caddyfile
print_info "Validating Caddyfile..."
if [ ! -f "$CADDYFILE" ]; then
    print_error "Caddyfile tidak ditemukan!"
    exit 1
fi

if ! sudo caddy validate --config "$CADDYFILE" 2>/dev/null; then
    print_error "Caddyfile validation gagal!"
    sudo caddy validate --config "$CADDYFILE"
    exit 1
fi
print_info "Caddyfile valid ✓"

# 5. Check Caddy status and start if needed
print_info "Checking Caddy status..."
CADDY_RUNNING=false

# Check if Caddy is running
if sudo caddy status 2>/dev/null | grep -q "running"; then
    CADDY_RUNNING=true
    print_info "Caddy sudah running ✓"
else
    print_warning "Caddy belum running, akan di-start..."

    # Try to start Caddy
    if sudo caddy start --config "$CADDYFILE" 2>/dev/null; then
        print_info "Caddy berhasil di-start ✓"
        CADDY_RUNNING=true
        sleep 2
    else
        print_error "Gagal start Caddy. Coba jalankan manual:"
        echo "  sudo caddy start --config $CADDYFILE"
        exit 1
    fi
fi

# 6. Check if Sail is available
print_info "Checking Laravel Sail..."
if ! command -v sail &> /dev/null; then
    SAIL="./vendor/bin/sail"
else
    SAIL="sail"
fi

# 7. Start Docker containers
print_info "Starting Docker containers..."
$SAIL up -d

# Wait for containers to be ready
print_info "Waiting for containers to be ready..."
sleep 5

# Verify containers are running
if ! $SAIL ps | grep -q "Up"; then
    print_warning "Beberapa container mungkin belum ready. Cek dengan: $SAIL ps"
else
    print_info "Docker containers running ✓"
fi

# 8. Start NPM dev server
print_info "Starting NPM dev server..."
echo ""
print_info "✅ Development environment siap!"
echo ""
echo "Aplikasi dapat diakses di:"
echo "  🌐 https://$DOMAIN"
echo ""
echo "Pastikan:"
echo "  - Caddy running: sudo caddy status"
echo "  - Docker containers running: $SAIL ps"
echo "  - Vite dev server akan start di bawah ini..."
echo ""

# Start NPM dev server (this will block)
npm run dev
