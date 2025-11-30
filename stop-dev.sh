#!/bin/bash

# Script untuk menghentikan semua development services
# Usage: ./stop-dev.sh

set -e

# Colors for output
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color

echo "🛑 Stopping Permathadi Swara Development Environment..."
echo ""

# Check if Sail is available
if ! command -v sail &> /dev/null; then
    SAIL="./vendor/bin/sail"
else
    SAIL="sail"
fi

# Stop processes from PID file
if [ -f .dev-pids ]; then
    echo -e "${YELLOW}🔄 Stopping background processes...${NC}"
    while read pid; do
        if ps -p $pid > /dev/null 2>&1; then
            kill $pid 2>/dev/null || true
            echo "   Stopped process: $pid"
        fi
    done < .dev-pids
    rm .dev-pids
    echo ""
fi

# Stop Horizon
echo -e "${YELLOW}🔄 Stopping Horizon...${NC}"
$SAIL artisan horizon:terminate 2>/dev/null || true

# Stop Docker containers (optional - uncomment if you want to stop containers too)
# echo -e "${YELLOW}📦 Stopping Docker containers...${NC}"
# $SAIL down

echo -e "${GREEN}✅ All services stopped!${NC}"
echo ""

