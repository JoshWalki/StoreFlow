# Email Notification System - Quick Start Guide

## What Was Implemented

A complete email notification system that automatically sends emails to customers at key order lifecycle events.

## Files Created

### Mailable Classes (4 files)
- `/app/Mail/OrderPlacedMail.php`
- `/app/Mail/OrderReadyForPickupMail.php`
- `/app/Mail/OrderShippedMail.php`
- `/app/Mail/OrderCompletedMail.php`

### Email Templates (4 files)
- `/resources/views/emails/orders/placed.blade.php`
- `/resources/views/emails/orders/ready-for-pickup.blade.php`
- `/resources/views/emails/orders/shipped.blade.php`
- `/resources/views/emails/orders/completed.blade.php`

### Event Listeners (2 files)
- `/app/Listeners/SendOrderPlacedEmail.php`
- `/app/Listeners/SendOrderStatusEmail.php`

### Updated Files (2 files)
- `/app/Providers/EventServiceProvider.php` (added listener registrations)
- `/.env.example` (added SMTP configuration examples)

## How It Works

### Automatic Email Triggers

1. **Order Placed** → Customer receives confirmation email
   - Triggered when: `OrderCreated` event fires
   - Email: Order details, items, totals, fulfilment info

2. **Order Ready for Pickup** → Customer receives pickup notification
   - Triggered when: Order status changes to `ready_for_pickup`
   - Email: Pickup location, time, what to bring

3. **Order Shipped** → Customer receives tracking information
   - Triggered when: Order status changes to `shipped`
   - Email: Tracking number, link, delivery address

4. **Order Completed** → Customer receives thank you email
   - Triggered when: Order status changes to `completed`, `picked_up`, or `delivered`
   - Email: Order summary, feedback request

## Quick Setup

### For Local Development

1. **Already configured!** The system uses Mailpit by default:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=mailpit
   MAIL_PORT=1025
   ```

2. **Access Mailpit UI:** http://localhost:8025

3. **Clear config cache:**
   ```bash
   php artisan config:clear
   ```

### For Production

1. **Choose your email provider** (examples in `.env.example`):
   - Gmail
   - SendGrid
   - Mailgun
   - AWS SES
   - Mailtrap

2. **Update `.env` with SMTP credentials:**
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.your-provider.com
   MAIL_PORT=587
   MAIL_USERNAME=your-username
   MAIL_PASSWORD=your-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@yourdomain.com
   MAIL_FROM_NAME="Your Store Name"
   ```

3. **Set up queue worker** (recommended):
   ```env
   QUEUE_CONNECTION=redis
   ```

   Then run:
   ```bash
   php artisan queue:work
   ```

4. **Clear config cache:**
   ```bash
   php artisan config:clear
   ```

## Testing

### Quick Test in Tinker

```bash
php artisan tinker
```

```php
// Test order placed email
$order = App\Models\Order::with(['store', 'items.product', 'customer'])->first();
event(new App\Events\OrderCreated($order));

// Test ready for pickup email
$order = App\Models\Order::with(['store', 'items.product', 'customer'])->first();
event(new App\Events\OrderStatusUpdated($order, 'pending', 'ready_for_pickup'));

// Test shipped email
event(new App\Events\OrderStatusUpdated($order, 'ready', 'shipped'));

// Test completed email
event(new App\Events\OrderStatusUpdated($order, 'shipped', 'completed'));
```

### Preview Email in Browser

Add this test route to `routes/web.php`:

```php
Route::get('/preview-email/{orderId}', function ($orderId) {
    $order = App\Models\Order::with(['store', 'items.product', 'customer'])
        ->findOrFail($orderId);
    return new App\Mail\OrderPlacedMail($order);
})->middleware('auth');
```

Visit: `http://your-domain/preview-email/1`

## Monitoring

### Check Logs

```bash
# Watch email logs in real-time
tail -f storage/logs/laravel.log | grep -i "email"

# Check for errors
tail -f storage/logs/laravel.log | grep -i "error"
```

### Check Queue Jobs

```bash
# Failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Retry specific job
php artisan queue:retry {job-id}
```

## Email Features

### What's Included in Each Email

