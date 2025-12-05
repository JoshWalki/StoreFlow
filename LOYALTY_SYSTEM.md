# Loyalty System Implementation

This document describes the implementation of the Loyalty System for StoreFlow, following the specification from A1.2 Data Model.

## Overview

The Loyalty System allows merchants to reward customers with points based on their purchases. Customers can accumulate points and redeem them for rewards when they reach specific thresholds.

## Database Structure

### 1. loyalty_config Table
Stores loyalty program configuration per merchant.

**Location:** `/mnt/c/xampp/htdocs/StoreFlow/database/migrations/2025_12_03_000001_create_loyalty_config_table.php`

**Columns:**
- `id` - Primary key
- `merchant_id` - Foreign key to merchants table (unique per merchant)
- `points_per_dollar` - Decimal(8,2), default 1.00 - Points earned per dollar spent
- `threshold` - Integer, default 100 - Points required to unlock reward
- `reward_json` - JSON - Stores reward configuration (type, value, description, etc.)
- `is_active` - Boolean, default true - Whether loyalty program is active
- `timestamps` - created_at, updated_at

**Indexes:**
- Unique index on `merchant_id` (one config per merchant)

### 2. loyalty_accounts Table
Stores loyalty point balances and history per customer.

**Location:** `/mnt/c/xampp/htdocs/StoreFlow/database/migrations/2025_12_03_000002_create_loyalty_accounts_table.php`

**Columns:**
- `id` - Primary key
- `customer_id` - Foreign key to customers table
- `merchant_id` - Foreign key to merchants table
- `points_balance` - Integer, default 0 - Current available points
- `points_earned` - Integer, default 0 - Total points earned in current period
- `points_redeemed` - Integer, default 0 - Total points redeemed
- `lifetime_points` - Integer, default 0 - All-time points earned
- `last_activity_at` - Timestamp - Last loyalty transaction
- `timestamps` - created_at, updated_at

**Indexes:**
- Unique composite index on `[customer_id, merchant_id]` (one account per customer per merchant)
- Index on `merchant_id` for merchant-scoped queries

## Models

### 1. LoyaltyConfig Model
**Location:** `/mnt/c/xampp/htdocs/StoreFlow/app/Models/LoyaltyConfig.php`

**Key Features:**
- JSON casting for `reward_json` field
- Relationship to Merchant model
- Helper methods:
  - `getRewardConfig()` - Returns reward configuration as array
  - `setRewardConfig(array $config)` - Sets reward configuration

**Relationships:**
- `belongsTo(Merchant::class)`

### 2. LoyaltyAccount Model
**Location:** `/mnt/c/xampp/htdocs/StoreFlow/app/Models/LoyaltyAccount.php`

**Key Features:**
- Tracks customer loyalty points
- Helper methods:
  - `hasSufficientPoints(int $points)` - Checks if balance is sufficient
  - `hasReachedThreshold(int $threshold)` - Checks if threshold reached

**Relationships:**
- `belongsTo(Customer::class)`
- `belongsTo(Merchant::class)`

### 3. Customer Model Update
**Location:** `/mnt/c/xampp/htdocs/StoreFlow/app/Models/Customer.php`

**Added Relationship:**
- `hasOne(LoyaltyAccount::class)` - One-to-one relationship with loyalty account

## Service Layer

### LoyaltyService
**Location:** `/mnt/c/xampp/htdocs/StoreFlow/app/Services/Loyalty/LoyaltyService.php`

This service handles all loyalty program business logic with proper merchant_id scoping.

#### Key Methods:

##### 1. calculatePointsForOrder(Order $order): int
Calculates points to be awarded for an order based on merchant's loyalty configuration.

**Logic:**
- Fetches active loyalty config for order's merchant
- Converts order total from cents to dollars
- Multiplies by points_per_dollar rate
- Returns floored integer value

**Example:**
```php
$loyaltyService = app(LoyaltyService::class);
$points = $loyaltyService->calculatePointsForOrder($order);
// Order total: $50.00, points_per_dollar: 1.5 → Returns: 75 points
```

##### 2. awardPoints(Customer $customer, int $points): LoyaltyAccount
Awards points to a customer's loyalty account.

**Features:**
- Creates loyalty account if it doesn't exist
- Uses database transaction for consistency
- Updates: points_balance, points_earned, lifetime_points
- Sets last_activity_at timestamp
- Logs activity

**Example:**
```php
$account = $loyaltyService->awardPoints($customer, 100);
```

##### 3. redeemPoints(Customer $customer, int $points): LoyaltyAccount
Redeems points from a customer's account.

**Features:**
- Uses row-level locking (lockForUpdate) to prevent race conditions
- Validates sufficient balance
- Updates: points_balance, points_redeemed
- Throws exception if insufficient points
- Logs activity

**Example:**
```php
try {
    $account = $loyaltyService->redeemPoints($customer, 50);
} catch (\Exception $e) {
    // Handle insufficient points or missing account
}
```

##### 4. checkThresholdAndIssueReward(Customer $customer): ?array
Checks if customer has reached the loyalty threshold for rewards.

**Returns:**
- Null if threshold not reached or config not found
- Array with reward information if threshold reached:
  ```php
  [
      'threshold_reached' => true,
      'points_balance' => 150,
      'threshold' => 100,
      'reward_config' => [...], // From loyalty_config.reward_json
      'customer_id' => 1,
      'merchant_id' => 1,
  ]
  ```

**Example:**
```php
$reward = $loyaltyService->checkThresholdAndIssueReward($customer);
if ($reward) {
    // Send notification, generate coupon, etc.
}
```

