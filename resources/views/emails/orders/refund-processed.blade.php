@extends('emails.customer-layout')

@section('content')
<h2 class="greeting">Hi {{ $order->customer_name }},</h2>

<p class="message">
    We've processed a refund for an item from your order <strong>#{{ $order->public_id }}</strong>.
</p>

<div class="info-card" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-left-color: #f59e0b;">
    <h3 style="color: #92400e;">Refund Details</h3>
    <div class="info-row" style="border-bottom: 1px solid #fde68a;">
        <span class="info-label" style="color: #78350f;">Refund Date</span>
        <span class="info-value" style="color: #78350f;">{{ $refundedItem->refund_date->format('F j, Y g:i A') }}</span>
    </div>
    <div class="info-row" style="border-bottom: 1px solid #fde68a;">
        <span class="info-label" style="color: #78350f;">Refund Reference</span>
        <span class="info-value" style="color: #78350f; font-weight: 700;">{{ $refundId }}</span>
    </div>
    <div class="info-row" style="border-bottom: none;">
        <span class="info-label" style="color: #78350f;">Refund Amount</span>
        <span class="info-value" style="color: #78350f; font-weight: 700; font-size: 18px;">${{ number_format($refundAmount, 2) }}</span>
    </div>
</div>

<div class="divider"></div>

<h3 style="font-size: 20px; font-weight: 600; color: #111827; margin: 0 0 20px 0;">Refunded Item</h3>

<div class="product-item">
    @php
        $productImage = null;
        if ($refundedItem->product) {
            if ($refundedItem->product->primaryImage) {
                $productImage = $refundedItem->product->primaryImage->image_path;
            } elseif ($refundedItem->product->image_path) {
                $productImage = $refundedItem->product->image_path;
            } elseif ($refundedItem->product->images && $refundedItem->product->images->isNotEmpty()) {
                $productImage = $refundedItem->product->images->first()->image_path;
            }
        }

        if ($productImage && !str_starts_with($productImage, 'http')) {
            $productImage = url($productImage);
        }
    @endphp

    @if($productImage)
    <img src="{{ $productImage }}" alt="{{ $refundedItem->name ?? $refundedItem->product_name }}" class="product-image">
    @else
    <div class="product-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);">
        <svg style="width: 40px; height: 40px; color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
    </div>
    @endif

    <div class="product-details">
        <h4 class="product-name">{{ $refundedItem->name ?? $refundedItem->product_name }}</h4>
        <p class="product-meta">Quantity: {{ $refundedItem->quantity }}</p>

        @if($refundedItem->addons && $refundedItem->addons->isNotEmpty())
        <div style="margin-top: 10px;">
            <p class="product-meta" style="font-weight: 600; margin-bottom: 5px;">Add-ons:</p>
            @foreach($refundedItem->addons as $addon)
            <p class="product-addon">
                + {{ $addon->name }} @if($addon->quantity > 1)(×{{ $addon->quantity }})@endif @if($addon->total_price_cents > 0)(+${{ number_format($addon->total_price_cents / 100, 2) }})@endif
            </p>
            @endforeach
        </div>
        @endif

        <p class="product-meta" style="color: #dc2626; margin-top: 10px;">
            <strong>Reason:</strong> {{ $refundReason }}
        </p>
    </div>

    <div class="product-price">
        <div style="font-size: 18px; color: #dc2626; font-weight: 700;">-${{ number_format($refundAmount, 2) }}</div>
    </div>
</div>

<div class="divider"></div>

<div class="info-card" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border-left-color: #dc2626;">
    <h3 style="color: #991b1b;">Important Information</h3>
    <p style="margin: 10px 0 0 0; color: #7f1d1d; line-height: 1.6;">
        Your refund of <strong>${{ number_format($refundAmount, 2) }}</strong> has been processed and sent back to your original payment method.
    </p>
    <p style="margin: 10px 0 0 0; color: #7f1d1d; line-height: 1.6;">
        <strong>Please note:</strong> It may take <strong>5-10 business days</strong> for the refund to appear in your account, depending on your bank or card issuer's processing time.
    </p>
</div>

<div class="divider"></div>

<p class="message" style="text-align: center;">
    We apologize for any inconvenience. If there's anything we can do to improve your experience, please let us know.
</p>

<p style="text-align: center; color: #6b7280; font-size: 14px; margin-top: 20px;">
    <strong>Reference Number:</strong> {{ $refundId }}
</p>
@endsection
