<x-mail::message>
# Your Order is Ready for Pickup!

Great news, {{ $order->customer_name }}!

Your order **#{{ $order->public_id }}** is ready and waiting for you.

## Pickup Details

**Order Number:** {{ $order->public_id }}
@if($order->pickup_time)
**Scheduled Pickup Time:** {{ $order->pickup_time->format('F j, Y \a\t g:i A') }}
@endif

**Pickup Location:**
{{ $store->name }}
@if($store->address_primary)
{{ $store->address_primary }}
@if($store->address_city && $store->address_state)
{{ $store->address_city }}, {{ $store->address_state }} {{ $store->address_postcode }}
@endif
@endif

@if($order->pickup_notes)
**Pickup Notes:**
{{ $order->pickup_notes }}
@endif

## Your Order

<x-mail::table>
| Item | Quantity | Price |
|:-----|:--------:|------:|
@foreach($items as $item)
| {{ $item->product->name }} | {{ $item->qty }} | ${{ number_format(($item->qty * $item->unit_price_cents) / 100, 2) }} |
@endforeach
</x-mail::table>

**Order Total:** ${{ number_format($total, 2) }}

---

## What to Bring

- This email or your order number: **{{ $order->public_id }}**
- A valid photo ID
@if($order->payment_status === 'unpaid')
- Payment (if not already completed)
@endif

@if($store->contact_phone)

If you need to reschedule or have any questions, please contact us at {{ $store->contact_phone }}.
@endif

We look forward to seeing you soon!

Best regards,
{{ $store->name }} Team
</x-mail::message>
