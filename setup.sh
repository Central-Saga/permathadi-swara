#!/bin/bash

# Script untuk setup project dari awal (fresh clone)
# Usage: ./setup.sh

set -e

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo "🚀 Setting up Permathadi Swara Project..."
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${BLUE}📝 Creating .env file from .env.example...${NC}"
    cp .env.example .env
    echo -e "${GREEN}✅ .env file created${NC}"
else
    echo -e "${YELLOW}⚠️  .env file already exists, skipping...${NC}"
fi

# Install PHP dependencies
echo -e "${BLUE}📦 Installing PHP dependencies...${NC}"
composer install --no-interaction
echo -e "${GREEN}✅ PHP dependencies installed${NC}"

# Install NPM dependencies
echo -e "${BLUE}📦 Installing NPM dependencies...${NC}"
npm install
echo -e "${GREEN}✅ NPM dependencies installed${NC}"

# Generate application key
if ! grep -q "APP_KEY=base64" .env 2>/dev/null; then
    echo -e "${BLUE}🔑 Generating application key...${NC}"
    php artisan key:generate
    echo -e "${GREEN}✅ Application key generated${NC}"
else
    echo -e "${YELLOW}⚠️  Application key already exists, skipping...${NC}"
fi

# Start Docker containers
echo -e "${BLUE}🐳 Starting Docker containers...${NC}"
if command -v sail &> /dev/null; then
    SAIL="sail"
else
    SAIL="./vendor/bin/sail"
fi

$SAIL up -d

# Wait for containers to be ready
echo -e "${BLUE}⏳ Waiting for containers to be ready...${NC}"
sleep 10

# Run migrations
echo -e "${BLUE}🗄️  Running database migrations...${NC}"
$SAIL artisan migrate --force
echo -e "${GREEN}✅ Database migrations completed${NC}"

# Build assets
echo -e "${BLUE}🏗️  Building frontend assets...${NC}"
npm run build
echo -e "${GREEN}✅ Frontend assets built${NC}"

# Clear caches
echo -e "${BLUE}🧹 Clearing caches...${NC}"
$SAIL artisan config:clear
$SAIL artisan cache:clear
$SAIL artisan route:clear
$SAIL artisan view:clear
echo -e "${GREEN}✅ Caches cleared${NC}"

echo ""
echo -e "${GREEN}✅ Setup completed successfully!${NC}"
echo ""
echo "📊 Next steps:"
echo "   1. Update .env file with your configuration (database, mail, etc.)"
echo "   2. Run './start-dev.sh' to start development environment"
echo "   3. Access the application at http://localhost"
echo ""