##### 5. processOrderLoyalty(Order $order): array
Convenience method that combines the full loyalty workflow for an order.

**Process:**
1. Validates order has a customer
2. Calculates points
3. Awards points
4. Checks for threshold rewards

**Returns:**
```php
[
    'success' => true,
    'points_awarded' => 75,
    'account' => LoyaltyAccount,
    'reward' => [...] // or null
]
```

**Example:**
```php
$result = $loyaltyService->processOrderLoyalty($order);
if ($result['success'] && $result['reward']) {
    // Handle reward issuance
}
```

##### 6. Helper Methods

**getLoyaltyAccount(Customer $customer): ?LoyaltyAccount**
- Retrieves existing loyalty account
- Returns null if not found

**getOrCreateLoyaltyAccount(Customer $customer): LoyaltyAccount**
- Gets existing account or creates new one
- Always returns an account

## Service Provider

### LoyaltyServiceProvider
**Location:** `/mnt/c/xampp/htdocs/StoreFlow/app/Providers/LoyaltyServiceProvider.php`

Registers the LoyaltyService as a singleton in the Laravel container.

**Registered in:** `/mnt/c/xampp/htdocs/StoreFlow/config/app.php`

**Usage:**
```php
// Dependency Injection
public function __construct(LoyaltyService $loyaltyService)
{
    $this->loyaltyService = $loyaltyService;
}

// Or resolve from container
$loyaltyService = app(LoyaltyService::class);
```

## Merchant Scoping

All queries are properly scoped by `merchant_id` to ensure data isolation:

1. **LoyaltyConfig** - Has unique constraint on merchant_id
2. **LoyaltyAccount** - Has composite unique constraint on [customer_id, merchant_id]
3. **All service methods** - Use merchant_id from Customer or Order model

## Usage Examples

### Setting Up Loyalty Program for a Merchant

```php
use App\Models\LoyaltyConfig;

// Create loyalty configuration
LoyaltyConfig::create([
    'merchant_id' => 1,
    'points_per_dollar' => 2.0, // 2 points per dollar
    'threshold' => 500, // Reward at 500 points
    'reward_json' => [
        'type' => 'discount',
        'value' => 10, // $10 off
        'description' => 'Get $10 off your next purchase!',
        'expires_days' => 30,
    ],
    'is_active' => true,
]);
```

### Processing Order with Loyalty

```php
use App\Services\Loyalty\LoyaltyService;

$loyaltyService = app(LoyaltyService::class);

// After order completion
$result = $loyaltyService->processOrderLoyalty($order);

if ($result['success']) {
    // Points awarded
    $pointsAwarded = $result['points_awarded'];

    // Check if reward earned
    if ($result['reward']) {
        // Issue reward notification
        $customer = $order->customer;
        $rewardConfig = $result['reward']['reward_config'];

        // Send email, create coupon, etc.
    }
}
```

### Manual Points Operations

```php
$loyaltyService = app(LoyaltyService::class);

// Award bonus points
$account = $loyaltyService->awardPoints($customer, 100);

// Redeem points for a reward
try {
    $account = $loyaltyService->redeemPoints($customer, 500);
    // Issue the reward
} catch (\Exception $e) {
    // Handle error (insufficient points, etc.)
}

// Check current balance
$account = $loyaltyService->getLoyaltyAccount($customer);
echo "Current balance: " . $account->points_balance;
```

### Checking Loyalty Status

```php
// Get customer's loyalty account
$account = $customer->loyaltyAccount;

if ($account) {
    echo "Points Balance: " . $account->points_balance;
    echo "Lifetime Points: " . $account->lifetime_points;
    echo "Points Redeemed: " . $account->points_redeemed;
}

// Check if eligible for reward
$reward = $loyaltyService->checkThresholdAndIssueReward($customer);
if ($reward) {
    echo "You've earned a reward!";
}
```

## Running Migrations

To create the database tables, run:

```bash
php artisan migrate
```

This will create both the `loyalty_config` and `loyalty_accounts` tables.

## Security Considerations

1. **Row-Level Locking** - Used in `redeemPoints()` to prevent race conditions
2. **Merchant Scoping** - All queries filtered by merchant_id
3. **Validation** - Points must be positive, accounts validated before redemption
4. **Transactions** - Database transactions ensure consistency
5. **Logging** - All loyalty operations logged for audit trail

## Extension Points

The system is designed to be extensible:

1. **Reward Types** - The `reward_json` field can store any reward structure
2. **Point Expiration** - Can add expiry logic in future
3. **Tiered Rewards** - Can add multiple thresholds
4. **Point Sources** - Can award points from various actions beyond orders
5. **Referral Bonuses** - Can integrate referral point awards

## Testing

Example test cases to implement:

```php
// Test point calculation
$points = $loyaltyService->calculatePointsForOrder($order);
$this->assertEquals(expected, $points);

// Test point awarding
$account = $loyaltyService->awardPoints($customer, 100);
$this->assertEquals(100, $account->points_balance);

// Test redemption
$account = $loyaltyService->redeemPoints($customer, 50);
$this->assertEquals(50, $account->points_balance);

// Test insufficient points
$this->expectException(\Exception::class);
$loyaltyService->redeemPoints($customer, 1000);

// Test threshold detection
$reward = $loyaltyService->checkThresholdAndIssueReward($customer);
$this->assertNotNull($reward);
```

## Notes

- All monetary values in orders are stored in cents, converted to dollars for point calculation
- Points are always integers (floored after calculation)
- One loyalty configuration per merchant (enforced by unique constraint)
- One loyalty account per customer per merchant (enforced by composite unique constraint)
- Loyalty account is automatically created on first point award
- All operations are logged for debugging and audit purposes
