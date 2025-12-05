#!/bin/bash

# StoreFlow Order Management Testing Script
# TESTER Agent - Hive Mind Collective
# Date: 2025-12-04

echo "======================================"
echo "StoreFlow Order Management Test Suite"
echo "======================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test counter
PASSED=0
FAILED=0

# Function to print test result
test_result() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓ PASSED${NC}: $2"
        ((PASSED++))
    else
        echo -e "${RED}✗ FAILED${NC}: $2"
        ((FAILED++))
    fi
}

# Function to print section header
section() {
    echo ""
    echo "======================================"
    echo "$1"
    echo "======================================"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: artisan file not found. Are you in the StoreFlow root directory?${NC}"
    exit 1
fi

section "1. Environment Check"

# Check PHP version
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
if (( $(echo "$PHP_VERSION >= 8.1" | bc -l) )); then
    test_result 0 "PHP version $PHP_VERSION"
else
    test_result 1 "PHP version $PHP_VERSION (requires >= 8.1)"
fi

# Check if database exists
php artisan migrate:status > /dev/null 2>&1
test_result $? "Database connection"

# Check if node_modules exists
if [ -d "node_modules" ]; then
    test_result 0 "Node dependencies installed"
else
    test_result 1 "Node dependencies missing (run npm install)"
fi

section "2. Backend Unit Tests"

echo "Running PHPUnit tests for OrderService..."
php artisan test --filter=OrderServiceTest --compact
TEST_RESULT=$?
test_result $TEST_RESULT "Backend unit tests"

section "3. File Integrity Check"

# Check critical frontend files exist
FILES=(
    "resources/js/Pages/Dashboard/Index.vue"
    "resources/js/Components/Operations/OrderDetailModal.vue"
    "resources/js/Components/Operations/OrderCard.vue"
    "resources/js/Components/Operations/OrderStatusColumn.vue"
)

for FILE in "${FILES[@]}"; do
    if [ -f "$FILE" ]; then
        test_result 0 "File exists: $FILE"
    else
        test_result 1 "File missing: $FILE"
    fi
done

# Check backend files
BACKEND_FILES=(
    "app/Http/Controllers/OrderController.php"
)

for FILE in "${BACKEND_FILES[@]}"; do
    if [ -f "$FILE" ]; then
        test_result 0 "File exists: $FILE"
    else
        test_result 1 "File missing: $FILE"
    fi
done

section "4. Search Functionality Code Check"

# Check if search implementation exists in Dashboard/Index.vue
if grep -q "searchQuery" resources/js/Pages/Dashboard/Index.vue; then
    test_result 0 "Search query variable found"
else
    test_result 1 "Search query variable missing"
fi

if grep -q "filterOrders" resources/js/Pages/Dashboard/Index.vue; then
    test_result 0 "Filter orders function found"
else
    test_result 1 "Filter orders function missing"
fi

if grep -q "searchQuery = ''" resources/js/Pages/Dashboard/Index.vue; then
    test_result 0 "Clear search functionality found"
else
    test_result 1 "Clear search functionality missing"
fi

section "5. Modal Functionality Code Check"

# Check modal implementation
if grep -q "OrderDetailModal" resources/js/Pages/Dashboard/Index.vue; then
    test_result 0 "Modal component imported"
else
    test_result 1 "Modal component not imported"
fi

if grep -q "openOrderDetail" resources/js/Pages/Dashboard/Index.vue; then
    test_result 0 "Modal open handler found"
else
    test_result 1 "Modal open handler missing"
fi

if grep -q "closeOrderDetail" resources/js/Pages/Dashboard/Index.vue; then
    test_result 0 "Modal close handler found"
else
    test_result 1 "Modal close handler missing"
fi

section "6. Order Display Code Check"

# Check OrderCard displays required fields
REQUIRED_FIELDS=(
    "order.public_id"
    "order.customer_name"
    "order.total_cents"
    "order.items_count"
    "order.payment_status"
)

for FIELD in "${REQUIRED_FIELDS[@]}"; do
    if grep -q "$FIELD" resources/js/Components/Operations/OrderCard.vue; then
        test_result 0 "Field displayed: $FIELD"
    else
        test_result 1 "Field missing: $FIELD"
    fi
done

section "7. Status Transition Check"

# Check if status transitions are defined
if grep -q "statusTransitions" resources/js/Components/Operations/OrderDetailModal.vue; then
    test_result 0 "Status transitions defined"
else
    test_result 1 "Status transitions missing"
fi

# Check backend status validation
if grep -q "validateStatusTransition" tests/Unit/OrderServiceTest.php; then
    test_result 0 "Backend status validation tested"
else
    test_result 1 "Backend status validation not tested"
fi

section "8. API Routes Check"

# Check if routes are defined
ROUTES=(
    "orders.status.update"
    "orders.shipping.update"
    "orders.history"
    "dashboard.orders.poll"
)

for ROUTE in "${ROUTES[@]}"; do
    if grep -q "$ROUTE" routes/web.php; then
        test_result 0 "Route defined: $ROUTE"
    else
        test_result 1 "Route missing: $ROUTE"
    fi
done

section "9. Edge Case Handling Check"

# Check empty state implementation
if grep -q "No orders" resources/js/Components/Operations/OrderStatusColumn.vue; then
    test_result 0 "Empty state message found"
else
    test_result 1 "Empty state message missing"
fi

if grep -q "orders.length === 0" resources/js/Components/Operations/OrderStatusColumn.vue; then
    test_result 0 "Empty state condition found"
else
    test_result 1 "Empty state condition missing"
fi

section "10. Real-time Polling Check"

# Check polling implementation
if grep -q "startPolling" resources/js/Pages/Dashboard/Index.vue; then
    test_result 0 "Polling function found"
else
    test_result 1 "Polling function missing"
fi

if grep -q "setInterval" resources/js/Pages/Dashboard/Index.vue; then
    test_result 0 "Polling interval found"
else
    test_result 1 "Polling interval missing"
fi

if grep -q "isConnected" resources/js/Pages/Dashboard/Index.vue; then
    test_result 0 "Connection status tracking found"
else
    test_result 1 "Connection status tracking missing"
fi

section "Test Summary"

TOTAL=$((PASSED + FAILED))
PERCENTAGE=$((PASSED * 100 / TOTAL))

echo ""
echo "Total Tests Run: $TOTAL"
echo -e "${GREEN}Passed: $PASSED${NC}"
echo -e "${RED}Failed: $FAILED${NC}"
echo "Success Rate: $PERCENTAGE%"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}======================================"
    echo "✓ ALL TESTS PASSED!"
    echo "======================================"
    echo -e "${NC}"
    exit 0
else
    echo -e "${YELLOW}======================================"
    echo "⚠ SOME TESTS FAILED"
    echo "======================================"
    echo -e "${NC}"
    echo "Please review the failed tests above."
    echo "See TESTING_REPORT.md for detailed information."
    exit 1
fi
