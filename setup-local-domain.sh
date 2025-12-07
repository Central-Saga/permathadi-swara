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
NGINX_DIR=""
if [ -d "$NGINX_SITES_DIR" ]; then
    # Homebrew nginx
    NGINX_DIR="$NGINX_SITES_DIR"
elif [ -d "$NGINX_SITES_DIR_ALT" ]; then
    # Alternative Homebrew path
    NGINX_DIR="$NGINX_SITES_DIR_ALT"
elif [ -d "/Applications/ServBay/package/etc/nginx/vhosts" ]; then
    # ServBay nginx
    NGINX_DIR="/Applications/ServBay/package/etc/nginx/vhosts"
    echo "✅ Detected ServBay nginx installation"
else
    echo "⚠️  Nginx sites directory not found."
    echo ""
    echo "Options:"
    echo "1. Install nginx via Homebrew: brew install nginx"
    echo "2. Use ServBay (if installed)"
    echo "3. Manually copy $NGINX_CONF to your nginx vhosts/servers directory"
    echo ""
    echo "For ServBay, copy to: /Applications/ServBay/package/etc/nginx/vhosts/"
    echo "For Homebrew, copy to: /opt/homebrew/etc/nginx/servers/ or /usr/local/etc/nginx/servers/"
    exit 1
fi

# 3. Setup SSL certificate
echo ""
echo "🔐 Setting up SSL certificate..."

SSL_DIR="/Applications/ServBay/ssl/private/tls-certs/permathadi-swara.test"
SSL_CERT="$SSL_DIR/permathadi-swara.test+1.pem"
SSL_KEY="$SSL_DIR/permathadi-swara.test+1-key.pem"

# Check if mkcert is installed
if ! command -v mkcert &> /dev/null; then
    echo "⚠️  mkcert not found. Installing via Homebrew..."
    if command -v brew &> /dev/null; then
        brew install mkcert
        mkcert -install
    else
        echo "❌ Homebrew not found. Please install mkcert manually:"
        echo "   brew install mkcert"
        echo "   mkcert -install"
        exit 1
    fi
fi

# Create SSL directory if it doesn't exist
if [ ! -d "$SSL_DIR" ]; then
    mkdir -p "$SSL_DIR"
    echo "✅ Created SSL directory"
fi

# Generate certificate if it doesn't exist
if [ ! -f "$SSL_CERT" ] || [ ! -f "$SSL_KEY" ]; then
    echo "Generating SSL certificate for $DOMAIN..."
    cd "$SSL_DIR"
    mkcert permathadi-swara.test www.permathadi-swara.test
    cd - > /dev/null
    echo "✅ SSL certificate generated"
else
    echo "✅ SSL certificate already exists"
fi

# Check if nginx config exists
if [ -f "$NGINX_CONF" ]; then
    # Copy nginx config
    echo ""
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
        if sudo nginx -s reload 2>/dev/null; then
            echo "✅ Nginx reloaded"
        else
            echo "⚠️  Could not reload nginx automatically."
            if [ -d "/Applications/ServBay" ]; then
                echo "   Please reload nginx via ServBay application or run:"
                echo "   sudo /Applications/ServBay/sbin/nginx -s reload"
            else
                echo "   Please reload nginx manually: sudo nginx -s reload"
            fi
        fi
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
echo "   APP_URL=https://$DOMAIN"
echo ""
echo "3. Access your application at:"
echo "   https://$DOMAIN (HTTPS - recommended)"
echo "   https://www.$DOMAIN"
echo "   http://$DOMAIN (will redirect to HTTPS)"
echo ""
echo "💡 Note: If you're not using nginx, you can access directly at:"
echo "   http://localhost (default Sail port)"

