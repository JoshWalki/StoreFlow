<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderPlacedMail;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPlacedMailTest extends TestCase
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
            'contact_email' => 'store@example.com',
        ]);

        $this->customer = Customer::factory()->create([
            'merchant_id' => $this->merchant->id,
            'email' => 'customer@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
            'customer_email' => $this->customer->email,
            'public_id' => 'ORD-TEST-001',
            'total_cents' => 2500,
            'items_total_cents' => 2000,
            'shipping_cost_cents' => 500,
        ]);

        $product = Product::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'name' => 'Test Product',
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'qty' => 2,
            'unit_price_cents' => 1000,
        ]);
    }

    /** @test */
    public function mailable_has_correct_subject()
    {
        $mailable = new OrderPlacedMail($this->order);

        $this->assertEquals(
            'Order Confirmation - Test Store',
            $mailable->envelope()->subject
        );
    }

    /** @test */
    public function mailable_renders_successfully()
    {
        $mailable = new OrderPlacedMail($this->order);

        $rendered = $mailable->render();

        $this->assertNotEmpty($rendered);
        $this->assertIsString($rendered);
    }

    /** @test */
    public function mailable_contains_order_information()
    {
        $mailable = new OrderPlacedMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('ORD-TEST-001', $rendered);
        $this->assertStringContainsString('Test Store', $rendered);
    }

    /** @test */
    public function mailable_contains_customer_name()
    {
        $mailable = new OrderPlacedMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('John', $rendered);
    }

    /** @test */
    public function mailable_includes_order_items()
    {
        $mailable = new OrderPlacedMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('Test Product', $rendered);
    }

    /** @test */
    public function mailable_calculates_prices_correctly()
    {
        $mailable = new OrderPlacedMail($this->order);

        $content = $mailable->content();
        $data = $content->with;

        $this->assertEquals(20.00, $data['subtotal']);
        $this->assertEquals(5.00, $data['shipping']);
        $this->assertEquals(25.00, $data['total']);
    }

    /** @test */
    public function mailable_includes_store_contact_information()
    {
        $mailable = new OrderPlacedMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('store@example.com', $rendered);
    }

    /** @test */
    public function mailable_loads_necessary_relationships()
    {
        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
        ]);

        $mailable = new OrderPlacedMail($order);

        $this->assertTrue($mailable->order->relationLoaded('store'));
        $this->assertTrue($mailable->order->relationLoaded('items'));
        $this->assertTrue($mailable->order->relationLoaded('customer'));
    }

    /** @test */
    public function mailable_uses_markdown_view()
    {
        $mailable = new OrderPlacedMail($this->order);

        $content = $mailable->content();

        $this->assertEquals('emails.orders.placed', $content->markdown);
    }

    /** @test */
    public function mailable_has_no_attachments()
    {
        $mailable = new OrderPlacedMail($this->order);

        $this->assertEmpty($mailable->attachments());
    }
}
