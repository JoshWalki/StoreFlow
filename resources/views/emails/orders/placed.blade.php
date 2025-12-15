<x-mail::message>
# Order Confirmation

Thank you for your order, {{ $order->customer_name }}!

We've received your order **#{{ $order->public_id }}** and it's being processed.

## Order Details

**Order Number:** {{ $order->public_id }}
**Order Date:** {{ $order->placed_at->format('F j, Y \a\t g:i A') }}
**Fulfilment Type:** {{ ucfirst($order->fulfilment_type) }}

@if($order->fulfilment_type === 'pickup' && $order->pickup_time)
**Pickup Time:** {{ $order->pickup_time->format('F j, Y \a\t g:i A') }}
@endif

@if($order->fulfilment_type === 'shipping')
**Shipping Address:**
{{ $order->shipping_name }}
{{ $order->shipping_line1 }}
@if($order->shipping_line2)
{{ $order->shipping_line2 }}
@endif
{{ $order->city }}, {{ $order->state }} {{ $order->postcode }}
{{ $order->country }}
@endif

## Order Items

<x-mail::table>
| Item | Quantity | Price | Total |
|:-----|:--------:|------:|------:|
@foreach($items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | ${{ number_format($item->unit_price_cents / 100, 2) }} | ${{ number_format($item->total_cents / 100, 2) }} |
@endforeach
</x-mail::table>

## Order Summary

**Subtotal:** ${{ number_format($subtotal, 2) }}
@if($discount > 0)
**Discount:** -${{ number_format($discount, 2) }}
@endif
@if($tax > 0)
**Tax:** ${{ number_format($tax, 2) }}
@endif
@if($shipping > 0)
**Shipping:** ${{ number_format($shipping, 2) }}
@endif
**Total:** ${{ number_format($total, 2) }}

**Payment Status:** {{ ucfirst($order->payment_status) }}
@if($order->payment_method)
**Payment Method:** {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}
@endif

---

@if($store->contact_email || $store->contact_phone)
## Need Help?

@if($store->contact_email)
Email: {{ $store->contact_email }}
@endif
@if($store->contact_phone)
Phone: {{ $store->contact_phone }}
@endif
@endif

Thank you for shopping with **{{ $store->name }}**!

Best regards,
{{ $store->name }} Team
</x-mail::message>
