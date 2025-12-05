@extends('emails.layout')

@section('title', 'Order Confirmation')

@section('content')
    <h2>Thank You for Your Order!</h2>

    <p>Hi {{ $customer->first_name }},</p>

    <p>We've received your order and will process it shortly. Here are the details:</p>

    <div class="order-info">
        <h3>Order #{{ $order->public_id }}</h3>
        <p><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y g:i A') }}</p>
        <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
        <p><strong>Fulfilment Type:</strong> {{ ucfirst($order->fulfilment_type) }}</p>

        @if($order->fulfilment_type === 'pickup')
            @if($order->pickup_time)
                <p><strong>Pickup Time:</strong> {{ $order->pickup_time->format('F j, Y g:i A') }}</p>
            @endif
            @if($store->address_primary)
                <p><strong>Pickup Location:</strong><br>
                {{ $store->address_primary }}<br>
                {{ $store->address_city }}, {{ $store->address_state }} {{ $store->address_postcode }}
                </p>
            @endif
        @elseif($order->fulfilment_type === 'shipping')
            <p><strong>Shipping Address:</strong><br>
            {{ $order->shipping_name }}<br>
            {{ $order->shipping_line1 }}<br>
            @if($order->shipping_line2)
                {{ $order->shipping_line2 }}<br>
            @endif
            {{ $order->city }}, {{ $order->state }} {{ $order->postcode }}<br>
            {{ $order->country }}
            </p>
        @endif
    </div>

    <h3>Order Items</h3>
    <table class="order-items">
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Price</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Unknown Product' }}</td>
                    <td style="text-align: center;">{{ $item->qty }}</td>
                    <td style="text-align: right;">${{ number_format($item->unit_price_cents / 100, 2) }}</td>
                    <td style="text-align: right;">${{ number_format(($item->qty * $item->unit_price_cents) / 100, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Subtotal:</strong></td>
                <td style="text-align: right;">${{ number_format($order->items_total_cents / 100, 2) }}</td>
            </tr>
            @if($order->shipping_cost_cents > 0)
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Shipping:</strong></td>
                    <td style="text-align: right;">${{ number_format($order->shipping_cost_cents / 100, 2) }}</td>
                </tr>
            @endif
            @if($order->discount_cents > 0)
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Discount:</strong></td>
                    <td style="text-align: right;">-${{ number_format($order->discount_cents / 100, 2) }}</td>
                </tr>
            @endif
            @if($order->tax_cents > 0)
                <tr>
                    <td colspan="3" style="text-align: right;"><strong>Tax:</strong></td>
                    <td style="text-align: right;">${{ number_format($order->tax_cents / 100, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                <td style="text-align: right;">${{ number_format($order->total_cents / 100, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <p>You will receive another email when your order status changes.</p>

    <p>Thank you for shopping with us!</p>
@endsection
