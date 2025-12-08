#!/bin/bash

echo "🚀 Starting Laravel application with SSL..."

# 1. Start Laravel Sail
echo "📦 Starting Docker containers..."
./vendor/bin/sail up -d

# Wait for containers to be ready
echo "⏳ Waiting for containers..."
sleep 5

# 2. Check if Vite is already running
if lsof -i :5173 > /dev/null 2>&1; then
    echo "⚠️  Port 5173 is already in use. Killing existing process..."
    lsof -ti :5173 | xargs kill -9 2>/dev/null
    sleep 2
fi

# 3. Start Vite dev server
echo "⚡ Starting Vite dev server..."
npm run dev > /dev/null 2>&1 &
VITE_PID=$!
sleep 3

# Check if Vite started successfully
if ps -p $VITE_PID > /dev/null; then
    echo "✅ Vite dev server started (PID: $VITE_PID)"
else
    echo "❌ Failed to start Vite dev server"
    exit 1
fi

# 4. Clear Laravel cache
echo "🧹 Clearing Laravel cache..."
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan view:clear
./vendor/bin/sail artisan cache:clear

# 5. Check nginx config
echo "🔍 Checking nginx configuration..."
if [ -f "/Applications/ServBay/package/etc/nginx/vhosts/permathadi-swara.test.conf" ]; then
    echo "✅ Nginx config found"
    # Test nginx config (requires sudo, so just inform)
    echo "⚠️  Please run: sudo nginx -t && sudo nginx -s reload"
else
    echo "⚠️  Nginx config not found. Copying..."
    sudo cp nginx/permathadi-swara.test.conf /Applications/ServBay/package/etc/nginx/vhosts/ 2>/dev/null
    if [ $? -eq 0 ]; then
        echo "✅ Nginx config copied. Please run: sudo nginx -s reload"
    else
        echo "❌ Failed to copy nginx config. Please copy manually:"
        echo "   sudo cp nginx/permathadi-swara.test.conf /Applications/ServBay/package/etc/nginx/vhosts/"
    fi
fi

echo ""
echo "✅ Application is ready!"
echo "🌐 Access at: https://permathadi-swara.test"
echo ""
echo "📝 To stop Vite dev server: kill $VITE_PID"
echo "📝 To stop Docker: ./vendor/bin/sail down"
