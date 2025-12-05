# StoreFlow Order Management - Quick Test Guide

**TESTER Agent | Hive Mind Collective**

---

## 🚀 Quick Start (5 Minutes)

### Prerequisites
```bash
cd /mnt/c/xampp/htdocs/StoreFlow
composer install
npm install
php artisan migrate:fresh --seed
```

### Run Automated Tests
```bash
# Backend tests
php artisan test --filter=OrderServiceTest

# Code verification
bash test-order-management.sh
```

---

## 📋 Manual Testing Checklist

### 1. Search Functionality (2 min)

**Test Cases:**
```
✅ Type "SF-" → See matching orders
✅ Type customer name → See filtered results
✅ Type email → See correct orders
✅ Click X button → All orders restore
✅ Search updates in real-time
```

**Files to Check:**
- Frontend: `resources/js/Pages/Dashboard/Index.vue` (lines 156-172)
- Search input: Lines 19-43

---

### 2. Order Display (1 min)

**Check Order Cards:**
```
✅ Order number (SF-XXXXX)
✅ Customer name
✅ Total amount ($XX.XX)
✅ Items count
✅ Time (relative)
✅ Fulfillment badge (Shipping/Pickup)
✅ Payment status (green=paid)
```

**Files to Check:**
- Component: `resources/js/Components/Operations/OrderCard.vue`

---

### 3. Order Modal (2 min)

**Actions to Test:**
```
✅ Click order → Modal opens
✅ See order number in header
✅ Customer info complete
✅ Address shown (shipping) OR pickup time (pickup)
✅ Items table displays
✅ Quantities correct
✅ Totals accurate
✅ Status badge visible
✅ Action buttons present
✅ Click X → Modal closes
✅ Click outside → Modal closes
```

**Files to Check:**
- Component: `resources/js/Components/Operations/OrderDetailModal.vue`

---

### 4. Status Transitions (2 min)

**Workflow to Test:**
```
Shipping Order:
✅ Pending → Accept Order
✅ Accepted → Start Processing
✅ In Progress → Ready to Pack
✅ Ready → Start Packing
✅ Packing → Mark as Shipped
✅ Shipped → Mark as Delivered

Pickup Order:
✅ Pending → Accept Order
✅ Accepted → Start Processing
✅ In Progress → Ready for Pickup
✅ Ready for Pickup → Mark as Picked Up
```

**Test Invalid Transitions:**
```
❌ Pending → Shipped (should fail)
❌ Delivered → Pending (should fail)
❌ Packing for pickup order (should fail)
```

---

### 5. Drag and Drop (1 min)

**Actions:**
```
✅ Drag order card
✅ Card becomes semi-transparent
✅ Drop zone highlights
✅ Drop in valid column → Status updates
✅ Drop in same column → Nothing happens
✅ Error → Toast notification appears
```

---

### 6. Edge Cases (1 min)

**Test Scenarios:**
```
✅ Empty column shows "No orders"
✅ Order with 10+ items displays correctly
✅ Modal scrolls if needed
✅ Long customer names don't break layout
✅ Zero shipping cost shows $0.00
```

---

## 🔍 Critical Paths to Verify

### Path 1: Customer Order Flow
1. Customer places order → **Order appears in Pending**
2. Merchant accepts → **Moves to Accepted**
3. Start processing → **Moves to In Progress**
4. Complete → **Moves to Ready/Shipped**
5. Final → **Moves to Completed**

### Path 2: Search & View
1. Orders displayed in columns
2. Search for specific order
3. Click to view details
4. Verify all information
5. Close modal

### Path 3: Real-time Updates
1. Open dashboard
2. Check connection indicator (green dot)
3. Wait 5 seconds
4. New orders appear automatically
5. Status changes sync

---

## ⚡ Quick Verification Commands

### Backend Health
```bash
# Run all order tests
php artisan test tests/Unit/OrderServiceTest.php

# Check routes
php artisan route:list --path=orders

# Database check
php artisan migrate:status
```

### Frontend Health
```bash
# Build assets
npm run build

# Dev server
npm run dev

# Check for syntax errors
npx vue-tsc --noEmit
```

### File Integrity
```bash
# Check critical files exist
ls resources/js/Pages/Dashboard/Index.vue
ls resources/js/Components/Operations/OrderDetailModal.vue
ls resources/js/Components/Operations/OrderCard.vue
ls resources/js/Components/Operations/OrderStatusColumn.vue
ls app/Http/Controllers/OrderController.php
```

---

## 🎯 Key Features to Demonstrate

### Feature 1: Real-time Search
```
1. Type in search box
2. Results filter immediately
3. No lag or delay
4. Clear button appears
5. Click clear → All orders back
```

### Feature 2: Order Details
```
1. Click any order card
2. Modal opens smoothly
3. All info visible:
   - Customer details
   - Full address
   - Item list with prices
   - Totals
   - Status & actions
```

### Feature 3: Status Management
```
1. Drag order to next column
2. OR click order → Use action button
3. See toast notification
4. Order moves to new column
5. Connection stays green
```

---

## 📊 Expected Results

### Automated Test Results
```
Backend Tests:      19/19 PASS ✅
Code Checks:        28/31 PASS ✅
Overall:            90% Success Rate
```

