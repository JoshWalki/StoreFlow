<?php

namespace App\Mail;

use App\Models\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionPurchasedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Merchant $merchant
    ) {
        // Load owner relationship for recipient email
        $this->merchant->load('owner');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscription Activated - Welcome to StoreFlow!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $cost = '$29.00'; // Default - will be replaced with actual pricing
        $billingInterval = 'month';
        $nextBillingDate = $this->merchant->subscription_current_period_end?->format('F j, Y') ?? 'Unknown';

        // Check if in trial
        $isTrialing = $this->merchant->subscription_status === 'trialing';
        $trialEndsAt = $this->merchant->subscription_trial_end?->format('F j, Y') ?? null;
        $trialDaysRemaining = $this->merchant->subscription_trial_end
            ? max(0, now()->diffInDays($this->merchant->subscription_trial_end, false))
            : 0;

        return new Content(
            markdown: 'emails.subscriptions.purchased',
            with: [
                'merchant' => $this->merchant,
                'merchantName' => $this->merchant->name,
                'cost' => $cost,
                'billingInterval' => $billingInterval,
                'nextBillingDate' => $nextBillingDate,
                'isTrialing' => $isTrialing,
                'trialEndsAt' => $trialEndsAt,
                'trialDaysRemaining' => $trialDaysRemaining,
                'dashboardUrl' => route('dashboard'),
                'billingUrl' => $this->merchant->stores->first()
                    ? route('store.settings', ['store' => $this->merchant->stores->first()->id]) . '#subscription'
                    : route('dashboard'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
