# Cart & Checkout Testing Checklist

This document outlines the complete cart and checkout functionality that has been implemented and provides a testing guide.

## Implementation Summary

### Features Implemented

1. **Shopping Cart System**
   - Add to cart functionality
   - Buy now quick checkout
   - Cart item management (add, remove, update quantity)
   - LocalStorage persistence
   - Product customization support
   - Real-time cart count badge

2. **Checkout Flow**
   - Contact information collection
   - Fulfillment type selection (pickup/shipping)
   - Dynamic shipping address form
   - Real-time shipping quote calculation
   - Shipping method selection
   - Order summary with totals
   - Simulated payment processing

3. **Real-time WebSocket Notifications**
   - Dashboard toast notifications for new orders
   - Auto-refresh active orders list
   - Optional notification sound support

## File Structure

### New Files Created

**Frontend Components:**
- `/resources/js/Composables/useCart.js` - Cart state management
- `/resources/js/Components/Storefront/CartButton.vue` - Cart icon with badge
- `/resources/js/Components/Storefront/CartDrawer.vue` - Sliding cart sidebar
- `/resources/js/Pages/Storefront/Checkout.vue` - Checkout page
- `/resources/js/Composables/useNotifications.js` - Notification state management
- `/resources/js/Components/Notifications/ToastContainer.vue` - Toast notifications
- `/resources/js/echo.js` - Laravel Echo configuration

**Documentation:**
- `/WEBSOCKET_SETUP.md` - WebSocket configuration guide
- `/TESTING_CART_CHECKOUT.md` - This file

### Modified Files

**Backend:**
- `/routes/web.php` - Added checkout routes
- `/app/Http/Controllers/Storefront/StorefrontController.php` - Added checkout() and processCheckout() methods
- `/app/Events/OrderCreated.php` - Fixed broadcast channel and data structure

**Frontend:**
- `/resources/js/Pages/Storefront/ProductDetail.vue` - Added cart buttons and functionality
- `/resources/js/Layouts/DashboardLayout.vue` - Added WebSocket listener and notifications
- `/resources/js/app.js` - Imported echo.js for WebSocket support

## Testing Checklist

### 1. Cart Functionality Tests

#### Test 1.1: Add Product to Cart
- [ ] Navigate to storefront product page
- [ ] Click "Add to Cart" button
- [ ] Verify cart drawer slides open
- [ ] Verify product appears in cart with correct name, price, and quantity
- [ ] Verify cart badge shows "1"

#### Test 1.2: Cart Persistence
- [ ] Add products to cart
- [ ] Refresh the page (F5)
- [ ] Verify cart items are still present
- [ ] Verify cart count badge shows correct number

#### Test 1.3: Update Quantities
- [ ] Click the "+" button on a cart item
- [ ] Verify quantity increases
- [ ] Verify subtotal updates correctly
- [ ] Click the "-" button
- [ ] Verify quantity decreases
- [ ] Verify subtotal updates correctly

#### Test 1.4: Remove Items
- [ ] Click the "Remove" button on a cart item
- [ ] Verify item is removed from cart
- [ ] Verify subtotal updates
- [ ] Verify cart badge count decreases

#### Test 1.5: Buy Now Functionality
- [ ] Navigate to product page
- [ ] Click "Buy Now" button
- [ ] Verify product is added to cart
- [ ] Verify immediate redirect to checkout page

#### Test 1.6: Empty Cart
- [ ] Remove all items from cart
- [ ] Verify "Your cart is empty" message appears
- [ ] Verify cart badge shows "0"

### 2. Checkout Flow Tests

#### Test 2.1: Checkout Page Access
- [ ] Add products to cart
- [ ] Click "Checkout" button in cart drawer
- [ ] Verify redirect to `/store/{id}/checkout`
- [ ] Verify order summary shows correct items and totals

