# StoreFlow Public Storefront API Documentation

## Overview
This document describes the public-facing API endpoints for the StoreFlow platform according to A1.6 specification. These endpoints allow storefronts to interact with the StoreFlow backend for shipping quotes, checkout, and order tracking.

## Base URL
```
/api/v1
```

## Rate Limiting
- **Shipping Quote & Order Tracking**: 60 requests per minute
- **Checkout**: 10 requests per minute

## CORS
CORS is enabled for all API routes with the following configuration:
- Allowed Origins: `*` (configurable in `config/cors.php`)
- Allowed Methods: `*`
- Allowed Headers: `*`

---

## Endpoints

### 1. Calculate Shipping Quote

Calculate available shipping options and costs for a given destination and items.

**Endpoint:** `POST /api/v1/stores/{store}/shipping/quote`

**Rate Limit:** 60 requests/minute

**Parameters:**
- `{store}` - Store ID (path parameter)

**Request Body:**
```json
{
  "country": "AU",
  "state": "NSW",
  "postcode": "2000",
  "items": [
    {
      "product_id": 1,
      "qty": 2
    },
    {
      "product_id": 5,
      "qty": 1
    }
  ]
}
```

**Validation Rules:**
- `country`: Required, string, max 2 characters (ISO country code)
- `state`: Optional, string, max 100 characters
- `postcode`: Optional, string, max 20 characters
- `items`: Required, array, minimum 1 item
- `items.*.product_id`: Required, integer, must exist in products table
- `items.*.qty`: Required, integer, min 1, max 1000

**Success Response (200):**
```json
{
  "shipping_options": [
    {
      "id": 1,
      "name": "Standard Shipping",
      "description": "5-7 business days",
      "carrier": "Australia Post",
      "service_code": "AUS_PARCEL_REGULAR",
      "price_cents": 995,
      "delivery_estimate": "5-7 days",
      "min_delivery_days": 5,
      "max_delivery_days": 7
    },
    {
      "id": 2,
      "name": "Express Shipping",
      "description": "1-2 business days",
      "carrier": "Australia Post",
      "service_code": "AUS_PARCEL_EXPRESS",
      "price_cents": 1595,
      "delivery_estimate": "1-2 days",
      "min_delivery_days": 1,
      "max_delivery_days": 2
    }
  ]
}
```

**No Options Available Response (200):**
```json
{
  "message": "No shipping options available for this destination.",
  "shipping_options": []
}
```

**Error Responses:**

**422 - Shipping Not Enabled:**
```json
{
  "message": "Shipping is not enabled for this store."
}
```

**422 - Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "country": ["The country field is required."],
    "items.0.product_id": ["The selected product does not exist."]
  }
}
```

---

### 2. Checkout / Create Order

Process checkout and create a new order.

**Endpoint:** `POST /api/v1/stores/{store}/checkout`

**Rate Limit:** 10 requests/minute

**Parameters:**
- `{store}` - Store ID (path parameter)

**Request Body (Delivery Order):**
```json
{
  "fulfilment_type": "delivery",
  "contact": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "mobile": "+61412345678"
  },
  "shipping_address": {
    "name": "John Doe",
    "line1": "123 Main Street",
    "line2": "Apt 4",
    "city": "Sydney",
    "state": "NSW",
    "postcode": "2000",
    "country": "AU"
  },
  "shipping_method_id": 1,
  "items": [
    {
      "product_id": 1,
      "qty": 2,
      "customizations": [
        {
          "group_id": 1,
          "option_id": 3
        }
      ]
    }
  ]
}
```

**Request Body (Pickup Order):**
```json
{
  "fulfilment_type": "pickup",
  "contact": {
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@example.com",
    "mobile": "+61498765432"
  },
  "pickup_time": "2025-12-05T14:00:00Z",
  "items": [
    {
      "product_id": 2,
      "qty": 1
    }
  ]
}
```

**Validation Rules:**
- `fulfilment_type`: Required, must be 'delivery' or 'pickup'
- `contact`: Required object
  - `first_name`: Required, string, max 255
  - `last_name`: Required, string, max 255
  - `email`: Required, valid email, max 255
  - `mobile`: Optional, string, max 20
- `items`: Required, array, minimum 1 item
  - `product_id`: Required, integer, must exist
  - `qty`: Required, integer, min 1, max 1000
  - `customizations`: Optional array
    - `group_id`: Required if customizations present
    - `option_id`: Required if customizations present

**For Delivery Orders (additional requirements):**
- `shipping_address`: Required object
  - `name`: Required, string, max 255
  - `line1`: Required, string, max 255
  - `line2`: Optional, string, max 255
  - `city`: Required, string, max 100
  - `state`: Required, string, max 100
  - `postcode`: Required, string, max 20
  - `country`: Required, string, max 2 (ISO code)
- `shipping_method_id`: Required, integer, must exist

**For Pickup Orders (additional requirements):**
- `pickup_time`: Optional, datetime, must be in the future

**Success Response (201):**
```json
{
  "order_id": 42,
  "public_id": "ORD-A1B2C3D4E5F6",
  "status": "pending",
  "total_cents": 4985,
  "created_at": "2025-12-03T10:30:00Z"
}
```

**Error Responses:**

**422 - Delivery Not Available:**
```json
{
  "message": "Delivery is not available for this store."
}
```

**422 - Invalid Shipping Method:**
```json
{
  "message": "The selected shipping method is not available for this destination."
}
```

**422 - Product Not Available:**
```json
{
  "message": "Product with ID 123 is not available."
}
```

**422 - Validation Error:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "fulfilment_type": ["The fulfilment type is required."],
    "contact.email": ["The contact email must be a valid email address."],
    "shipping_address": ["Shipping address is required for delivery orders."]
  }
}
```

