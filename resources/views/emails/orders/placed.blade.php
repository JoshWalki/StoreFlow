@extends('emails.customer-layout')

@section('content')
<h2 class="greeting">Thank you for your order, {{ $order->customer_name }}!</h2>

<p class="message">
    We've received your order <strong>#{{ $order->public_id }}</strong> and it's being processed.
    You'll receive another email when your order is ready.
</p>

<div class="info-card">
    <h3>Order Information</h3>
    <div class="info-row">
        <span class="info-label">Order Number</span>
        <span class="info-value">{{ $order->public_id }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Order Date</span>
        <span class="info-value">{{ $order->placed_at->format('F j, Y \a\t g:i A') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Fulfilment Type</span>
        <span class="info-value">{{ ucfirst($order->fulfilment_type) }}</span>
    </div>
    @if($order->fulfilment_type === 'pickup' && $order->pickup_time)
    <div class="info-row">
        <span class="info-label">Requested Pickup Time</span>
        <span class="info-value">{{ $order->pickup_time->format('F j, Y \a\t g:i A') }}</span>
    </div>
    @endif
</div>

@if($order->fulfilment_type === 'shipping')
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
@endif

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

        // Ensure image URL is absolute
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
        <p class="product-meta">Quantity: {{ $item->quantity }} × ${{ number_format($item->unit_price_cents / 100, 2) }}</p>

        @if($item->special_instructions)
        <p class="product-meta" style="color: #3b82f6; font-style: italic;">
            Note: {{ $item->special_instructions }}
        </p>
        @endif

        @if($item->addons && $item->addons->isNotEmpty())
        <div style="margin-top: 10px;">
            <p class="product-meta" style="font-weight: 600; margin-bottom: 5px;">Add-ons:</p>
            @foreach($item->addons as $addon)
            <p class="product-addon">
                + {{ $addon->name }} (×{{ $addon->quantity }}) - ${{ number_format($addon->unit_price_cents / 100, 2) }} each
            </p>
            @endforeach
        </div>
        @endif
    </div>

    <div class="product-price">
        <div style="font-size: 16px;">${{ number_format($item->total_cents / 100, 2) }}</div>
        @if($item->addons && $item->addons->isNotEmpty())
        <div style="font-size: 13px; color: #6b7280; margin-top: 5px;">
            + ${{ number_format($item->addons->sum('total_price_cents') / 100, 2) }} add-ons
        </div>
        <div style="font-size: 16px; font-weight: 700; color: #111827; margin-top: 5px; padding-top: 5px; border-top: 1px solid #e5e7eb;">
            ${{ number_format(($item->total_cents + $item->addons->sum('total_price_cents')) / 100, 2) }}
        </div>
        @endif
    </div>
</div>
@endforeach

<div class="order-summary">
    <h3>Order Summary</h3>

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
        <span class="summary-label">Total</span>
        <span class="summary-value">${{ number_format($total, 2) }}</span>
    </div>
</div>

<div class="info-card" style="margin-top: 30px;">
    <div class="info-row">
        <span class="info-label">Payment Status</span>
        <span class="info-value">{{ ucfirst($order->payment_status) }}</span>
    </div>
    @if($order->payment_method)
    <div class="info-row">
        <span class="info-label">Payment Method</span>
        <span class="info-value">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
    </div>
    @endif
</div>

<div class="divider"></div>

<p class="message" style="text-align: center;">
    If you have any questions about your order, please don't hesitate to contact us.
</p>

<p style="text-align: center; color: #6b7280; font-size: 16px; margin-top: 30px;">
    Thank you for shopping with <strong>{{ $store->name }}</strong>!
</p>
@endsection
