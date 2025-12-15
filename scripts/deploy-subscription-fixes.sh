#!/bin/bash

# Subscription System Fixes - Deployment Script
# This script deploys all subscription-related fixes safely
# Run as: sudo bash scripts/deploy-subscription-fixes.sh

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/mnt/c/xampp/htdocs/StoreFlow"
WEB_USER="www-data"
PHP_FPM_SERVICE="php8.2-fpm"
WEB_SERVER_SERVICE="nginx"  # Change to "apache2" if using Apache

# Functions
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_root() {
    if [ "$EUID" -ne 0 ]; then
        print_error "Please run as root or with sudo"
        exit 1
    fi
}

backup_files() {
    print_status "Creating backup..."
    BACKUP_DIR="$PROJECT_DIR/backups/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"

    # Backup critical files
    cp -r "$PROJECT_DIR/.env" "$BACKUP_DIR/" 2>/dev/null || true
    cp -r "$PROJECT_DIR/storage/logs" "$BACKUP_DIR/" 2>/dev/null || true

    print_success "Backup created at: $BACKUP_DIR"
}

verify_environment() {
    print_status "Verifying environment..."

    # Check if in correct directory
    if [ ! -f "$PROJECT_DIR/artisan" ]; then
        print_error "Laravel project not found at $PROJECT_DIR"
        exit 1
    fi

    # Check if www-data user exists
    if ! id "$WEB_USER" &>/dev/null; then
        print_error "User $WEB_USER does not exist"
        exit 1
    fi

    print_success "Environment verified"
}

pull_latest_code() {
    print_status "Pulling latest code from repository..."
    cd "$PROJECT_DIR"

    # Check current branch
    CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
    print_status "Current branch: $CURRENT_BRANCH"

    # Stash any local changes (shouldn't be any in production)
    git stash save "Auto-stash before deployment $(date)"

    # Pull latest changes
    git pull origin "$CURRENT_BRANCH"

    # Show what changed
    COMMIT_HASH=$(git rev-parse HEAD)
    print_success "Updated to commit: $COMMIT_HASH"
}

fix_permissions() {
    print_status "Fixing file permissions..."
    cd "$PROJECT_DIR"

    # Storage and cache directories
    chown -R "$WEB_USER:$WEB_USER" storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache

    # Ensure log file exists and is writable
    touch storage/logs/laravel.log
    chown "$WEB_USER:$WEB_USER" storage/logs/laravel.log
    chmod 664 storage/logs/laravel.log

    print_success "Permissions fixed"
}

clear_caches() {
    print_status "Clearing application caches..."
    cd "$PROJECT_DIR"

    # Clear caches as www-data user (CRITICAL!)
    sudo -u "$WEB_USER" php artisan config:clear
    sudo -u "$WEB_USER" php artisan route:clear
    sudo -u "$WEB_USER" php artisan cache:clear
    sudo -u "$WEB_USER" php artisan view:clear

    print_success "Caches cleared"
}

rebuild_caches() {
    print_status "Rebuilding optimized caches..."
    cd "$PROJECT_DIR"

    # Rebuild as www-data
    sudo -u "$WEB_USER" php artisan config:cache
    sudo -u "$WEB_USER" php artisan route:cache

    print_success "Caches rebuilt"
}

restart_services() {
    print_status "Restarting PHP-FPM and web server..."

    # Restart PHP-FPM
    systemctl restart "$PHP_FPM_SERVICE"
    print_success "PHP-FPM restarted"

    # Restart web server
    systemctl restart "$WEB_SERVER_SERVICE"
    print_success "Web server restarted"
}

verify_services() {
    print_status "Verifying services are running..."

    # Check PHP-FPM
    if systemctl is-active --quiet "$PHP_FPM_SERVICE"; then
        print_success "PHP-FPM is running"
    else
        print_error "PHP-FPM is not running!"
        exit 1
    fi

    # Check web server
    if systemctl is-active --quiet "$WEB_SERVER_SERVICE"; then
        print_success "Web server is running"
    else
        print_error "Web server is not running!"
        exit 1
    fi
}