**500 - Server Error:**
```json
{
  "message": "An error occurred while processing your order.",
  "error": "Detailed error message (only in debug mode)"
}
```

---

### 3. Track Order

Get public order information and real-time channel details.

**Endpoint:** `GET /api/v1/orders/{public_id}`

**Rate Limit:** 60 requests/minute

**Parameters:**
- `{public_id}` - Order public ID (path parameter, e.g., "ORD-A1B2C3D4E5F6")

**Success Response (200) - Delivery Order:**
```json
{
  "public_id": "ORD-A1B2C3D4E5F6",
  "status": "pending",
  "payment_status": "unpaid",
  "fulfilment_type": "shipping",
  "items_total_cents": 3990,
  "shipping_cost_cents": 995,
  "total_cents": 4985,
  "created_at": "2025-12-03T10:30:00Z",
  "updated_at": "2025-12-03T10:30:00Z",
  "customer": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com"
  },
  "items": [
    {
      "product_name": "Premium Coffee Beans",
      "qty": 2,
      "unit_price_cents": 1995,
      "customizations": [
        {
          "option_name": "Medium Grind",
          "qty": 2,
          "price_delta_cents": 0
        }
      ]
    }
  ],
  "shipping": {
    "status": "pending",
    "tracking_code": null,
    "tracking_url": null,
    "address": {
      "name": "John Doe",
      "line1": "123 Main Street",
      "line2": "Apt 4",
      "city": "Sydney",
      "state": "NSW",
      "postcode": "2000",
      "country": "AU"
    }
  },
  "realtime_channel": "order.ORD-A1B2C3D4E5F6"
}
```

**Success Response (200) - Pickup Order:**
```json
{
  "public_id": "ORD-X7Y8Z9A0B1C2",
  "status": "ready",
  "payment_status": "paid",
  "fulfilment_type": "pickup",
  "items_total_cents": 2495,
  "shipping_cost_cents": 0,
  "total_cents": 2495,
  "created_at": "2025-12-03T08:15:00Z",
  "updated_at": "2025-12-03T09:45:00Z",
  "customer": {
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@example.com"
  },
  "items": [
    {
      "product_name": "Artisan Pastries",
      "qty": 1,
      "unit_price_cents": 2495,
      "customizations": []
    }
  ],
  "pickup": {
    "time": "2025-12-05T14:00:00Z"
  },
  "realtime_channel": "order.ORD-X7Y8Z9A0B1C2"
}
```

**Error Response:**

**404 - Order Not Found:**
```json
{
  "message": "Order not found."
}
```

---

## Order Status Values

### Status
- `pending` - Order received, awaiting processing
- `accepted` - Order accepted by merchant
- `in_progress` - Order being prepared
- `packing` - Order being packed (delivery orders)
- `ready` - Order ready for pickup/dispatch
- `shipped` - Order shipped (delivery orders)
- `delivered` - Order delivered (delivery orders)
- `completed` - Order completed (pickup orders)
- `cancelled` - Order cancelled

