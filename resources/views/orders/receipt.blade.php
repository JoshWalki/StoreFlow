<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $order->public_id }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        /* Screen preview: mimic 80mm width */
        body {
            margin: 0;
            padding: 0;
            background: #f0f0f0;
            display: flex;
            justify-content: center;
        }

        .receipt {
            width: 80mm;
            background: #ffffff;
            padding: 8px 6px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 11px;
            color: #000;
        }

        .branding {
            font-size: 8px;
            color: #666;
            margin-bottom: 4px;
        }

        .receipt-header,
        .receipt-footer {
            text-align: center;
            margin-bottom: 8px;
        }

        .store-name {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .store-details {
            font-size: 10px;
            line-height: 1.3;
        }

        .section-title {
            font-weight: 600;
            margin-top: 6px;
            margin-bottom: 4px;
            border-bottom: 1px dashed #000;
            padding-bottom: 2px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
        }

        .items-section {
            margin-top: 6px;
            margin-bottom: 6px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px dotted #ccc;
        }

        .item-row.header {
            font-weight: 600;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 2px;
        }

        .item-name {
            flex: 1;
            font-size: 12px;
            font-weight: 600;
            color: #000;
            padding-right: 8px;
        }

        .item-qty {
            width: 40px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #000;
        }

        .addon-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0 2px 8px;
            font-size: 10px;
            color: #000;
            background: #f5f5f5;
            margin: 1px 0;
            font-weight: 500;
        }

        .addon-name {
            flex: 1;
            padding-right: 4px;
        }

        .addon-name::before {
            content: "→ ";
            font-weight: 700;
        }

        .addon-qty {
            width: 40px;
            text-align: center;
        }

        .totals {
            margin-top: 4px;
            font-size: 11px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
        }

        .totals-row.total {
            font-weight: 700;
            margin-top: 2px;
            border-top: 1px solid #000;
            padding-top: 3px;
        }

        .footer-text {
            font-size: 9px;
            line-height: 1.3;
            margin-top: 6px;
        }

        .pickup-bar {
            background: #000 !important;
            color: #fff !important;
            text-align: center;
            padding: 8px 0;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 8px;
            border: 3px solid #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color-adjust: exact;
        }

        /* Print styling */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            html, body {
                margin: 0;
                padding: 0;
                background: #ffffff;
            }

            .receipt {
                margin: 0;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }

            .pickup-bar {
                background: #000 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .addon-row {
                background: #f5f5f5 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            div[style*="background: #fffbea"] {
                background: #fffbea !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>

    <script>
        // Auto-open print dialog when loaded
        window.addEventListener('load', function () {
            setTimeout(() => window.print(), 300);
        });
    </script>

</head>
<body>
<div class="receipt">
    {{-- StoreFlow Branding --}}
    <div class="branding">StoreFlow</div>

    {{-- PICKUP BAR (only for pickup orders) --}}
    @if($order->fulfilment_type === 'pickup')
        <div class="pickup-bar">PICKUP</div>
    @endif

    {{-- ORDER META --}}
    <div class="section-title">Kitchen Order</div>
    <div class="meta-row">
        <span>Order #</span>
        <span>{{ $order->public_id }}</span>
    </div>
    <div class="meta-row">
        <span>Date</span>
        <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
    </div>

    @if($order->customer)
        <div class="meta-row">
            <span>Customer</span>
            <span style="font-weight: 700;">{{ $order->customer->first_name }} {{ $order->customer->last_name }}</span>
        </div>
    @endif

    {{-- ITEMS --}}
    <div class="items-section">
        {{-- Header --}}
        <div class="item-row header">
            <div class="item-name">Item</div>
            <div class="item-qty">Qty</div>
        </div>

        {{-- Items --}}
        @foreach($order->items as $item)
            <div class="item-row">
                <div class="item-name">{{ $item->name }}</div>
                <div class="item-qty">{{ $item->quantity }}</div>
            </div>

            {{-- Display special instructions --}}
            @if($item->special_instructions)
                <div style="padding: 2px 0 2px 8px; font-size: 10px; color: #666; font-style: italic; background: #fffbea; margin: 1px 0;">
                    Note: {{ $item->special_instructions }}
                </div>
            @endif

            {{-- Display addons as sub-items --}}
            @if($item->addons && $item->addons->isNotEmpty())
                @foreach($item->addons as $addon)
                    <div class="addon-row">
                        <div class="addon-name">{{ $addon->name }}</div>
                        <div class="addon-qty">{{ $addon->quantity }}</div>
                    </div>
                @endforeach
            @endif
        @endforeach
    </div>

    {{-- TOTALS --}}
    <div class="totals">
        <div class="totals-row">
            <span>Subtotal</span>
            <span>${{ number_format($order->items_total_cents / 100, 2) }}</span>
        </div>

        @if($order->tax_cents > 0)
            <div class="totals-row">
                <span>Tax</span>
                <span>${{ number_format($order->tax_cents / 100, 2) }}</span>
            </div>
        @endif

        @if($order->discount_cents > 0)
            <div class="totals-row">
                <span>Discount</span>
                <span>-${{ number_format($order->discount_cents / 100, 2) }}</span>
            </div>
        @endif

        @if($order->shipping_cost_cents > 0)
            <div class="totals-row">
                <span>Shipping</span>
                <span>${{ number_format($order->shipping_cost_cents / 100, 2) }}</span>
            </div>
        @endif

        <div class="totals-row total">
            <span>Total</span>
            <span>${{ number_format($order->total_cents / 100, 2) }}</span>
        </div>

        @if(!empty($order->payment_method))
            <div class="totals-row">
                <span>Paid via</span>
                <span>{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</span>
            </div>
        @endif
    </div>

    {{-- FOOTER --}}
    <div class="receipt-footer">
        <div class="footer-text">
            Thank you for your business!
            <br><br>
            Keep this receipt as proof of purchase.
        </div>
    </div>

    {{-- STORE INFO (moved to bottom) --}}
    <div class="receipt-footer" style="border-top: 1px dashed #000; padding-top: 6px; margin-top: 6px;">
        <div class="store-name">
            {{ $store->name ?? 'Store Name' }}
        </div>
        <div class="store-details">
            @if(!empty($store->address_primary))
                {{ $store->address_primary }}<br>
            @endif
            @if(!empty($store->address_city))
                {{ $store->address_city }}@if($store->address_state), {{ $store->address_state }}@endif @if($store->address_postcode) {{ $store->address_postcode }}@endif<br>
            @endif
            @if(!empty($store->contact_phone))
                Ph: {{ $store->contact_phone }}<br>
            @endif
            @if(!empty($merchant->abn))
                ABN: {{ $merchant->abn }}
            @endif
        </div>
    </div>

    {{-- Optional debug/helper link for screen only --}}
    <div class="no-print" style="text-align:center; margin-top: 8px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Print Again
        </button>
    </div>

</div>
</body>
</html>
