<x-mail::message>
# Your Order Has Shipped!

Good news, {{ $order->customer_name }}!

Your order **#{{ $order->public_id }}** has been shipped and is on its way to you.

## Shipping Details

**Order Number:** {{ $order->public_id }}
**Shipping Method:** {{ $order->shipping_method ? ucfirst(str_replace('_', ' ', $order->shipping_method)) : 'Standard Shipping' }}

@if($trackingCode)
**Tracking Number:** {{ $trackingCode }}
@endif

@if($trackingUrl)
<x-mail::button :url="$trackingUrl">
Track Your Package
</x-mail::button>
@endif

**Shipping Address:**
{{ $order->shipping_name }}
{{ $order->shipping_line1 }}
@if($order->shipping_line2)
{{ $order->shipping_line2 }}
@endif
{{ $order->city }}, {{ $order->state }} {{ $order->postcode }}
{{ $order->country }}

## Order Summary

<x-mail::table>
| Item | Quantity | Price |
|:-----|:--------:|------:|
@foreach($items as $item)
| {{ $item->product->name }} | {{ $item->qty }} | ${{ number_format(($item->qty * $item->unit_price_cents) / 100, 2) }} |
@endforeach
</x-mail::table>

**Order Total:** ${{ number_format($total, 2) }}

---

## Delivery Information

Your package should arrive within the estimated delivery time frame provided by the carrier.
@if($trackingUrl)
You can track your shipment in real-time using the tracking link above.
@endif

@if($store->contact_email || $store->contact_phone)

## Questions?

If you have any questions about your shipment, please don't hesitate to contact us:

@if($store->contact_email)
Email: {{ $store->contact_email }}
@endif
@if($store->contact_phone)
Phone: {{ $store->contact_phone }}
@endif
@endif

Thank you for your order!

Best regards,
{{ $store->name }} Team
</x-mail::message>
