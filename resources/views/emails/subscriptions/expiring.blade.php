<x-mail::message>
    # Your Subscription is Expiring Soon

    Hello {{ $merchantName }},

    This is a friendly reminder that your StoreFlow subscription will expire in **{{ $daysRemaining }} days** on **{{ $expiryDate }}**.

    ---

    ## What Happens When Your Subscription Expires?

    @if($daysRemaining <= 2)
        **Urgent:** Your subscription is expiring very soon. Without an active subscription:
        @else
        When your subscription expires:
        @endif

        - Your stores will be deactivated
        - You won't be able to accept new orders
        - Customers won't be able to access your storefront
        - Your products and data will be preserved

        ---

        ## Keep Your Store Active

        To continue accepting orders and keep your store running, please renew your subscription:

        <x-mail::button :url="$renewUrl">
        Renew Subscription Now
        </x-mail::button>

        ---

        ## Update Your Payment Method

        If your subscription didn't renew due to a payment issue, you can update your payment method:

        <x-mail::button :url="$billingUrl" color="secondary">
            Update Payment Method
        </x-mail::button>

        ---

        ## Subscription Details

        - **Merchant:** {{ $merchantName }}
        - **Current Status:** Expiring
        - **Expiry Date:** {{ $expiryDate }}
        - **Days Remaining:** {{ $daysRemaining }} days

        ---

        ## Need Help?

        If you're experiencing issues with payment or have questions about your subscription:

        - **Email:** hello@storeflow.com.au
        - **Live Chat:** Available in your dashboard
        - **Phone Support:** Priority support for premium members

        We're here to help ensure your business stays online!

        ---

        ## Don't Want to Renew?

        If you no longer wish to use StoreFlow, your subscription will automatically expire on {{ $expiryDate }}. Your store will be deactivated, but your data will be preserved for 30 days in case you decide to return.

        ---

        **Action Required:** Renew your subscription before {{ $expiryDate }} to avoid service interruption.

        Best regards,

        **The StoreFlow Team**

        <x-mail::subcopy>
            **StoreFlow Platform**
            Melbourne, Victoria, Australia

            Questions? Reply to this email or contact us at hello@storeflow.com.au

            You're receiving this email because your subscription for {{ $merchantName }} is expiring soon.
            To stop receiving these reminders, please renew your subscription or let it expire.
        </x-mail::subcopy>
</x-mail::message>