#### Test 2.2: Contact Information Validation
- [ ] Try to submit with empty first name - should show validation error
- [ ] Try to submit with empty last name - should show validation error
- [ ] Try to submit with invalid email - should show validation error
- [ ] Fill in valid contact information
- [ ] Verify no validation errors

#### Test 2.3: Pickup Fulfillment
- [ ] Select "Pick up" fulfillment option
- [ ] Verify shipping address form is hidden
- [ ] Verify no shipping cost is added
- [ ] Verify total = subtotal only

#### Test 2.4: Shipping Fulfillment
- [ ] Select "Shipping" fulfillment option
- [ ] Verify shipping address form appears
- [ ] Fill in shipping address (use AU country, VIC state, valid postcode)
- [ ] Verify shipping quote is calculated automatically
- [ ] Verify shipping methods appear if available
- [ ] Select a shipping method
- [ ] Verify shipping cost is added to total

#### Test 2.5: Order Submission
- [ ] Fill in all required fields
- [ ] Click "Place Order" button
- [ ] Verify redirect to order tracking page
- [ ] Verify success message with order number
- [ ] Verify cart is cleared

### 3. Backend Order Creation Tests

#### Test 3.1: Order Record
- [ ] Check database `orders` table
- [ ] Verify new order record exists
- [ ] Verify `public_id` starts with "ORD-"
- [ ] Verify `payment_status` = "paid"
- [ ] Verify `payment_reference` starts with "SIM-"
- [ ] Verify `customer_name` is populated
- [ ] Verify `total_cents` is correct

#### Test 3.2: Order Items
- [ ] Check database `order_items` table
- [ ] Verify order items exist for the order
- [ ] Verify quantities are correct
- [ ] Verify prices are frozen (copied from product at order time)

#### Test 3.3: Customer Record
- [ ] Check database `customers` table
- [ ] Verify customer record was created or updated
- [ ] Verify email, name, and mobile are correct

### 4. WebSocket Notification Tests

**Note:** These tests require Pusher or Laravel WebSockets to be configured. See `WEBSOCKET_SETUP.md` for setup instructions.

#### Test 4.1: Setup Verification
- [ ] Check `.env` file has PUSHER credentials configured
- [ ] Run `npm run dev` to rebuild assets
- [ ] Open dashboard in browser
- [ ] Open browser console (F12)
- [ ] Verify message: "Setting up websocket listener for store: X"
- [ ] No JavaScript errors should appear

#### Test 4.2: Order Notification
- [ ] Open dashboard in one browser window
- [ ] Open storefront in another browser window/tab
- [ ] Place an order from storefront
- [ ] Check dashboard window
- [ ] Verify toast notification appears: "New Order! Order ORD-XXXX placed for Customer Name"
- [ ] Verify notification auto-dismisses after 10 seconds
- [ ] If on active orders page, verify list refreshes automatically

#### Test 4.3: Multiple Notifications
- [ ] Keep dashboard open
- [ ] Place 3 orders in quick succession
- [ ] Verify all 3 toast notifications appear
- [ ] Verify they stack vertically
- [ ] Verify they dismiss independently

#### Test 4.4: Close Button
- [ ] Trigger a notification
- [ ] Click the X button on the toast
- [ ] Verify toast immediately disappears

### 5. Edge Cases & Error Handling

#### Test 5.1: Empty Cart Checkout
- [ ] Clear cart completely
- [ ] Try to navigate to checkout URL directly
- [ ] Verify appropriate handling (should redirect or show error)

#### Test 5.2: Invalid Shipping Address
- [ ] Select shipping fulfillment
- [ ] Enter postcode not covered by any shipping zone
- [ ] Verify "No shipping options available" message
- [ ] Verify cannot submit order

#### Test 5.3: Product Images
- [ ] View cart with products that have images
- [ ] Verify images load correctly (no 404 errors)
- [ ] Verify images don't have double /storage/ prefix in URL

#### Test 5.4: Large Quantities
- [ ] Add product with quantity 999
- [ ] Verify subtotal calculates correctly
- [ ] Verify no JavaScript errors

