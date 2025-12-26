@extends('emails.customer-layout')

@section('content')
<h2 class="greeting">Good news, {{ $order->customer_name }}!</h2>

<p class="message">
    Your order <strong>#{{ $order->public_id }}</strong> has been shipped and is on its way to you!
</p>

<div class="info-card" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-left-color: #3b82f6;">
    <h3 style="color: #1e40af;">
        <span class="status-badge status-shipped">SHIPPED</span>
    </h3>
    @if($trackingCode)
    <div class="info-row" style="border-bottom: 1px solid #bfdbfe;">
        <span class="info-label" style="color: #1e3a8a;">Tracking Number</span>
        <span class="info-value" style="color: #1e3a8a; font-weight: 700;">{{ $trackingCode }}</span>
    </div>
    @endif
    <div class="info-row" style="border-bottom: none;">
        <span class="info-label" style="color: #1e3a8a;">Shipping Method</span>
        <span class="info-value" style="color: #1e3a8a;">{{ $order->shipping_method ? ucfirst(str_replace('_', ' ', $order->shipping_method)) : 'Standard Shipping' }}</span>
    </div>
</div>

@if($trackingUrl)
<div style="text-align: center; margin: 30px 0;">
    <a href="{{ $trackingUrl }}" class="button">Track Your Package</a>
</div>
@endif

<div class="info-card">
    <h3>Shipping Address</h3>
    <div class="info-value" style="text-align: left;">
        {{ $order->shipping_name }}<br>
        {{ $order->shipping_line1 }}<br>
        @if($order->shipping_line2)
        {{ $order->shipping_line2 }}<br>
        @endif
        {{ $order->city }}, {{ $order->state }} {{ $order->postcode }}<br>
        {{ $order->country }}
    </div>
</div>

<div class="divider"></div>

<h3 style="font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Order Items</h3>

@foreach($items as $item)
<div class="product-item">
    @php
        $productImage = null;
        if ($item->product) {
            if ($item->product->primaryImage) {
                $productImage = $item->product->primaryImage->image_path;
            } elseif ($item->product->image_path) {
                $productImage = $item->product->image_path;
            } elseif ($item->product->images && $item->product->images->isNotEmpty()) {
                $productImage = $item->product->images->first()->image_path;
            }
        }

        if ($productImage && !str_starts_with($productImage, 'http')) {
            $productImage = url($productImage);
        }
    @endphp

    @if($productImage)
    <img src="{{ $productImage }}" alt="{{ $item->product->name }}" class="product-image">
    @else
    <div class="product-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);">
        <svg style="width: 40px; height: 40px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
    </div>
    @endif

    <div class="product-details">
        <h4 class="product-name">{{ $item->product->name }}</h4>
        <p class="product-meta">Quantity: {{ $item->quantity }}</p>

        @if($item->addons && $item->addons->isNotEmpty())
        <div style="margin-top: 10px;">
            <p class="product-meta" style="font-weight: 600; margin-bottom: 5px;">Add-ons:</p>
            @foreach($item->addons as $addon)
            <p class="product-addon">
                + {{ $addon->name }} (×{{ $addon->quantity }})
            </p>
            @endforeach
        </div>
        @endif
    </div>

    <div class="product-price">
        <div style="font-size: 16px;">${{ number_format($item->total_cents / 100, 2) }}</div>
    </div>
</div>
@endforeach

<div class="order-summary">
    <h3>Order Total</h3>

    <div class="summary-row">
        <span class="summary-label">Subtotal</span>
        <span class="summary-value">${{ number_format($subtotal, 2) }}</span>
    </div>

    @if($discount > 0)
    <div class="summary-row" style="color: #059669;">
        <span class="summary-label">Discount</span>
        <span class="summary-value">-${{ number_format($discount, 2) }}</span>
    </div>
    @endif

    @if($tax > 0)
    <div class="summary-row">
        <span class="summary-label">Tax</span>
        <span class="summary-value">${{ number_format($tax, 2) }}</span>
    </div>
    @endif

    @if($shipping > 0)
    <div class="summary-row">
        <span class="summary-label">Shipping</span>
        <span class="summary-value">${{ number_format($shipping, 2) }}</span>
    </div>
    @endif

    <div class="summary-row summary-total">
        <span class="summary-label">Total Paid</span>
        <span class="summary-value">${{ number_format($total, 2) }}</span>
    </div>
</div>

<div class="divider"></div>

<p class="message" style="text-align: center;">
    Your package should arrive within the estimated delivery time provided by the carrier.
    @if($trackingUrl)
    You can track your shipment in real-time using the tracking link above.
    @endif
</p>

<p style="text-align: center; color: #6b7280; font-size: 16px; margin-top: 30px;">
    Thank you for your order!
</p>
@endsection
