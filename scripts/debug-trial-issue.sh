#!/bin/bash

# Debug script to check trial application issue

echo "=== Checking Trial Application Debug ==="
echo ""

# 1. Check if merchant has previous subscription data
echo "1. Checking merchant subscription history:"
echo "   Run this SQL query:"
echo ""
echo "   SELECT"
echo "       id,"
echo "       name,"
echo "       subscription_trial_end,"
echo "       stripe_subscription_id,"
echo "       subscription_status"
echo "   FROM merchants"
echo "   WHERE id = YOUR_MERCHANT_ID;"
echo ""
echo "   Expected for returning user:"
echo "   - subscription_trial_end: NOT NULL"
echo "   - stripe_subscription_id: NOT NULL"
echo ""

# 2. Check recent checkout logs
echo "2. Check recent checkout creation logs:"
echo "   tail -100 storage/logs/laravel.log | grep -A 10 'Creating checkout session'"
echo ""
echo "   Look for:"
echo "   - 'has_used_trial_before': true/false"
echo "   - 'subscription_trial_end': value or null"
echo "   - 'stripe_subscription_id': value or null"
echo ""

# 3. Check StripeService logs
echo "3. Check StripeService session creation:"
echo "   tail -100 storage/logs/laravel.log | grep -A 5 'Checkout session created'"
echo ""
echo "   Look for:"
echo "   - 'has_trial': true/false"
echo "   - 'trial_days': 0 or 14"
echo "   - 'subscription_metadata': check for trial_already_used"
echo ""

# 4. Check webhook logs
echo "4. After subscription is created, check webhook:"
echo "   tail -100 storage/logs/laravel.log | grep -A 10 'Syncing subscription from Stripe'"
echo ""
echo "   Look for:"
echo "   - 'has_current_period_start': true"
echo "   - 'current_period_start_value': timestamp"
echo ""

echo "=== Copy and run these commands on your server ==="
