#!/bin/bash

# Script untuk setup domain local permathadi-swara.test
# Script ini akan menambahkan entry ke /etc/hosts dan setup nginx config

DOMAIN="permathadi-swara.test"
IP="127.0.0.1"
NGINX_CONF="nginx/permathadi-swara.test.conf"
NGINX_SITES_DIR="/opt/homebrew/etc/nginx/servers"  # Homebrew nginx
NGINX_SITES_DIR_ALT="/usr/local/etc/nginx/servers"  # Alternative path
HOSTS_FILE="/etc/hosts"

echo "🚀 Setting up local domain: $DOMAIN"

# Check if running on macOS
if [[ "$OSTYPE" != "darwin"* ]]; then
    echo "❌ This script is designed for macOS. Please setup manually."
    exit 1
fi

# 1. Setup hosts file
echo ""
echo "📝 Adding entry to /etc/hosts..."

if grep -q "$DOMAIN" "$HOSTS_FILE"; then
    echo "✅ Domain $DOMAIN already exists in $HOSTS_FILE"
else
    echo "Adding $IP $DOMAIN www.$DOMAIN to $HOSTS_FILE"
    echo "$IP $DOMAIN www.$DOMAIN" | sudo tee -a "$HOSTS_FILE" > /dev/null
    echo "✅ Domain added to hosts file"
fi

# 2. Setup nginx config
echo ""
echo "🔧 Setting up nginx configuration..."

# Detect nginx installation
if [ -d "$NGINX_SITES_DIR" ]; then
    NGINX_DIR="$NGINX_SITES_DIR"
elif [ -d "$NGINX_SITES_DIR_ALT" ]; then
    NGINX_DIR="$NGINX_SITES_DIR_ALT"
else
    echo "⚠️  Nginx sites directory not found. Please install nginx first:"
    echo "   brew install nginx"
    echo ""
    echo "Or manually copy $NGINX_CONF to your nginx sites directory"
    exit 1
fi

# Check if nginx config exists
if [ -f "$NGINX_CONF" ]; then
    # Copy nginx config
    echo "Copying nginx config to $NGINX_DIR..."
    sudo cp "$NGINX_CONF" "$NGINX_DIR/"
    echo "✅ Nginx config copied"
    
    # Test nginx config
    echo ""
    echo "🧪 Testing nginx configuration..."
    if sudo nginx -t; then
        echo "✅ Nginx configuration is valid"
        
        # Reload nginx
        echo ""
        echo "🔄 Reloading nginx..."
        sudo nginx -s reload
        echo "✅ Nginx reloaded"
    else
        echo "❌ Nginx configuration test failed. Please check the config."
        exit 1
    fi
else
    echo "⚠️  Nginx config file not found: $NGINX_CONF"
    echo "Please create it first or run this script from project root"
fi

# 3. Instructions
echo ""
echo "✨ Setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Make sure Laravel Sail is running:"
echo "   ./vendor/bin/sail up -d"
echo ""
echo "2. Update your .env file:"
echo "   APP_URL=http://$DOMAIN"
echo ""
echo "3. Access your application at:"
echo "   http://$DOMAIN"
echo "   http://www.$DOMAIN"
echo ""
echo "💡 Note: If you're not using nginx, you can access directly at:"
echo "   http://localhost (default Sail port)"

