<?php

namespace Tests\Unit;

use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Events\ShippingStatusUpdated;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Services\Orders\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OrderService $orderService;
    protected Merchant $merchant;
    protected Store $store;
    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = new OrderService();

        // Create test data
        $this->merchant = Merchant::factory()->create();
        $this->store = Store::factory()->create(['merchant_id' => $this->merchant->id]);
        $this->customer = Customer::factory()->create(['merchant_id' => $this->merchant->id]);
        $this->product = Product::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
        ]);
    }

    public function test_create_shipping_order_successfully()
    {
        Event::fake([OrderCreated::class]);

        $orderData = [
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
            'fulfilment_type' => 'shipping',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 2,
                    'unit_price_cents' => 1000,
                ],
            ],
            'shipping_cost_cents' => 500,
            'shipping_name' => 'John Doe',
            'line1' => '123 Main St',
            'city' => 'Sydney',
            'state' => 'NSW',
            'postcode' => '2000',
            'country' => 'AU',
        ];

        $order = $this->orderService->createOrder($orderData);

        $this->assertNotNull($order);
        $this->assertStringStartsWith('SF-', $order->public_id);
        $this->assertEquals(Order::STATUS_PENDING, $order->status);
        $this->assertEquals(2000, $order->items_total_cents);
        $this->assertEquals(500, $order->shipping_cost_cents);
        $this->assertEquals(2500, $order->total_cents);
        $this->assertCount(1, $order->items);

        Event::assertDispatched(OrderCreated::class);
    }

    public function test_create_pickup_order_successfully()
    {
        Event::fake([OrderCreated::class]);

        $orderData = [
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
            'fulfilment_type' => 'pickup',
            'pickup_time' => now()->addHours(2),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'qty' => 1,
                    'unit_price_cents' => 1500,
                ],
            ],
        ];

        $order = $this->orderService->createOrder($orderData);

        $this->assertNotNull($order);
        $this->assertEquals('pickup', $order->fulfilment_type);
        $this->assertEquals(1500, $order->total_cents);
        $this->assertNotNull($order->pickup_time);

        Event::assertDispatched(OrderCreated::class);
    }

    public function test_validate_status_transition_allowed()
    {
        $this->assertTrue(
            $this->orderService->validateStatusTransition(
                Order::STATUS_PENDING,
                Order::STATUS_ACCEPTED
            )
        );

        $this->assertTrue(
            $this->orderService->validateStatusTransition(
                Order::STATUS_ACCEPTED,
                Order::STATUS_IN_PROGRESS
            )
        );

        $this->assertTrue(
            $this->orderService->validateStatusTransition(
                Order::STATUS_READY,
                Order::STATUS_PACKING
            )
        );
    }

    public function test_validate_status_transition_not_allowed()
    {
        $this->assertFalse(
            $this->orderService->validateStatusTransition(
                Order::STATUS_PENDING,
                Order::STATUS_SHIPPED
            )
        );

        $this->assertFalse(
            $this->orderService->validateStatusTransition(
                Order::STATUS_DELIVERED,
                Order::STATUS_PENDING
            )
        );
    }

    public function test_update_order_status_successfully()
    {
        Event::fake([OrderStatusUpdated::class]);

        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'status' => Order::STATUS_PENDING,
        ]);

        $updatedOrder = $this->orderService->updateOrderStatus(
            $order,
            Order::STATUS_ACCEPTED
        );

        $this->assertEquals(Order::STATUS_ACCEPTED, $updatedOrder->status);

        Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($order) {
            return $event->order->id === $order->id
                && $event->oldStatus === Order::STATUS_PENDING
                && $event->newStatus === Order::STATUS_ACCEPTED;
        });
    }

    public function test_update_order_status_invalid_transition_throws_exception()
    {
        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'status' => Order::STATUS_PENDING,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid status transition from 'pending' to 'shipped'");

        $this->orderService->updateOrderStatus($order, Order::STATUS_SHIPPED);
    }

    public function test_packing_status_only_for_shipping_orders()
    {
        $pickupOrder = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'fulfilment_type' => 'pickup',
            'status' => Order::STATUS_READY,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Status 'packing' is only valid for shipping orders");

        $this->orderService->updateOrderStatus($pickupOrder, Order::STATUS_PACKING);
    }

    public function test_ready_for_pickup_status_only_for_pickup_orders()
    {
        $shippingOrder = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'fulfilment_type' => 'shipping',
            'status' => Order::STATUS_READY,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Status 'ready_for_pickup' is only valid for pickup orders");

        $this->orderService->updateOrderStatus($shippingOrder, Order::STATUS_READY_FOR_PICKUP);
    }

    public function test_update_shipping_status()
    {
        Event::fake([ShippingStatusUpdated::class]);

        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'fulfilment_type' => 'shipping',
            'status' => Order::STATUS_SHIPPED,
        ]);

        $updatedOrder = $this->orderService->updateShippingStatus(
            $order,
            'in_transit',
            [
                'tracking_code' => 'ABC123456',
                'tracking_url' => 'https://track.example.com/ABC123456',
            ]
        );

        $this->assertEquals('in_transit', $updatedOrder->shipping_status);
        $this->assertEquals('ABC123456', $updatedOrder->tracking_code);
        $this->assertEquals('https://track.example.com/ABC123456', $updatedOrder->tracking_url);

        Event::assertDispatched(ShippingStatusUpdated::class);
    }

    public function test_update_shipping_status_fails_for_pickup_orders()
    {
        $pickupOrder = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'fulfilment_type' => 'pickup',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Cannot update shipping status for non-shipping orders");

        $this->orderService->updateShippingStatus($pickupOrder, 'in_transit');
    }

    public function test_calculate_order_totals()
    {
        $items = [
            ['qty' => 2, 'unit_price_cents' => 1000],
            ['qty' => 1, 'unit_price_cents' => 500],
            ['qty' => 3, 'unit_price_cents' => 750],
        ];

        $totals = $this->orderService->calculateOrderTotals($items);

        $this->assertEquals(4750, $totals['items_total_cents']);
        $this->assertEquals(4750, $totals['total_cents']);
    }

    public function test_generate_public_id_format()
    {
        $publicId = $this->orderService->generatePublicId();

        $this->assertMatchesRegularExpression('/^SF-\d{5}$/', $publicId);
    }

    public function test_generate_unique_public_ids()
    {
        $ids = [];
        for ($i = 0; $i < 10; $i++) {
            $ids[] = $this->orderService->generatePublicId();
        }

        $this->assertEquals(count($ids), count(array_unique($ids)));
    }

    public function test_cancel_order_successfully()
    {
        Event::fake([OrderStatusUpdated::class]);

        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'status' => Order::STATUS_ACCEPTED,
        ]);

        $cancelledOrder = $this->orderService->cancelOrder($order, 'Customer requested cancellation');

        $this->assertEquals(Order::STATUS_CANCELLED, $cancelledOrder->status);

        Event::assertDispatched(OrderStatusUpdated::class);
    }

    public function test_cannot_cancel_delivered_order()
    {
        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'status' => Order::STATUS_DELIVERED,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Order cannot be cancelled in current status: delivered");

        $this->orderService->cancelOrder($order);
    }

    public function test_get_allowed_next_statuses()
    {
        $nextStatuses = $this->orderService->getAllowedNextStatuses(Order::STATUS_PENDING);

        $this->assertContains(Order::STATUS_ACCEPTED, $nextStatuses);
        $this->assertContains(Order::STATUS_CANCELLED, $nextStatuses);
        $this->assertCount(2, $nextStatuses);
    }

    public function test_full_shipping_order_lifecycle()
    {
        Event::fake();

        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'fulfilment_type' => 'shipping',
            'status' => Order::STATUS_PENDING,
        ]);

        // pending -> accepted
        $order = $this->orderService->updateOrderStatus($order, Order::STATUS_ACCEPTED);
        $this->assertEquals(Order::STATUS_ACCEPTED, $order->status);

        // accepted -> in_progress
        $order = $this->orderService->updateOrderStatus($order, Order::STATUS_IN_PROGRESS);
        $this->assertEquals(Order::STATUS_IN_PROGRESS, $order->status);

        // in_progress -> ready
        $order = $this->orderService->updateOrderStatus($order, Order::STATUS_READY);
        $this->assertEquals(Order::STATUS_READY, $order->status);

        // ready -> packing
        $order = $this->orderService->updateOrderStatus($order, Order::STATUS_PACKING);
        $this->assertEquals(Order::STATUS_PACKING, $order->status);

        // packing -> shipped
        $order = $this->orderService->updateOrderStatus($order, Order::STATUS_SHIPPED);
        $this->assertEquals(Order::STATUS_SHIPPED, $order->status);

        // shipped -> delivered
        $order = $this->orderService->updateOrderStatus($order, Order::STATUS_DELIVERED);
        $this->assertEquals(Order::STATUS_DELIVERED, $order->status);

        Event::assertDispatchedTimes(OrderStatusUpdated::class, 6);
    }

    public function test_full_pickup_order_lifecycle()
    {
        Event::fake();

        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'fulfilment_type' => 'pickup',
            'status' => Order::STATUS_PENDING,
        ]);

        // pending -> accepted -> in_progress -> ready -> ready_for_pickup -> picked_up
        $order = $this->orderService->updateOrderStatus($order, Order::STATUS_ACCEPTED);
        $order = $this->orderService->updateOrderStatus($order, Order::STATUS_IN_PROGRESS);
        $order = $this->orderService->updateOrderStatus($order, Order::STATUS_READY);
        $order = $this->orderService->updateOrderStatus($order, Order::STATUS_READY_FOR_PICKUP);
        $order = $this->orderService->updateOrderStatus($order, Order::STATUS_PICKED_UP);

        $this->assertEquals(Order::STATUS_PICKED_UP, $order->status);

        Event::assertDispatchedTimes(OrderStatusUpdated::class, 5);
    }
}
