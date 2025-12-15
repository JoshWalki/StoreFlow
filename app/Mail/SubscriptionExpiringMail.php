<?php

namespace App\Mail;

use App\Models\Merchant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiringMail extends Mailable
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
            subject: 'Subscription Expiring Soon - Action Required',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $expiryDate = $this->merchant->subscription_current_period_end?->format('F j, Y') ?? 'Unknown';
        $daysRemaining = $this->merchant->subscription_current_period_end
            ? max(0, now()->diffInDays($this->merchant->subscription_current_period_end, false))
            : 0;

        return new Content(
            markdown: 'emails.subscriptions.expiring',
            with: [
                'merchant' => $this->merchant,
                'merchantName' => $this->merchant->name,
                'expiryDate' => $expiryDate,
                'daysRemaining' => $daysRemaining,
                'dashboardUrl' => route('dashboard'),
                'billingUrl' => $this->merchant->stores->first()
                    ? route('store.settings', ['store' => $this->merchant->stores->first()->id]) . '#subscription'
                    : route('dashboard'),
                'renewUrl' => route('subscriptions.index'),
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
