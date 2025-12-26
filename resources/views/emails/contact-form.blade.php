<x-mail::message>
# New Contact Form Submission

You have received a new message through the StoreFlow contact form.

## Contact Information

**Name:** {{ $name }}
**Email:** {{ $email }}
**Submitted:** {{ $submittedAt }}

---

## Message

{{ $message }}

---

<x-mail::panel>
**Quick Actions**

Reply to this message to respond directly to {{ $name }} at {{ $email }}.
</x-mail::panel>

---

<x-mail::subcopy>
This email was sent from the StoreFlow landing page contact form.
IP Address: {{ request()->ip() }}
User Agent: {{ request()->userAgent() }}
</x-mail::subcopy>
</x-mail::message>
