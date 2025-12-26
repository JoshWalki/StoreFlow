<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Email from ' . ($store->name ?? config('app.name')))</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }

        .email-wrapper {
            background-color: #f3f4f6;
            padding: 40px 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 15px;
        }

        .logo-image {
            max-width: 200px;
            max-height: 80px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .store-name {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .header-subtitle {
            font-size: 16px;
            color: rgba(255, 255, 255, 0.9);
            margin: 10px 0 0 0;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 24px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 20px 0;
        }

        .message {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        .info-card {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .info-card h3 {
            margin: 0 0 15px 0;
            color: #667eea;
            font-size: 18px;
            font-weight: 600;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }

        .info-value {
            color: #111827;
            font-size: 14px;
            text-align: right;
        }

        .product-item {
            display: flex;
            gap: 15px;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: box-shadow 0.2s;
        }

        .product-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            background-color: #f3f4f6;
            flex-shrink: 0;
        }

        .product-details {
            flex: 1;
            min-width: 0;
        }

        .product-name {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin: 0 0 5px 0;
        }

        .product-meta {
            font-size: 14px;
            color: #6b7280;
            margin: 3px 0;
        }

        .product-addon {
            font-size: 13px;
            color: #9ca3af;
            margin: 2px 0;
            padding-left: 15px;
        }

        .product-price {
            text-align: right;
            font-weight: 600;
            color: #111827;
            white-space: nowrap;
        }

        .order-summary {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 25px;
            border-radius: 8px;
            margin-top: 30px;
        }

        .order-summary h3 {
            margin: 0 0 20px 0;
            color: #92400e;
            font-size: 18px;
            font-weight: 600;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 15px;
        }

        .summary-label {
            color: #78350f;
        }

        .summary-value {
            font-weight: 600;
            color: #78350f;
        }

        .summary-total {
            border-top: 2px solid #d97706;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 18px;
            font-weight: 700;
        }

        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .button:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15);
        }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-ready_for_pickup {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-shipped {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-in_progress {
            background-color: #fef3c7;
            color: #92400e;
        }

        .footer {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .footer-links {
            margin: 20px 0;
        }

        .footer-link {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }

        .footer-text {
            color: #6b7280;
            font-size: 13px;
            margin: 8px 0;
        }

        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e5e7eb, transparent);
            margin: 30px 0;
        }

        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 20px 10px;
            }

            .content {
                padding: 30px 20px;
            }

            .header {
                padding: 30px 20px;
            }

            .product-item {
                flex-direction: column;
            }

            .product-image {
                width: 100%;
                height: 150px;
            }

            .product-price {
                text-align: left;
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="header">
                @if(isset($store) && $store->logo_url)
                <div class="logo-container">
                    <img src="{{ $store->logo_url }}" alt="{{ $store->name }}" class="logo-image">
                </div>
                @endif
                <h1 class="store-name">{{ $store->name ?? config('app.name') }}</h1>
                @if(isset($headerSubtitle))
                <p class="header-subtitle">{{ $headerSubtitle }}</p>
                @endif
            </div>

            <div class="content">
                @yield('content')
            </div>

            <div class="footer">
                @if(isset($store) && ($store->contact_email || $store->contact_phone))
                <div class="footer-links">
                    @if($store->contact_email)
                    <a href="mailto:{{ $store->contact_email }}" class="footer-link">Contact Us</a>
                    @endif
                    @if($store->storefront_url)
                    <a href="{{ $store->storefront_url }}" class="footer-link">Visit Store</a>
                    @endif
                </div>
                @endif

                @if(isset($store))
                    @if($store->contact_email)
                    <p class="footer-text">{{ $store->contact_email }}</p>
                    @endif
                    @if($store->contact_phone)
                    <p class="footer-text">{{ $store->contact_phone }}</p>
                    @endif
                @endif

                <p class="footer-text">&copy; {{ date('Y') }} {{ $store->name ?? config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>

</html>
