<x-mail::message>
# Subscription Activated Successfully!

Hello {{ $merchantName }},

@if($isTrialing)
Great news! Your **{{ $trialDaysRemaining }}-day free trial** has started. You now have full access to all StoreFlow Premium features.

## Trial Information

- **Trial Period:** {{ $trialDaysRemaining }} days remaining
- **Trial Ends:** {{ $trialEndsAt }}
- **After Trial:** Your card will be charged **{{ $cost }}** per {{ $billingInterval }}

@else
Thank you for subscribing to StoreFlow! Your payment has been processed successfully, and your account is now active.

## Subscription Details

- **Plan:** StoreFlow
- **Cost:** **{{ $cost }}** per {{ $billingInterval }}
- **Next Billing Date:** {{ $nextBillingDate }}
- **Status:** Active
@endif

---

## Get Started

<x-mail::button :url="$dashboardUrl">
Go to Dashboard
</x-mail::button>

Start building your online store, adding products, and accepting orders right away!

---

## Manage Your Subscription

You can view your billing history, update payment methods, and manage your subscription at any time:

<x-mail::button :url="$billingUrl">
Manage Billing
</x-mail::button>

---

## Need Help?

Our support team is here to help you succeed:

- **Documentation** - Coming soon!
- **Email Support** - hello@storeflow.com.au

---

@if($isTrialing)
**Important:** Your trial is completely free. You won't be charged until {{ $trialEndsAt }}. You can cancel anytime before then with no charge.
@else
Thank you for choosing StoreFlow! We're excited to help you grow your business.
@endif

Best regards,

**The StoreFlow Team**

<x-mail::subcopy>
**StoreFlow Platform**
Melbourne, Victoria, Australia

Questions? Reply to this email or contact us at hello@storeflow.com.au

You're receiving this email because a subscription was purchased for {{ $merchantName }}.
</x-mail::subcopy>
</x-mail::message>
