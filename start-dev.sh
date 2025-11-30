#!/bin/bash

# Script untuk menjalankan semua development services sekaligus
# Usage: ./start-dev.sh

set -e

echo "🚀 Starting Permathadi Swara Development Environment..."
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Sail is available
if ! command -v sail &> /dev/null; then
    echo -e "${YELLOW}⚠️  Sail not found. Using vendor/bin/sail instead...${NC}"
    SAIL="./vendor/bin/sail"
else
    SAIL="sail"
fi

# Start Docker containers
echo -e "${BLUE}📦 Starting Docker containers...${NC}"
$SAIL up -d

# Wait for containers to be ready
echo -e "${BLUE}⏳ Waiting for containers to be ready...${NC}"
sleep 5

# Check if containers are running
if ! $SAIL ps | grep -q "Up"; then
    echo -e "${YELLOW}⚠️  Some containers may not be running. Please check with 'sail ps'${NC}"
fi

# Clear caches
echo -e "${BLUE}🧹 Clearing caches...${NC}"
$SAIL artisan config:clear
$SAIL artisan cache:clear
$SAIL artisan route:clear
$SAIL artisan view:clear

# Start Horizon (queue worker)
echo -e "${GREEN}🔄 Starting Horizon (Queue Worker)...${NC}"
$SAIL artisan horizon > /dev/null 2>&1 &
HORIZON_PID=$!
echo "   Horizon PID: $HORIZON_PID"

# Start NPM dev server
echo -e "${GREEN}📦 Starting NPM dev server...${NC}"
npm run dev > /dev/null 2>&1 &
NPM_PID=$!
echo "   NPM PID: $NPM_PID"

# Save PIDs to file for later cleanup
echo "$HORIZON_PID" > .dev-pids
echo "$NPM_PID" >> .dev-pids

echo ""
echo -e "${GREEN}✅ Development environment started!${NC}"
echo ""
echo "📊 Services running:"
echo "   - Laravel App: http://localhost"
echo "   - Horizon Dashboard: http://localhost/horizon (requires admin login)"
echo "   - Vite Dev Server: http://localhost:5173"
echo ""
echo "🛑 To stop all services, run: ./stop-dev.sh"
echo "   Or manually: kill \$(cat .dev-pids) && sail down"
echo ""