#### Test 5.5: Special Characters
- [ ] Enter customer name with special characters (e.g., "O'Brien")
- [ ] Submit order
- [ ] Verify order processes successfully
- [ ] Verify name displays correctly on dashboard notification

## Expected Behavior

### Cart Calculations
- **Subtotal:** Sum of (product price × quantity) for all items
- **Shipping:** Cost from selected shipping method (if shipping fulfillment)
- **Total:** Subtotal + Shipping
- **Currency:** All amounts displayed in AUD format (e.g., $123.45)

### Order Status
- **Payment Status:** Always "paid" (simulated payment)
- **Payment Method:** "simulated"
- **Payment Reference:** Random string starting with "SIM-"

### WebSocket Channels
- **Channel Name:** `store.{storeId}.orders`
- **Event Name:** `order.created`
- **Event Payload:**
  ```javascript
  {
    order: {
      id: 123,
      public_id: "ORD-XXXXXXXXXXXX",
      customer_name: "John Doe",
      total_cents: 5000,
      status: "pending",
      fulfilment_type: "shipping",
      // ... other fields
    }
  }
  ```

## Common Issues & Solutions

### Issue: Cart badge not updating
**Solution:** Check browser console for JavaScript errors. Ensure useCart composable is imported correctly.

### Issue: Checkout page not found
**Solution:** Run `npm run dev` to rebuild assets. Check that Checkout.vue exists in `/resources/js/Pages/Storefront/`

### Issue: Images not loading (404 errors)
**Solution:** Verify images are in `/storage/app/public/products/`. Run `php artisan storage:link` if needed.

### Issue: No shipping options available
**Solution:**
1. Check shipping zones in dashboard
2. Verify zone covers the test address (country, state, postcode)
3. Check shipping methods are enabled and assigned to zone
4. Review `/storage/logs/laravel.log` for errors

### Issue: WebSocket not configured message
**Solution:** Follow `WEBSOCKET_SETUP.md` to configure Pusher or Laravel WebSockets.

### Issue: Order notification not appearing
**Solution:**
1. Verify BROADCAST_DRIVER=pusher in `.env`
2. Check Pusher credentials are correct
3. Run `php artisan config:clear`
4. Rebuild assets: `npm run dev`
5. Check browser console for Echo connection errors

## Performance Considerations

### LocalStorage Limits
- Browser localStorage typically has 5-10MB limit
- Cart data is small, unlikely to hit limits
- Consider clearing old cart data if implementing user accounts later

### WebSocket Connections
- Each browser tab maintains its own Echo connection
- Pusher free tier: 100 concurrent connections
- Consider upgrading Pusher plan for production use

## Security Notes

### Current Implementation
- **No authentication required** for storefront checkout
- **Email used as customer identifier**
- **Simulated payment** - no real payment processing
- **No rate limiting** on checkout endpoint

### Recommendations for Production
1. Add rate limiting to checkout endpoint
2. Implement CSRF protection (already in Laravel by default)
3. Add reCAPTCHA to prevent bot orders
4. Implement real payment gateway integration
5. Add email verification for customer accounts
6. Enable WebSocket channel authorization

## Next Steps

### Immediate
- [ ] Run through complete testing checklist
- [ ] Configure Pusher for WebSocket testing
- [ ] Test on different browsers (Chrome, Firefox, Safari, Edge)

### Short Term
- [ ] Add product customization UI (color, size, etc.)
- [ ] Implement order status tracking page improvements
- [ ] Add email notifications for orders
- [ ] Implement inventory management

### Long Term
- [ ] Integrate real payment gateway (Stripe, PayPal, etc.)
- [ ] Add customer account system
- [ ] Implement abandoned cart recovery
- [ ] Add analytics and reporting
- [ ] Mobile app for storefront

## Support

For questions or issues:
1. Check Laravel logs: `/storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Review this testing checklist
4. Consult `WEBSOCKET_SETUP.md` for WebSocket issues
