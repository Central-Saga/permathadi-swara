#!/bin/bash

echo "🚀 Setup Caddy untuk Permathadi Swara (Manual)"
echo ""

# Check if Caddy is installed
if ! command -v caddy &> /dev/null; then
    echo "❌ Caddy belum terinstall"
    echo ""
    echo "Install Caddy dengan salah satu cara berikut:"
    echo ""
    echo "1. Homebrew (recommended):"
    echo "   brew install caddy"
    echo ""
    echo "2. Download langsung:"
    echo "   curl -O https://caddyserver.com/api/download?os=darwin&arch=arm64"
    echo "   (atau kunjungi https://caddyserver.com/download)"
    echo ""
    exit 1
fi

echo "✅ Caddy sudah terinstall: $(which caddy)"
echo ""

# Check if Caddyfile exists
if [ ! -f "Caddyfile" ]; then
    echo "❌ Caddyfile tidak ditemukan!"
    exit 1
fi

echo "✅ Caddyfile ditemukan"
echo ""

# Validate Caddyfile
echo "🔍 Validating Caddyfile..."
caddy validate --config Caddyfile

if [ $? -ne 0 ]; then
    echo "❌ Caddyfile validation failed!"
    exit 1
fi

echo "✅ Caddyfile valid"
echo ""

# Check if port 443 is available
if lsof -i :443 > /dev/null 2>&1; then
    echo "⚠️  Port 443 sudah digunakan!"
    echo "   Process yang menggunakan port 443:"
    lsof -i :443 | head -3
    echo ""
    echo "   Stop process tersebut atau gunakan port lain"
    exit 1
fi

echo "✅ Port 443 tersedia"
echo ""

# Check if Laravel is running
if ! curl -s http://127.0.0.1:8080 > /dev/null 2>&1; then
    echo "⚠️  Laravel tidak running di port 8080"
    echo "   Jalankan: ./vendor/bin/sail up -d"
    echo ""
fi

# Check if Vite is running
if ! curl -s http://127.0.0.1:5173 > /dev/null 2>&1; then
    echo "⚠️  Vite dev server tidak running di port 5173"
    echo "   Jalankan: npm run dev"
    echo ""
fi

echo "📋 Instruksi:"
echo ""
echo "1. Start Caddy (di terminal terpisah atau background):"
echo "   sudo caddy run --config Caddyfile"
echo ""
echo "   Atau untuk background:"
echo "   sudo caddy start --config Caddyfile"
echo ""
echo "2. Akses aplikasi:"
echo "   https://permathadi-swara.test"
echo ""
echo "3. Stop Caddy:"
echo "   sudo caddy stop"
echo ""
echo "4. Reload config (jika sudah running):"
echo "   sudo caddy reload --config Caddyfile"
echo ""

