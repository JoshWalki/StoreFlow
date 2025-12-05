# Email Notification System - Testing Guide

## Overview

This guide provides comprehensive information about testing the email notification system in StoreFlow. The system sends automated emails to customers when orders are created and when their status changes.

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Email Types](#email-types)
3. [Running Tests](#running-tests)
4. [Manual Testing](#manual-testing)
5. [Production Configuration](#production-configuration)
6. [Troubleshooting](#troubleshooting)

---

## System Architecture

### Components

1. **Mail Classes** (`app/Mail/`)
   - `OrderPlacedMail.php` - Sent when order is created
   - `OrderReadyForPickupMail.php` - Sent when order ready for pickup
   - `OrderShippedMail.php` - Sent when order is shipped
   - `OrderCompletedMail.php` - Sent when order is completed/delivered

2. **Listeners** (`app/Listeners/`)
   - `SendOrderPlacedEmail.php` - Listens for OrderCreated event
   - `SendOrderStatusEmail.php` - Listens for OrderStatusUpdated event

3. **Events** (`app/Events/`)
   - `OrderCreated` - Fired when new order is placed
   - `OrderStatusUpdated` - Fired when order status changes

4. **Email Templates** (`resources/views/emails/`)
   - `layout.blade.php` - Base email template with branding
   - `orders/placed.blade.php` - Order confirmation email
   - `orders/ready-for-pickup.blade.php` - Pickup notification
   - `orders/shipped.blade.php` - Shipping notification
   - `orders/completed.blade.php` - Completion confirmation

### Event Flow

```
Order Created/Updated
    ↓
Event Dispatched (OrderCreated/OrderStatusUpdated)
    ↓
Listener Catches Event (SendOrderPlacedEmail/SendOrderStatusEmail)
    ↓
Mailable Built (OrderPlacedMail/etc.)
    ↓
Email Sent to Customer
```

---

## Email Types

### 1. Order Confirmation (Order Placed)

**Trigger:** When a new order is created
**Mailable:** `OrderPlacedMail`
**Template:** `emails.orders.placed`

**Includes:**
- Order number
- Customer name
- Order items with quantities and prices
- Subtotal, shipping, tax, and total
- Pickup/shipping information
- Store contact details

**Event:** `OrderCreated`

### 2. Ready for Pickup

**Trigger:** Order status changes to `ready_for_pickup`
**Mailable:** `OrderReadyForPickupMail`
**Template:** `emails.orders.ready-for-pickup`

**Includes:**
- Order number
- Pickup location (store address)
- Pickup time (if scheduled)
- Order summary
- Store contact information

**Status Transition:** Any status → `ready_for_pickup`

### 3. Order Shipped

**Trigger:** Order status changes to `shipped`
**Mailable:** `OrderShippedMail`
**Template:** `emails.orders.shipped`

**Includes:**
- Order number
- Tracking code
- Tracking URL (clickable link)
- Shipping address
- Order summary
- Estimated delivery information

**Status Transition:** Any status → `shipped`

### 4. Order Completed

**Trigger:** Order status changes to `completed`, `picked_up`, or `delivered`
**Mailable:** `OrderCompletedMail`
**Template:** `emails.orders.completed`

**Includes:**
- Order number
- Thank you message
- Order summary
- Feedback/review prompt
- Store contact information

**Status Transitions:**
- Any status → `completed`
- Any status → `picked_up`
- Any status → `delivered`

---

## Running Tests

### Prerequisites

```bash
# Ensure database is set up
php artisan migrate:fresh --seed

# Clear cache
php artisan config:clear
php artisan cache:clear
```

### Running All Email Tests

```bash
# Run all email-related tests
php artisan test --filter Email

# Run feature tests only
php artisan test tests/Feature/EmailNotificationTest.php

# Run unit tests only
php artisan test tests/Unit/Mail/
```

### Running Specific Test Suites

```bash
# Test email sending on order creation
php artisan test --filter test_email_is_sent_when_order_is_created

# Test ready for pickup emails
php artisan test --filter test_email_is_sent_when_order_status_changes_to_ready_for_pickup

# Test shipped emails
php artisan test --filter test_email_is_sent_when_order_status_changes_to_shipped

# Test completed emails
php artisan test --filter test_email_is_sent_when_order_status_changes_to_completed
```

### Running Unit Tests for Mailable Classes

```bash
# Test OrderPlacedMail
php artisan test tests/Unit/Mail/OrderPlacedMailTest.php

# Test OrderShippedMail
php artisan test tests/Unit/Mail/OrderShippedMailTest.php

# Test OrderReadyForPickupMail
php artisan test tests/Unit/Mail/OrderReadyForPickupMailTest.php

# Test OrderCompletedMail
php artisan test tests/Unit/Mail/OrderCompletedMailTest.php
```

### Test Coverage

```bash
# Generate test coverage report (requires Xdebug)
php artisan test --coverage --coverage-html=coverage-report

# View coverage for email components
php artisan test --coverage --coverage-filter=app/Mail
php artisan test --coverage --coverage-filter=app/Listeners
```

### Expected Test Results

All tests should pass with the following coverage:

- ✅ Email sent on order creation
- ✅ Email sent for status changes (ready_for_pickup, shipped, completed)
- ✅ Correct customer email used
- ✅ Order information accurate
- ✅ Store branding included
- ✅ Tracking URLs correct (for shipped orders)
- ✅ No emails sent for internal status changes
- ✅ All Mailables render correctly
- ✅ Relationships properly loaded

---

## Manual Testing

### Using Log Driver (Development)

The `.env` file is configured to use the `log` driver for development:

```env
MAIL_MAILER=log
```

**To test emails manually:**

1. Create an order through the API or admin panel

2. Check the log file for email output:
```bash
tail -f storage/logs/laravel.log
```

3. Look for email content in the logs:
```
[timestamp] local.INFO: Email sent to: customer@example.com
Subject: Order Confirmation - Test Store
[Email content will be displayed here]
```

### Using Mailpit (Recommended for Development)

Mailpit is a fake SMTP server with a web UI perfect for testing.

1. **Install Mailpit:**
```bash
# Using Docker
docker run -d -p 1025:1025 -p 8025:8025 axllent/mailpit

# Or download binary from https://github.com/axllent/mailpit
```

2. **Update `.env`:**
```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

3. **Access Mailpit UI:**
   - Open browser to `http://localhost:8025`
   - All sent emails will appear here
   - You can view HTML, inspect headers, and test links

4. **Test the flow:**
```bash
# Create a test order
php artisan tinker
>>> $order = Order::factory()->create(['customer_email' => 'test@example.com']);
>>> event(new App\Events\OrderCreated($order));
>>> exit

# Check Mailpit UI - email should appear immediately
```

### Using Mailtrap (Alternative)

Mailtrap is a professional email testing service with free tier.

1. **Sign up at https://mailtrap.io**

2. **Update `.env` with Mailtrap credentials:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

3. **View emails in Mailtrap inbox**

### Testing Email Content

To preview email templates without sending:

```bash
php artisan tinker
>>> $order = App\Models\Order::with(['store', 'customer', 'items.product'])->first();
>>> $mailable = new App\Mail\OrderPlacedMail($order);
>>> echo $mailable->render();
```

### Testing Event Listeners

```bash
php artisan tinker

# Test OrderCreated event
>>> $order = Order::first();
>>> event(new App\Events\OrderCreated($order));

# Test OrderStatusUpdated event
>>> event(new App\Events\OrderStatusUpdated($order, 'pending', 'ready_for_pickup'));

# Check logs
>>> tail storage/logs/laravel.log
```

---

## Production Configuration

### Environment Variables

For production, configure real SMTP credentials in `.env`:

#### Option 1: Gmail (For Testing/Small Scale)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-specific-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="StoreFlow"
```

**Note:** Use App Password, not your regular password. Enable 2FA and generate app password at https://myaccount.google.com/apppasswords

#### Option 2: SendGrid (Recommended for Production)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="StoreFlow"
```

#### Option 3: Amazon SES (Scalable)

```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your-ses-access-key
AWS_SECRET_ACCESS_KEY=your-ses-secret-key
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="StoreFlow"
```

#### Option 4: Mailgun

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-username
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="StoreFlow"
```

### Queue Configuration

For production, use queues to prevent email sending from blocking requests:

1. **Update `.env`:**
```env
QUEUE_CONNECTION=redis
# or
QUEUE_CONNECTION=database
```

2. **Run queue worker:**
```bash
# For Redis
php artisan queue:work redis --tries=3

# For Database
php artisan queue:work database --tries=3

# Use Supervisor in production to keep worker running
```

3. **Supervisor configuration** (`/etc/supervisor/conf.d/storeflow-worker.conf`):
```ini
[program:storeflow-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/storeflow/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storeflow/storage/logs/worker.log
stopwaitsecs=3600
```

### Monitoring

1. **Failed Jobs:**
```bash
# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

2. **Email Logs:**
```bash
# Monitor email sending in logs
tail -f storage/logs/laravel.log | grep "Order.*email"
```

3. **Queue Monitoring:**
```bash
# Use Laravel Horizon for Redis queues
composer require laravel/horizon
php artisan horizon:install
php artisan horizon
```

---

## Troubleshooting

### Common Issues

#### 1. Emails Not Sending

**Check Configuration:**
```bash
php artisan config:clear
php artisan tinker
>>> config('mail')
```

**Verify SMTP Connection:**
```bash
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
>>> # Check for errors
```

#### 2. Customer Email Missing

**Issue:** Order has no customer_email field populated

**Solution:** Ensure customer_email is set when creating order:
```php
$order = Order::create([
    'customer_email' => $customer->email,
    // ... other fields
]);
```

#### 3. Relationships Not Loading

**Issue:** Store/customer/items data missing in email

**Solution:** The Mailables automatically load relationships in constructor. If issues persist:
```php
$order->load(['store', 'customer', 'items.product']);
event(new OrderCreated($order));
```

#### 4. Email Template Errors

**Debug Template Rendering:**
```bash
php artisan tinker
>>> $order = Order::with(['store', 'customer', 'items.product'])->first();
>>> $mailable = new App\Mail\OrderPlacedMail($order);
>>> $mailable->render(); # Will show blade errors
```

#### 5. Queue Jobs Failing

**Check Failed Jobs:**
```bash
php artisan queue:failed
php artisan queue:retry {job-id}
```

**View Error Details:**
```sql
SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 1;
```

#### 6. Wrong Email Driver

**Verify Driver:**
```bash
php artisan tinker
>>> config('mail.default')
>>> # Should return: 'log' (dev) or 'smtp' (prod)
```

### Debug Mode

Enable detailed email debugging:

```php
// In EventServiceProvider or AppServiceProvider
use Illuminate\Support\Facades\Mail;

Mail::alwaysFrom('noreply@storeflow.local', 'StoreFlow');
Mail::alwaysTo('debug@example.com'); // Catch all emails in dev
```

### Testing Checklist

Before deploying to production:

- [ ] All automated tests pass
- [ ] Manual test of each email type
- [ ] Verify email styling in multiple clients (Gmail, Outlook, Apple Mail)
- [ ] Test with real SMTP server (not log driver)
- [ ] Verify tracking URLs are clickable and correct
- [ ] Check store branding appears correctly
- [ ] Test queue system is working
- [ ] Monitor failed jobs queue
- [ ] Set up email sending alerts/monitoring
- [ ] Verify unsubscribe links (if applicable)
- [ ] Test email deliverability (spam scores)

### Useful Commands

```bash
# Clear all caches
php artisan optimize:clear

# Test email configuration
php artisan tinker
>>> Mail::raw('Test', fn($m) => $m->to('test@example.com'));

# View mail configuration
php artisan tinker
>>> config('mail')

# Monitor logs in real-time
tail -f storage/logs/laravel.log

# Check queue status
php artisan queue:work --once

# List all events and listeners
php artisan event:list
```

---

## API Integration

### Testing via API

Create order through API to trigger emails:

```bash
# Create order (triggers OrderCreated event)
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "store_id": 1,
    "items": [
      {"product_id": 1, "quantity": 2}
    ]
  }'

# Update order status (triggers OrderStatusUpdated event)
curl -X PATCH http://localhost:8000/api/orders/1 \
  -H "Content-Type: application/json" \
  -d '{
    "status": "ready_for_pickup"
  }'
```

---

## Best Practices

1. **Always use queues in production** - Don't block requests waiting for email sending
2. **Log email failures** - Monitor failed_jobs table regularly
3. **Test email rendering** in multiple email clients before deploying
4. **Use meaningful from addresses** - noreply@yourdomain.com
5. **Include unsubscribe links** - Required for commercial emails
6. **Monitor deliverability** - Check spam scores and bounce rates
7. **Set up proper SPF/DKIM/DMARC** - Improves email deliverability
8. **Keep templates responsive** - Test on mobile devices
9. **Rate limit email sending** - Prevent spam accusations
10. **Provide alternative to email** - SMS notifications for critical updates

---

## Support

For issues or questions:

1. Check this guide first
2. Review test files for examples
3. Check Laravel documentation: https://laravel.com/docs/mail
4. Review application logs: `storage/logs/laravel.log`
5. Contact development team

---

**Last Updated:** December 4, 2024
**Version:** 1.0.0
