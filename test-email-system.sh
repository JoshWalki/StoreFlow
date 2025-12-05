#!/bin/bash

# Email Notification System Test Script
# ======================================
# This script validates that all email components are correctly installed

echo "=========================================="
echo "Email Notification System - Validation"
echo "=========================================="
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check counter
CHECKS_PASSED=0
CHECKS_FAILED=0

# Function to check if file exists
check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} Found: $1"
        ((CHECKS_PASSED++))
    else
        echo -e "${RED}✗${NC} Missing: $1"
        ((CHECKS_FAILED++))
    fi
}

echo "1. Checking Mailable Classes..."
echo "-----------------------------------"
check_file "app/Mail/OrderPlacedMail.php"
check_file "app/Mail/OrderReadyForPickupMail.php"
check_file "app/Mail/OrderShippedMail.php"
check_file "app/Mail/OrderCompletedMail.php"
echo ""

echo "2. Checking Email Templates..."
echo "-----------------------------------"
check_file "resources/views/emails/orders/placed.blade.php"
check_file "resources/views/emails/orders/ready-for-pickup.blade.php"
check_file "resources/views/emails/orders/shipped.blade.php"
check_file "resources/views/emails/orders/completed.blade.php"
echo ""

echo "3. Checking Event Listeners..."
echo "-----------------------------------"
check_file "app/Listeners/SendOrderPlacedEmail.php"
check_file "app/Listeners/SendOrderStatusEmail.php"
echo ""

echo "4. Checking Configuration..."
echo "-----------------------------------"
check_file "app/Providers/EventServiceProvider.php"

# Check if listeners are registered in EventServiceProvider
if grep -q "SendOrderPlacedEmail" app/Providers/EventServiceProvider.php; then
    echo -e "${GREEN}✓${NC} SendOrderPlacedEmail registered in EventServiceProvider"
    ((CHECKS_PASSED++))
else
    echo -e "${RED}✗${NC} SendOrderPlacedEmail NOT registered in EventServiceProvider"
    ((CHECKS_FAILED++))
fi

if grep -q "SendOrderStatusEmail" app/Providers/EventServiceProvider.php; then
    echo -e "${GREEN}✓${NC} SendOrderStatusEmail registered in EventServiceProvider"
    ((CHECKS_PASSED++))
else
    echo -e "${RED}✗${NC} SendOrderStatusEmail NOT registered in EventServiceProvider"
    ((CHECKS_FAILED++))
fi
echo ""

echo "5. Checking SMTP Configuration..."
echo "-----------------------------------"
if grep -q "MAIL_MAILER" .env; then
    echo -e "${GREEN}✓${NC} MAIL_MAILER configured in .env"
    ((CHECKS_PASSED++))

    # Show current mail configuration
    echo -e "${YELLOW}Current Configuration:${NC}"
    grep "^MAIL_" .env | head -8
else
    echo -e "${RED}✗${NC} MAIL_MAILER not found in .env"
    ((CHECKS_FAILED++))
fi
echo ""

echo "6. Checking Documentation..."
echo "-----------------------------------"
check_file "EMAIL_NOTIFICATION_IMPLEMENTATION.md"
check_file "EMAIL_QUICK_START.md"
check_file "EMAIL_FILES_CREATED.txt"
echo ""

echo "=========================================="
echo "Validation Summary"
echo "=========================================="
echo -e "Checks Passed: ${GREEN}$CHECKS_PASSED${NC}"
echo -e "Checks Failed: ${RED}$CHECKS_FAILED${NC}"
echo ""

if [ $CHECKS_FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All checks passed! Email system is properly installed.${NC}"
    echo ""
    echo "Next Steps:"
    echo "1. Ensure SMTP is configured in .env"
    echo "2. Clear config cache: php artisan config:clear"
    echo "3. Start queue worker: php artisan queue:work"
    echo "4. Test with: php artisan tinker"
    echo ""
    echo "For testing commands, see EMAIL_QUICK_START.md"
    exit 0
else
    echo -e "${RED}✗ Some checks failed. Please review the missing files above.${NC}"
    exit 1
fi
