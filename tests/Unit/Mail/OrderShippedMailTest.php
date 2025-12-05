<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderShippedMail;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderShippedMailTest extends TestCase
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
            'name' => 'Test Store',
        ]);

        $this->customer = Customer::factory()->create([
            'merchant_id' => $this->merchant->id,
            'email' => 'customer@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        $this->order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
            'customer_email' => $this->customer->email,
            'public_id' => 'ORD-SHIP-001',
            'status' => Order::STATUS_SHIPPED,
            'fulfilment_type' => Order::FULFILMENT_SHIPPING,
            'tracking_code' => 'TRACK123456',
            'tracking_url' => 'https://tracking.example.com/TRACK123456',
            'total_cents' => 3000,
        ]);

        $product = Product::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price_cents' => 3000,
        ]);
    }

    /** @test */
    public function mailable_has_correct_subject()
    {
        $mailable = new OrderShippedMail($this->order);

        $this->assertEquals(
            'Order Shipped - Test Store',
            $mailable->envelope()->subject
        );
    }

    /** @test */
    public function mailable_renders_successfully()
    {
        $mailable = new OrderShippedMail($this->order);

        $rendered = $mailable->render();

        $this->assertNotEmpty($rendered);
        $this->assertIsString($rendered);
    }

    /** @test */
    public function mailable_includes_tracking_code()
    {
        $mailable = new OrderShippedMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('TRACK123456', $rendered);
    }

    /** @test */
    public function mailable_includes_tracking_url()
    {
        $mailable = new OrderShippedMail($this->order);

        $content = $mailable->content();
        $data = $content->with;

        $this->assertEquals('https://tracking.example.com/TRACK123456', $data['trackingUrl']);
    }

    /** @test */
    public function mailable_contains_order_public_id()
    {
        $mailable = new OrderShippedMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('ORD-SHIP-001', $rendered);
    }

    /** @test */
    public function mailable_includes_customer_name()
    {
        $mailable = new OrderShippedMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('Jane', $rendered);
    }

    /** @test */
    public function mailable_loads_necessary_relationships()
    {
        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
        ]);

        $mailable = new OrderShippedMail($order);

        $this->assertTrue($mailable->order->relationLoaded('store'));
        $this->assertTrue($mailable->order->relationLoaded('items'));
        $this->assertTrue($mailable->order->relationLoaded('customer'));
    }

    /** @test */
    public function mailable_uses_markdown_view()
    {
        $mailable = new OrderShippedMail($this->order);

        $content = $mailable->content();

        $this->assertEquals('emails.orders.shipped', $content->markdown);
    }

    /** @test */
    public function mailable_calculates_total_correctly()
    {
        $mailable = new OrderShippedMail($this->order);

        $content = $mailable->content();
        $data = $content->with;

        $this->assertEquals(30.00, $data['total']);
    }
}
