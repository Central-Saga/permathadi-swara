#!/bin/bash

# Simple script untuk menjalankan docker dan npm run dev
# Usage: ./dev.sh

set -e

echo "🚀 Starting development environment..."

# Check if Sail is available
if ! command -v sail &> /dev/null; then
    SAIL="./vendor/bin/sail"
else
    SAIL="sail"
fi

# Start Docker containers
echo "📦 Starting Docker containers..."
$SAIL up -d

# Wait a bit for containers to be ready
sleep 3

# Start NPM dev server
echo "⚡ Starting NPM dev server..."
npm run dev

