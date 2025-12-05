<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderReadyForPickupMail;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReadyForPickupMailTest extends TestCase
{
    use RefreshDatabase;

    protected Merchant $merchant;
    protected Store $store;
    protected Customer $customer;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->merchant = Merchant::factory()->create();

        $this->store = Store::factory()->create([
            'merchant_id' => $this->merchant->id,
            'name' => 'Pickup Store',
            'address_primary' => '123 Main St',
            'address_city' => 'Test City',
            'address_state' => 'TS',
            'address_postcode' => '12345',
        ]);

        $this->customer = Customer::factory()->create([
            'merchant_id' => $this->merchant->id,
            'email' => 'customer@example.com',
            'first_name' => 'Mike',
            'last_name' => 'Johnson',
        ]);

        $pickupTime = now()->addDays(1)->setHour(14)->setMinute(0);

        $this->order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
            'customer_email' => $this->customer->email,
            'public_id' => 'ORD-PICKUP-001',
            'status' => Order::STATUS_READY_FOR_PICKUP,
            'fulfilment_type' => Order::FULFILMENT_PICKUP,
            'pickup_time' => $pickupTime,
            'total_cents' => 1500,
        ]);

        $product = Product::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price_cents' => 1500,
        ]);
    }

    /** @test */
    public function mailable_has_correct_subject()
    {
        $mailable = new OrderReadyForPickupMail($this->order);

        $this->assertEquals(
            'Order Ready for Pickup - Pickup Store',
            $mailable->envelope()->subject
        );
    }

    /** @test */
    public function mailable_renders_successfully()
    {
        $mailable = new OrderReadyForPickupMail($this->order);

        $rendered = $mailable->render();

        $this->assertNotEmpty($rendered);
        $this->assertIsString($rendered);
    }

    /** @test */
    public function mailable_includes_pickup_location()
    {
        $mailable = new OrderReadyForPickupMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('123 Main St', $rendered);
        $this->assertStringContainsString('Test City', $rendered);
    }

    /** @test */
    public function mailable_includes_order_public_id()
    {
        $mailable = new OrderReadyForPickupMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('ORD-PICKUP-001', $rendered);
    }

    /** @test */
    public function mailable_includes_customer_name()
    {
        $mailable = new OrderReadyForPickupMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('Mike', $rendered);
    }

    /** @test */
    public function mailable_loads_necessary_relationships()
    {
        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
        ]);

        $mailable = new OrderReadyForPickupMail($order);

        $this->assertTrue($mailable->order->relationLoaded('store'));
        $this->assertTrue($mailable->order->relationLoaded('items'));
        $this->assertTrue($mailable->order->relationLoaded('customer'));
    }

    /** @test */
    public function mailable_uses_markdown_view()
    {
        $mailable = new OrderReadyForPickupMail($this->order);

        $content = $mailable->content();

        $this->assertEquals('emails.orders.ready-for-pickup', $content->markdown);
    }

    /** @test */
    public function mailable_includes_pickup_time()
    {
        $mailable = new OrderReadyForPickupMail($this->order);

        $rendered = $mailable->render();

        // The pickup time should be formatted and included
        $this->assertStringContainsString($this->order->pickup_time->format('M'), $rendered);
    }
}