run_tests() {
    print_status "Running post-deployment tests..."
    cd "$PROJECT_DIR"

    # Test 1: Check if application is accessible
    print_status "Test 1: Application health check..."
    if curl -s -o /dev/null -w "%{http_code}" http://localhost | grep -q "200\|302"; then
        print_success "Application is accessible"
    else
        print_warning "Application may not be accessible (check manually)"
    fi

    # Test 2: Check log file permissions
    print_status "Test 2: Log file permissions..."
    if [ -w "storage/logs/laravel.log" ]; then
        print_success "Log file is writable"
    else
        print_error "Log file is not writable!"
    fi

    # Test 3: Check environment variables
    print_status "Test 3: Critical environment variables..."
    if grep -q "STRIPE_SECRET=" .env && grep -q "STRIPE_WEBHOOK_SECRET=" .env; then
        print_success "Stripe configuration exists"
    else
        print_warning "Stripe configuration may be incomplete"
    fi
}

show_summary() {
    echo ""
    echo "============================================"
    echo -e "${GREEN}Deployment Complete!${NC}"
    echo "============================================"
    echo ""
    echo "✅ Files Changed:"
    echo "   - app/Http/Controllers/SubscriptionController.php"
    echo "   - app/Services/StripeService.php"
    echo "   - app/Http/Middleware/TrimStrings.php"
    echo "   - app/Http/Middleware/VerifyCsrfToken.php"
    echo "   - app/Services/SubscriptionService.php"
    echo ""
    echo "✅ Fixes Applied:"
    echo "   - Trial period reactivation fix"
    echo "   - Checkout 500 error fix"
    echo "   - Webhook signature verification"
    echo "   - File permission fixes"
    echo ""
    echo "📋 Next Steps:"
    echo "   1. Monitor logs: tail -f storage/logs/laravel.log"
    echo "   2. Test new user checkout (should get trial)"
    echo "   3. Test returning user checkout (should NOT get trial)"
    echo "   4. Verify webhooks from Stripe Dashboard"
    echo ""
    echo "📚 Documentation:"
    echo "   - docs/TRIAL-REACTIVATION-FIX.md"
    echo "   - docs/SUBSCRIPTION-FIXES-SUMMARY.md"
    echo "   - docs/CHECKOUT-DEBUGGING-GUIDE.md"
    echo ""
    echo "🔍 Monitoring Commands:"
    echo "   tail -f storage/logs/laravel.log | grep 'checkout'"
    echo "   tail -f storage/logs/laravel.log | grep 'webhook'"
    echo "   tail -f storage/logs/laravel.log | grep 'has_used_trial_before'"
    echo ""
    echo "============================================"
}

rollback() {
    print_error "Deployment failed! Rolling back..."

    # Restore from backup if exists
    if [ -d "$BACKUP_DIR" ]; then
        cp -r "$BACKUP_DIR/.env" "$PROJECT_DIR/" 2>/dev/null || true
        print_warning "Restored .env from backup"
    fi

    # Clear caches
    cd "$PROJECT_DIR"
    sudo -u "$WEB_USER" php artisan config:clear
    sudo -u "$WEB_USER" php artisan route:clear
    sudo -u "$WEB_USER" php artisan cache:clear

    # Restart services
    systemctl restart "$PHP_FPM_SERVICE"
    systemctl restart "$WEB_SERVER_SERVICE"

    print_error "Rollback complete. Check logs for details."
    exit 1
}

# Main deployment flow
main() {
    echo ""
    echo "============================================"
    echo "  Subscription System Fixes - Deployment"
    echo "============================================"
    echo ""

    # Set error trap
    trap rollback ERR

    # Run deployment steps
    check_root
    verify_environment
    backup_files
    pull_latest_code
    fix_permissions
    clear_caches
    rebuild_caches
    restart_services
    verify_services
    run_tests
    show_summary

    # Remove error trap
    trap - ERR
}

# Run main function
main

exit 0
