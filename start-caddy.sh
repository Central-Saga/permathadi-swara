#!/bin/bash

# Start Caddy dengan Caddyfile
# Run dengan: ./start-caddy.sh

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CADDYFILE="$SCRIPT_DIR/Caddyfile"

echo "🚀 Starting Caddy..."
echo ""

# Check if Caddy is installed
if ! command -v caddy &> /dev/null; then
    echo "❌ Caddy belum terinstall!"
    echo "   Install dengan: brew install caddy"
    exit 1
fi

# Check if Caddyfile exists
if [ ! -f "$CADDYFILE" ]; then
    echo "❌ Caddyfile tidak ditemukan di: $CADDYFILE"
    exit 1
fi

# Validate Caddyfile
echo "🔍 Validating Caddyfile..."
caddy validate --config "$CADDYFILE"

if [ $? -ne 0 ]; then
    echo "❌ Caddyfile validation failed!"
    exit 1
fi

echo "✅ Caddyfile valid"
echo ""

# Check if already running
if pgrep -f "caddy.*Caddyfile" > /dev/null; then
    echo "⚠️  Caddy sudah running!"
    echo "   Reloading config..."
    sudo caddy reload --config "$CADDYFILE"
    exit 0
fi

# Start Caddy
echo "🚀 Starting Caddy server..."
echo "   Config: $CADDYFILE"
echo "   Access: https://permathadi-swara.test"
echo ""
echo "   Press Ctrl+C to stop"
echo ""

sudo caddy run --config "$CADDYFILE"

