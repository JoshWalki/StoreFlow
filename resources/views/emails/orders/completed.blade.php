<x-mail::message>
# Thank You for Your Order!

Hi {{ $order->customer_name }},

We hope you're enjoying your purchase! Your order **#{{ $order->public_id }}** has been completed.

## Order Details

**Order Number:** {{ $order->public_id }}
**Order Date:** {{ $order->placed_at->format('F j, Y') }}
@if($order->completed_at)
**Completed Date:** {{ $order->completed_at->format('F j, Y') }}
@endif
**Fulfilment Type:** {{ ucfirst($order->fulfilment_type) }}

## What You Ordered

<x-mail::table>
| Item | Quantity | Price |
|:-----|:--------:|------:|
@foreach($items as $item)
| {{ $item->product->name }} | {{ $item->qty }} | ${{ number_format(($item->qty * $item->unit_price_cents) / 100, 2) }} |
@endforeach
</x-mail::table>

**Order Total:** ${{ number_format($total, 2) }}

---

## We Value Your Feedback

Your satisfaction is important to us. If you have any feedback about your order or experience, we'd love to hear from you.

@if($store->contact_email || $store->contact_phone)
## Need Support?

If you have any questions or concerns about your order, please contact us:

@if($store->contact_email)
Email: {{ $store->contact_email }}
@endif
@if($store->contact_phone)
Phone: {{ $store->contact_phone }}
@endif
@endif

---

Thank you for choosing **{{ $store->name }}**. We hope to serve you again soon!

Best regards,
{{ $store->name }} Team
</x-mail::message>
