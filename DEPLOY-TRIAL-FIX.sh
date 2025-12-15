#!/bin/bash
# Deployment script for trial period fix

echo "🚀 Deploying trial period fix..."

# Navigate to project
cd /mnt/c/xampp/htdocs/StoreFlow

# Pull latest changes (if using git)
# git pull origin main

# Clear Laravel caches
echo "Clearing caches..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Restart services
echo "Restarting services..."
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

echo "✅ Deployment complete!"
echo ""
echo "📊 Testing Instructions:"
echo "1. Watch logs: tail -f storage/logs/laravel.log | grep -E 'Checkout session created|trial_end|current_period'"
echo "2. Test with returning user (should show trial_end: 'now')"
echo "3. Check subscription dates in database"
echo ""
echo "Expected for returning users:"
echo '- Log: "trial_end": "now"'
echo "- Log: current_period_start = today's timestamp"
echo "- Database: subscription_current_period_start ≈ NOW()"
