@extends('emails.layout')

@section('title', 'Order Status Update')

@section('content')
    <h2>Order Status Update</h2>

    <p>Hi {{ $customer->first_name }},</p>

    <p>Your order status has been updated!</p>

    <div class="order-info">
        <h3>Order #{{ $order->public_id }}</h3>
        <p>
            <strong>New Status:</strong>
            <span class="status-badge status-{{ $newStatus }}">
                {{ ucwords(str_replace('_', ' ', $newStatus)) }}
            </span>
        </p>

        @if($newStatus === 'ready_for_pickup')
            <div style="background-color: #dbeafe; padding: 15px; border-radius: 6px; margin: 15px 0;">
                <p style="margin: 0; font-weight: 600; color: #1e40af;">Your order is ready for pickup!</p>
                @if($order->pickup_time)
                    <p style="margin: 10px 0 0 0;">Scheduled pickup time: {{ $order->pickup_time->format('F j, Y g:i A') }}</p>
                @endif
                @if($store->address_primary)
                    <p style="margin: 10px 0 0 0;"><strong>Pickup Location:</strong><br>
                    {{ $store->address_primary }}<br>
                    {{ $store->address_city }}, {{ $store->address_state }} {{ $store->address_postcode }}
                    </p>
                @endif
            </div>
        @elseif($newStatus === 'shipped')
            <div style="background-color: #dbeafe; padding: 15px; border-radius: 6px; margin: 15px 0;">
                <p style="margin: 0; font-weight: 600; color: #1e40af;">Your order has been shipped!</p>
                @if($order->tracking_code)
                    <p style="margin: 10px 0 0 0;"><strong>Tracking Code:</strong> {{ $order->tracking_code }}</p>
                @endif
                @if($order->tracking_url)
                    <p style="margin: 10px 0 0 0;">
                        <a href="{{ $order->tracking_url }}" class="button">Track Your Order</a>
                    </p>
                @endif
                <p style="margin: 10px 0 0 0;"><strong>Shipping Address:</strong><br>
                {{ $order->shipping_name }}<br>
                {{ $order->shipping_line1 }}<br>
                @if($order->shipping_line2)
                    {{ $order->shipping_line2 }}<br>
                @endif
                {{ $order->city }}, {{ $order->state }} {{ $order->postcode }}<br>
                {{ $order->country }}
                </p>
            </div>
        @elseif($newStatus === 'completed')
            <div style="background-color: #d1fae5; padding: 15px; border-radius: 6px; margin: 15px 0;">
                <p style="margin: 0; font-weight: 600; color: #065f46;">Your order is complete!</p>
                <p style="margin: 10px 0 0 0;">Thank you for your business. We hope to serve you again soon!</p>
            </div>
        @elseif($newStatus === 'cancelled')
            <div style="background-color: #fee2e2; padding: 15px; border-radius: 6px; margin: 15px 0;">
                <p style="margin: 0; font-weight: 600; color: #991b1b;">Your order has been cancelled.</p>
                <p style="margin: 10px 0 0 0;">If you have any questions, please contact us.</p>
            </div>
        @endif
    </div>

    <h3>Order Summary</h3>
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
            <tr class="total-row">
                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                <td style="text-align: right;">${{ number_format($order->total_cents / 100, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <p>Thank you for choosing {{ $store->name }}!</p>
@endsection
