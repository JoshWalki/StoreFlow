<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderCompletedMail;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCompletedMailTest extends TestCase
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
            'name' => 'Completed Store',
        ]);

        $this->customer = Customer::factory()->create([
            'merchant_id' => $this->merchant->id,
            'email' => 'completed@example.com',
            'first_name' => 'Sarah',
            'last_name' => 'Williams',
        ]);

        $this->order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
            'customer_email' => $this->customer->email,
            'public_id' => 'ORD-COMPLETE-001',
            'status' => Order::STATUS_COMPLETED,
            'total_cents' => 5000,
        ]);

        $product = Product::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
        ]);

        OrderItem::factory()->create([
            'order_id' => $this->order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price_cents' => 5000,
        ]);
    }

    /** @test */
    public function mailable_has_correct_subject()
    {
        $mailable = new OrderCompletedMail($this->order);

        $this->assertEquals(
            'Order Completed - Completed Store',
            $mailable->envelope()->subject
        );
    }

    /** @test */
    public function mailable_renders_successfully()
    {
        $mailable = new OrderCompletedMail($this->order);

        $rendered = $mailable->render();

        $this->assertNotEmpty($rendered);
        $this->assertIsString($rendered);
    }

    /** @test */
    public function mailable_contains_order_public_id()
    {
        $mailable = new OrderCompletedMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('ORD-COMPLETE-001', $rendered);
    }

    /** @test */
    public function mailable_includes_customer_name()
    {
        $mailable = new OrderCompletedMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('Sarah', $rendered);
    }

    /** @test */
    public function mailable_includes_store_name()
    {
        $mailable = new OrderCompletedMail($this->order);

        $rendered = $mailable->render();

        $this->assertStringContainsString('Completed Store', $rendered);
    }

    /** @test */
    public function mailable_loads_necessary_relationships()
    {
        $order = Order::factory()->create([
            'merchant_id' => $this->merchant->id,
            'store_id' => $this->store->id,
            'customer_id' => $this->customer->id,
        ]);

        $mailable = new OrderCompletedMail($order);

        $this->assertTrue($mailable->order->relationLoaded('store'));
        $this->assertTrue($mailable->order->relationLoaded('items'));
        $this->assertTrue($mailable->order->relationLoaded('customer'));
    }

    /** @test */
    public function mailable_uses_markdown_view()
    {
        $mailable = new OrderCompletedMail($this->order);

        $content = $mailable->content();

        $this->assertEquals('emails.orders.completed', $content->markdown);
    }

    /** @test */
    public function mailable_calculates_total_correctly()
    {
        $mailable = new OrderCompletedMail($this->order);

        $content = $mailable->content();
        $data = $content->with;

        $this->assertEquals(50.00, $data['total']);
    }

    /** @test */
    public function mailable_has_no_attachments()
    {
        $mailable = new OrderCompletedMail($this->order);

        $this->assertEmpty($mailable->attachments());
    }
}
