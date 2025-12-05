<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Email from ' . config('app.name'))</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #4F46E5;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
        }
        .store-name {
            font-size: 20px;
            color: #666;
            margin-top: 5px;
        }
        .content {
            margin-bottom: 30px;
        }
        .order-info {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .order-info h3 {
            margin-top: 0;
            color: #4F46E5;
        }
        .order-items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .order-items th {
            background-color: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .order-items td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .total-row {
            font-weight: bold;
            font-size: 1.1em;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4F46E5;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            margin-top: 30px;
            color: #6b7280;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
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
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
            @if(isset($store))
                <div class="store-name">{{ $store->name }}</div>
            @endif
        </div>

        <div class="content">
            @yield('content')
        </div>

        <div class="footer">
            @if(isset($store) && $store->contact_email)
                <p>Questions? Contact us at <a href="mailto:{{ $store->contact_email }}">{{ $store->contact_email }}</a></p>
            @endif
            @if(isset($store) && $store->contact_phone)
                <p>Phone: {{ $store->contact_phone }}</p>
            @endif
            <p>&copy; {{ date('Y') }} {{ isset($store) ? $store->name : config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