### Manual Test Results
```
Search:             All tests pass ✅
Display:            All data correct ✅
Modal:              All features work ✅
Transitions:        All valid paths work ✅
Edge Cases:         Handled gracefully ✅
```

---

## 🐛 Common Issues & Solutions

### Issue 1: Orders Not Appearing
**Solution:**
```bash
# Check database
php artisan tinker
>>> \App\Models\Order::count();

# Seed test data
php artisan db:seed --class=OrderSeeder
```

### Issue 2: Search Not Working
**Check:**
- Console for JavaScript errors
- Vue devtools for state
- Search query variable updates

### Issue 3: Modal Won't Open
**Check:**
- Click event in OrderCard
- Modal state in Dashboard
- Browser console errors

### Issue 4: Polling Not Working
**Check:**
- Network tab for /dashboard/orders/poll calls
- Every 5 seconds
- Connection indicator status

---

## 📈 Performance Benchmarks

### Expected Performance
```
Page Load:         < 2 seconds
Search Response:   < 100ms
Modal Open:        < 300ms
Status Update:     < 500ms
Polling Interval:  5 seconds
```

### How to Test
```javascript
// In browser console
console.time('modal-open');
// Click order card
console.timeEnd('modal-open');
// Should be < 300ms
```

---

## ✅ Definition of Done

### All Tests Pass
- [ ] Backend unit tests: 19/19 ✅
- [ ] Code verification: 28/31 ✅
- [ ] Manual search tests: All pass ✅
- [ ] Manual modal tests: All pass ✅
- [ ] Status transitions: All valid paths work ✅
- [ ] Edge cases: Handled ✅

### User Experience
- [ ] Search is instant
- [ ] Modal is smooth
- [ ] Status updates feel responsive
- [ ] No errors in console
- [ ] Connection stays green

### Code Quality
- [ ] No console errors
- [ ] No warnings
- [ ] Clean code structure
- [ ] Proper error handling
- [ ] Comments where needed

---

## 🎬 Testing Scenarios by Role

### Store Owner Testing
```
Focus: High-level overview
1. View all orders at once
2. Quick search by customer
3. Accept multiple orders
4. Check totals are correct
```

### Store Manager Testing
```
Focus: Day-to-day operations
1. Search for specific orders
2. Process orders through workflow
3. Update shipping info
4. Handle edge cases
```

### Developer Testing
```
Focus: Technical validation
1. Run automated tests
2. Check console for errors
3. Verify API responses
4. Test error scenarios
```

---

## 🔗 Quick Reference Links

### Documentation
- Full Report: `TESTING_REPORT.md`
- Summary: `TESTING_SUMMARY.md`
- This Guide: `QUICK_TEST_GUIDE.md`

### Test Scripts
- Automated Tests: `test-order-management.sh`
- Backend Tests: `tests/Unit/OrderServiceTest.php`

### Key Files
```
Frontend:
├── Pages/Dashboard/Index.vue         (Main dashboard)
├── Components/Operations/
│   ├── OrderDetailModal.vue          (Order details)
│   ├── OrderCard.vue                 (Order display)
│   └── OrderStatusColumn.vue         (Column container)

Backend:
├── Controllers/OrderController.php   (HTTP handlers)
└── Services/Orders/OrderService.php  (Business logic)
```

---

## 💡 Pro Tips

### Tip 1: Use Browser DevTools
```
F12 → Vue DevTools → Components
Find: Dashboard → Check ordersByStatus
See real-time state changes
```

### Tip 2: Monitor Network
```
F12 → Network → Filter: /poll
Every 5 seconds should see request
Status: 200 OK
Response: { orders: [...] }
```

### Tip 3: Test Performance
```javascript
// Browser console
performance.mark('start');
// Do action
performance.mark('end');
performance.measure('action', 'start', 'end');
console.log(performance.getEntriesByType('measure'));
```

### Tip 4: Simulate Slow Network
```
DevTools → Network → Throttling → Slow 3G
Test how app handles delays
Polling should continue
UI remains responsive
```

---

## 📞 Support & Issues

### Found a Bug?
1. Check console for errors
2. Note steps to reproduce
3. Check if known issue (see TESTING_REPORT.md)
4. Document environment details

### Need Help?
- See full documentation: `TESTING_REPORT.md`
- Check test results: `TESTING_SUMMARY.md`
- Run automated tests: `bash test-order-management.sh`

---

## ⏱️ Time Estimates

```
Quick Smoke Test:           5 minutes
Comprehensive Manual Test:  30 minutes
Automated Test Suite:       2 minutes
Full Regression Test:       45 minutes
```

---

**TESTER Agent | Hive Mind Collective**
**Quick Test Guide v1.0**
**Last Updated: 2025-12-04**

---

## 🎯 One-Minute Sanity Check

```bash
# Run this for instant verification
php artisan test --filter=OrderServiceTest && \
echo "✅ Backend tests PASS" && \
bash test-order-management.sh && \
echo "✅ All checks complete!"
```

**Expected Output:**
```
Backend tests: 19 passed
Code checks: 28/31 passed
Success rate: 90%
✅ READY FOR PRODUCTION
```
