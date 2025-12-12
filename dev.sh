#!/bin/bash

# Script untuk menjalankan development environment dengan Docker Full
# Usage: ./dev.sh

set -e

DOMAIN="permathadi-swara.test"
ENV_FILE=".env"

echo "🚀 Starting development environment (Dockerized)..."

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

# 1. Check /etc/hosts entry
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
        # Try to add with sudo (non-interactive)
        if sudo -n true 2>/dev/null; then
            echo "127.0.0.1 $DOMAIN www.$DOMAIN" | sudo tee -a /etc/hosts > /dev/null
            print_info "Entry berhasil ditambahkan ke /etc/hosts ✓"
        else
            print_warning "Tidak bisa menambahkan entry tanpa sudo. Silakan tambahkan manual."
            read -p "Tekan Enter untuk melanjutkan (pastikan sudah menambahkan entry) atau Ctrl+C untuk keluar..."
        fi
    fi
else
    print_info "Entry untuk $DOMAIN sudah ada di /etc/hosts ✓"
fi

# 2. Check if Sail is available
print_info "Checking Laravel Sail..."
if [ -f "./vendor/bin/sail" ]; then
    SAIL="./vendor/bin/sail"
elif command -v sail &> /dev/null; then
    SAIL="sail"
else
    # Fallback to docker compose
    SAIL="docker compose"
    print_warning "Sail tidak ditemukan, menggunakan docker compose"
fi

# 3. Start Docker containers (including Caddy and Vite)
print_info "Starting Docker containers (App + Caddy + Vite)..."
$SAIL up -d

# Wait for containers to be ready
print_info "Waiting for containers to be ready..."
sleep 5

# Verify containers are running
if ! $SAIL ps 2>/dev/null | grep -q "Up"; then
    print_warning "Beberapa container mungkin belum ready. Cek dengan: $SAIL ps"
else
    print_info "Docker containers running ✓"
fi

# 4. Display status
echo ""
print_info "✅ Development environment siap!"
echo ""
echo "Aplikasi dapat diakses di:"
echo "  🌐 https://$DOMAIN"
echo ""
echo "Services yang berjalan:"
echo "  - Laravel: http://laravel.test:80 (internal)"
echo "  - Caddy: Port 80/443 (reverse proxy)"
echo "  - Vite: http://vite:5173 (internal)"
echo "  - MySQL: Port ${FORWARD_DB_PORT:-3306}"
echo "  - Redis: Port ${FORWARD_REDIS_PORT:-6379}"
echo ""
echo "Untuk melihat logs:"
echo "  $SAIL logs -f"
echo ""
echo "Catatan: Vite dev server sudah berjalan di container, tidak perlu menjalankan 'npm run dev' lagi."
