#!/bin/bash
# Production Pusher Fix Script

echo "🔧 Fixing Production Pusher Configuration..."

# 1. Ensure production .env has correct values
echo "📝 Step 1: Check production .env has these variables:"
echo "BROADCAST_DRIVER=pusher"
echo "PUSHER_APP_ID=2086052"
echo "PUSHER_APP_KEY=cfa77b4a5196421b0d98"
echo "PUSHER_APP_SECRET=cd41dcb4ac298eb36ffb"
echo "PUSHER_APP_CLUSTER=ap1"
echo "PUSHER_SCHEME=https"
echo "PUSHER_PORT=443"
echo ""
echo "VITE_PUSHER_APP_KEY=\"\${PUSHER_APP_KEY}\""
echo "VITE_PUSHER_APP_CLUSTER=\"\${PUSHER_APP_CLUSTER}\""
echo "VITE_PUSHER_SCHEME=\"\${PUSHER_SCHEME}\""
echo "VITE_PUSHER_PORT=\"\${PUSHER_PORT}\""
echo ""
echo "SESSION_DRIVER=database (or redis, NOT cookie for broadcasting)"
echo "SESSION_DOMAIN=.storeflow.com.au"
echo ""

# 2. Clear caches
echo "🗑️  Step 2: Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 3. Rebuild assets with production env
echo "🏗️  Step 3: Rebuilding frontend assets..."
npm run build

# 4. Cache config
echo "💾 Step 4: Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "✅ Done! Deploy to production and check browser console for Pusher logs"
