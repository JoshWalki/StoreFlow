#!/bin/bash
# Setup Push Notifications for StoreFlow PWA

echo "🔔 Setting up Push Notifications..."

# Step 1: Install web-push library
echo "📦 Step 1: Installing web-push-php library..."
composer require minishlink/web-push

# Step 2: Generate VAPID keys
echo "🔑 Step 2: Generating VAPID keys..."
php -r "
require 'vendor/autoload.php';
use Minishlink\WebPush\VAPID;
\$keys = VAPID::createVapidKeys();
echo 'Add these to your .env file:\n\n';
echo 'VAPID_PUBLIC_KEY=' . \$keys['publicKey'] . '\n';
echo 'VAPID_PRIVATE_KEY=' . \$keys['privateKey'] . '\n';
"

# Step 3: Run migration
echo "💾 Step 3: Running migrations..."
php artisan migrate

# Step 4: Clear caches
echo "🗑️  Step 4: Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear

echo "✅ Push notification setup complete!"
echo ""
echo "Next steps:"
echo "1. Add the VAPID keys to your .env file"
echo "2. Create config/webpush.php configuration file"
echo "3. Add routes to routes/web.php"
echo "4. Update OrderCreated event to send push notifications"
echo "5. Add push notification toggle to dashboard"