### Payment Status
- `unpaid` - Payment pending
- `paid` - Payment received
- `refunded` - Payment refunded

### Shipping Status (Delivery Orders Only)
- `pending` - Not yet shipped
- `shipped` - In transit
- `delivered` - Successfully delivered

---

## Real-time Updates

Orders broadcast real-time updates via WebSocket channels. The channel name is provided in the order tracking response.

**Channel:** `order.{public_id}`

**Events:**
- `order.created` - When order is first created
- `order.updated` - When order status changes
- `order.shipped` - When order is marked as shipped (delivery)
- `order.delivered` - When order is marked as delivered (delivery)

**Example Event Payload:**
```json
{
  "public_id": "ORD-A1B2C3D4E5F6",
  "status": "shipped",
  "fulfilment_type": "shipping",
  "total_cents": 4985,
  "created_at": "2025-12-03T10:30:00Z"
}
```

---

## Implementation Details

### Service Classes
- **ShippingEngine** (`app/Services/ShippingEngine.php`)
  - Calculates shipping quotes based on zones, rates, and item weights
  - Validates shipping method availability for destinations
  - Supports weight-based, item-based, and flat-rate calculations

### Controllers
- **ShippingQuoteController** (`app/Http/Controllers/Api/ShippingQuoteController.php`)
  - Handles shipping quote requests

- **CheckoutController** (`app/Http/Controllers/Api/CheckoutController.php`)
  - Processes checkout and order creation
  - Creates or updates customer records
  - Calculates order totals including customizations
  - Broadcasts OrderCreated event

- **OrderTrackingController** (`app/Http/Controllers/Api/OrderTrackingController.php`)
  - Provides public order tracking information
  - Returns real-time channel information

### Form Requests
- **ShippingQuoteRequest** (`app/Http/Requests/Api/ShippingQuoteRequest.php`)
- **CheckoutRequest** (`app/Http/Requests/Api/CheckoutRequest.php`)
  - Dynamic validation based on fulfilment_type

### Events
- **OrderCreated** (`app/Events/OrderCreated.php`)
  - Broadcasts to merchant and store channels
  - Implements ShouldBroadcast interface

---

## Testing

### Test Shipping Quote
```bash
curl -X POST http://localhost/api/v1/stores/1/shipping/quote \
  -H "Content-Type: application/json" \
  -d '{
    "country": "AU",
    "state": "NSW",
    "postcode": "2000",
    "items": [
      {"product_id": 1, "qty": 2}
    ]
  }'
```

### Test Checkout
```bash
curl -X POST http://localhost/api/v1/stores/1/checkout \
  -H "Content-Type: application/json" \
  -d '{
    "fulfilment_type": "delivery",
    "contact": {
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com"
    },
    "shipping_address": {
      "name": "John Doe",
      "line1": "123 Main St",
      "city": "Sydney",
      "state": "NSW",
      "postcode": "2000",
      "country": "AU"
    },
    "shipping_method_id": 1,
    "items": [
      {"product_id": 1, "qty": 2}
    ]
  }'
```

### Test Order Tracking
```bash
curl -X GET http://localhost/api/v1/orders/ORD-A1B2C3D4E5F6
```

---

## Security Considerations

1. **Rate Limiting**: Configured per endpoint to prevent abuse
2. **CORS**: Configured to allow cross-origin requests from storefronts
3. **Public ID**: Orders use public_id instead of internal IDs for tracking
4. **Guest Checkout**: Supports guest customers without authentication
5. **Validation**: All inputs are validated using Form Request classes
6. **Database Transactions**: Checkout uses transactions to ensure data consistency
7. **Error Handling**: Generic error messages in production, detailed in debug mode

---

## Notes

- The checkout endpoint accepts both guest and registered customers
- Customer records are created/updated automatically based on email
- Order public_id is generated using format: `ORD-{12 random uppercase alphanumeric chars}`
- The API uses 'shipping' internally but accepts 'delivery' from the frontend for consistency
- All prices are stored and returned in cents to avoid floating-point precision issues
- Timestamps follow ISO 8601 format (RFC 3339)