#### Order Placed Email
- Order number and date
- Customer details
- Complete item list with prices
- Subtotal, discount, tax, shipping
- Total amount
- Payment status
- Fulfilment details (pickup time OR shipping address)
- Store contact information

#### Ready for Pickup Email
- Order number
- Pickup time
- Store address and location
- Pickup instructions
- What to bring checklist
- Order items summary
- Contact information for reschedule

#### Order Shipped Email
- Tracking number
- Clickable tracking link button
- Shipping method
- Delivery address
- Order items summary
- Delivery expectations
- Support contact details

#### Order Completed Email
- Thank you message
- Order summary with dates
- Items purchased
- Total amount
- Feedback request
- Support contact information

### Email Design Features

- Professional Laravel markdown styling
- Mobile-responsive layout
- Store branding (name, logo, contact info)
- Formatted tables for order items
- Call-to-action buttons (tracking links)
- Conditional sections based on order type

## Troubleshooting

### Emails Not Sending

1. **Check SMTP configuration:**
   ```bash
   php artisan config:cache
   php artisan config:clear
   ```

2. **Test SMTP connection:**
   ```bash
   php artisan tinker
   Mail::raw('Test', function($msg) {
       $msg->to('test@example.com')->subject('Test');
   });
   ```

3. **Check queue is running:**
   ```bash
   php artisan queue:work --once
   ```

4. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Missing Customer Email

The system automatically checks for customer email before sending. If missing:
- A warning is logged
- No email is sent (no error thrown)
- Order processing continues normally

Check logs:
```bash
grep "No customer email" storage/logs/laravel.log
```

### Queue Jobs Failing

1. **View failed jobs:**
   ```bash
   php artisan queue:failed
   ```

2. **Retry failed jobs:**
   ```bash
   php artisan queue:retry all
   ```

3. **Clear failed jobs:**
   ```bash
   php artisan queue:flush
   ```

## Integration

### The System Uses Existing:

- `OrderCreated` event (no changes needed)
- `OrderStatusUpdated` event (no changes needed)
- `Order` model with relationships (no changes needed)
- `Store` model (no changes needed)
- `Customer` model (no changes needed)

### No Changes Required To:

- Controllers
- Routes
- Migrations
- Existing events
- Existing models
- Database structure

### It Just Works!

The email system integrates seamlessly with your existing order flow. Every time an order is created or its status changes, the appropriate email is automatically sent to the customer.

## Customization

### Change Email Subject

Edit the `envelope()` method in Mailable classes:

```php
// In /app/Mail/OrderPlacedMail.php
public function envelope(): Envelope
{
    return new Envelope(
        subject: 'Your Custom Subject - ' . $this->order->store->name,
    );
}
```

### Change Email Content

Edit the Blade templates in `/resources/views/emails/orders/`

### Add New Email Type

1. Create new Mailable: `php artisan make:mail OrderCancelledMail`
2. Create new template: `/resources/views/emails/orders/cancelled.blade.php`
3. Update `SendOrderStatusEmail.php` to include new status
4. Update `getMailableForStatus()` method

## Important Notes

1. **All emails are queued** - They run asynchronously to avoid slowing down requests
2. **Customer email validation** - System checks for valid email before sending
3. **Error resilience** - Failed emails automatically retry via queue
4. **Comprehensive logging** - All email activity is logged for debugging
5. **Store branding** - Each email includes the store's information

## Security

- Only sends to validated customer email addresses
- No sensitive payment information (card numbers, etc.)
- All operations logged for audit trail
- Queue jobs run in isolated process
- Errors logged but not exposed to users

## Performance

- Asynchronous email sending (non-blocking)
- Eager loading to prevent N+1 queries
- Conditional sending (only for relevant status changes)
- Automatic retry logic for failures

## Support

For detailed information, see: `EMAIL_NOTIFICATION_IMPLEMENTATION.md`

## Verification Checklist

- [ ] SMTP configuration in `.env` is correct
- [ ] Queue worker is running (production)
- [ ] Test email sends successfully
- [ ] Mailpit/Mailtrap shows received emails (development)
- [ ] Logs show successful email dispatch
- [ ] No errors in `storage/logs/laravel.log`

---

**System Status:** Fully Implemented ✅

All components are created and configured. The email notification system is ready to use immediately with no additional setup required for local development.
