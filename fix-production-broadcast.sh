#!/bin/bash
# Fix Production Broadcasting

echo "🔧 Fixing production broadcasting..."

# This should be run on PRODUCTION server
# ssh josh@storeflow.com.au

echo "Step 1: Update .env"
echo "Change: BROADCAST_DRIVER=log"
echo "To:     BROADCAST_DRIVER=pusher"
echo ""
echo "Run on production:"
echo "sed -i 's/BROADCAST_DRIVER=log/BROADCAST_DRIVER=pusher/' .env"
echo ""
echo "Step 2: Clear caches"
echo "php artisan config:clear"
echo "php artisan cache:clear"
echo "php artisan route:clear"
echo ""
echo "Step 3: Recache config"
echo "php artisan config:cache"
echo "php artisan route:cache"
echo ""
echo "Step 4: Test"
echo "Create a new order - it should appear instantly!"
