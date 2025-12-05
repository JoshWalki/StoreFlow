<?php

namespace Tests\Feature;

use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Mail\OrderCompletedMail;
use App\Mail\OrderPlacedMail;
use App\Mail\OrderReadyForPickupMail;
use App\Mail\OrderShippedMail;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Merchant $merchant;
    protected Store $store;
    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->merchant = Merchant::factory()->create();

        $this->store = Store::factory()->create([
            'merchant_id' => $this->merchant->id,
            'name' => 'Test Store',
            'contact_email' => 'store@example.com',
            'contact_phone' => '1234567890',
            'address_primary' => '123 Test St',
            'address_city' => 'Test City',
            'address_state' => 'TS',
            'address_postcode' => '12345',
        ]);

        $this->customer = Customer::factory()->create([
            'merchant_id' => $this->merchant->id,
            'email' => 'customer@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->product = Product::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'name' => 'Test Product',
            'price_cents' => 1000,
        ]);
    }

    /** @test */
    public function email_is_sent_when_order_is_created()
    {
        Mail::fake();

        // Create an order
        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
            'customer_email' => $this->customer->email,
            'customer_name' => $this->customer->first_name . ' ' . $this->customer->last_name,
            'status' => Order::STATUS_PENDING,
            'total_cents' => 1000,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'unit_price_cents' => 1000,
        ]);

        // Fire the OrderCreated event
        event(new OrderCreated($order));

        // Assert email was sent
        Mail::assertSent(OrderPlacedMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id &&
                   $mail->hasTo($this->customer->email);
        });
    }

    /** @test */
    public function email_is_sent_when_order_status_changes_to_ready_for_pickup()
    {
        Mail::fake();

        $order = $this->createOrderWithItems();

        // Fire the OrderStatusUpdated event
        event(new OrderStatusUpdated(
            $order,
            Order::STATUS_PENDING,
            Order::STATUS_READY_FOR_PICKUP
        ));

        // Assert email was sent
        Mail::assertSent(OrderReadyForPickupMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id &&
                   $mail->hasTo($this->customer->email);
        });
    }

    /** @test */
    public function email_is_sent_when_order_status_changes_to_shipped()
    {
        Mail::fake();

        $order = $this->createOrderWithItems([
            'fulfilment_type' => Order::FULFILMENT_SHIPPING,
            'tracking_code' => 'TRACK123456',
            'tracking_url' => 'https://tracking.example.com/TRACK123456',
        ]);

        // Fire the OrderStatusUpdated event
        event(new OrderStatusUpdated(
            $order,
            Order::STATUS_PACKING,
            Order::STATUS_SHIPPED
        ));

        // Assert email was sent
        Mail::assertSent(OrderShippedMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id &&
                   $mail->hasTo($this->customer->email);
        });
    }

    /** @test */
    public function email_is_sent_when_order_status_changes_to_completed()
    {
        Mail::fake();

        $order = $this->createOrderWithItems();

        // Fire the OrderStatusUpdated event
        event(new OrderStatusUpdated(
            $order,
            Order::STATUS_READY_FOR_PICKUP,
            Order::STATUS_COMPLETED
        ));

        // Assert email was sent
        Mail::assertSent(OrderCompletedMail::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id &&
                   $mail->hasTo($this->customer->email);
        });
    }

    /** @test */
    public function email_contains_correct_order_information()
    {
        Mail::fake();

        $order = $this->createOrderWithItems([
            'public_id' => 'ORD-TEST-001',
            'total_cents' => 1500,
            'items_total_cents' => 1000,
            'shipping_cost_cents' => 500,
        ]);

        event(new OrderCreated($order));

        Mail::assertSent(OrderPlacedMail::class, function ($mail) use ($order) {
            $mailOrder = $mail->order;

            return $mailOrder->public_id === 'ORD-TEST-001' &&
                   $mailOrder->total_cents === 1500 &&
                   $mailOrder->items_total_cents === 1000 &&
                   $mailOrder->shipping_cost_cents === 500;
        });
    }

    /** @test */
    public function email_includes_store_branding_information()
    {
        Mail::fake();

        $order = $this->createOrderWithItems();

        event(new OrderCreated($order));

        Mail::assertSent(OrderPlacedMail::class, function ($mail) {
            $store = $mail->order->store;

            return $store->name === 'Test Store' &&
                   $store->contact_email === 'store@example.com' &&
                   $store->contact_phone === '1234567890';
        });
    }

    /** @test */
    public function email_is_sent_to_correct_customer_email()
    {
        Mail::fake();

        $order = $this->createOrderWithItems([
            'customer_email' => 'specific@customer.com',
        ]);

        event(new OrderCreated($order));

        Mail::assertSent(OrderPlacedMail::class, function ($mail) {
            return $mail->hasTo('specific@customer.com');
        });
    }

    /** @test */
    public function no_email_sent_for_non_customer_facing_status_changes()
    {
        Mail::fake();

        $order = $this->createOrderWithItems();

        // Fire event for internal status change
        event(new OrderStatusUpdated(
            $order,
            Order::STATUS_PENDING,
            Order::STATUS_ACCEPTED
        ));

        // Assert no email was sent
        Mail::assertNothingSent();
    }

    /** @test */
    public function email_includes_tracking_information_when_order_is_shipped()
    {
        Mail::fake();

        $order = $this->createOrderWithItems([
            'fulfilment_type' => Order::FULFILMENT_SHIPPING,
            'tracking_code' => 'TRACK-XYZ-789',
            'tracking_url' => 'https://tracking.example.com/TRACK-XYZ-789',
            'shipping_name' => 'Jane Smith',
            'shipping_line1' => '456 Ship St',
            'city' => 'Shipping City',
            'state' => 'SC',
            'postcode' => '54321',
            'country' => 'USA',
        ]);

        event(new OrderStatusUpdated(
            $order,
            Order::STATUS_PACKING,
            Order::STATUS_SHIPPED
        ));

        Mail::assertSent(OrderShippedMail::class, function ($mail) use ($order) {
            return $mail->order->tracking_code === 'TRACK-XYZ-789' &&
                   $mail->order->tracking_url === 'https://tracking.example.com/TRACK-XYZ-789';
        });
    }

    /** @test */
    public function email_includes_pickup_information_for_pickup_orders()
    {
        Mail::fake();

        $pickupTime = now()->addDays(2);

        $order = $this->createOrderWithItems([
            'fulfilment_type' => Order::FULFILMENT_PICKUP,
            'pickup_time' => $pickupTime,
        ]);

        event(new OrderStatusUpdated(
            $order,
            Order::STATUS_IN_PROGRESS,
            Order::STATUS_READY_FOR_PICKUP
        ));

        Mail::assertSent(OrderReadyForPickupMail::class, function ($mail) use ($order, $pickupTime) {
            return $mail->order->fulfilment_type === Order::FULFILMENT_PICKUP &&
                   $mail->order->pickup_time->equalTo($pickupTime);
        });
    }

    /** @test */
    public function email_includes_all_order_items_with_correct_pricing()
    {
        Mail::fake();

        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
            'customer_email' => $this->customer->email,
            'customer_name' => $this->customer->first_name . ' ' . $this->customer->last_name,
            'status' => Order::STATUS_PENDING,
            'items_total_cents' => 3500,
            'total_cents' => 3500,
        ]);

        // Create multiple items
        $product1 = Product::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'name' => 'Product One',
            'price_cents' => 1000,
        ]);

        $product2 = Product::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'name' => 'Product Two',
            'price_cents' => 1500,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'qty' => 2,
            'unit_price_cents' => 1000,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'qty' => 1,
            'unit_price_cents' => 1500,
        ]);

        event(new OrderCreated($order));

        Mail::assertSent(OrderPlacedMail::class, function ($mail) use ($order) {
            $items = $mail->order->items;

            return $items->count() === 2 &&
                   $items->sum(fn($item) => $item->qty * $item->unit_price_cents) === 3500;
        });
    }

    /** @test */
    public function multiple_status_updates_send_appropriate_emails()
    {
        Mail::fake();

        $order = $this->createOrderWithItems([
            'fulfilment_type' => Order::FULFILMENT_SHIPPING,
            'tracking_code' => 'TRACK123',
        ]);

        // First status change: shipped
        event(new OrderStatusUpdated(
            $order,
            Order::STATUS_PACKING,
            Order::STATUS_SHIPPED
        ));

        Mail::assertSent(OrderShippedMail::class);

        // Second status change: completed
        $order->status = Order::STATUS_SHIPPED;
        event(new OrderStatusUpdated(
            $order,
            Order::STATUS_SHIPPED,
            Order::STATUS_COMPLETED
        ));

        Mail::assertSent(OrderCompletedMail::class);
    }

    /**
     * Helper method to create an order with items
     */
    protected function createOrderWithItems(array $attributes = []): Order
    {
        $order = Order::factory()->create(array_merge([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
            'customer_email' => $this->customer->email,
            'customer_name' => $this->customer->first_name . ' ' . $this->customer->last_name,
            'status' => Order::STATUS_PENDING,
            'items_total_cents' => 1000,
            'total_cents' => 1000,
        ], $attributes));

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'unit_price_cents' => 1000,
        ]);

        return $order->fresh(['customer', 'store', 'items.product']);
    }
}
