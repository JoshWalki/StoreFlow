<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactFormRequest;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Handle contact form submission.
     *
     * @param ContactFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ContactFormRequest $request)
    {
        try {
            // Send email to hello@storeflow.com.au
            Mail::to(config('mail.from.address'))
                ->send(new ContactFormMail($request->validated()));

            return back()->with('success', 'Your message has been sent successfully!');
        } catch (\Exception $e) {
            \Log::error('Contact form submission failed: ' . $e->getMessage());

            return back()
                ->withErrors(['email' => 'Failed to send message. Please try again later.'])
                ->withInput();
        }
    }
}
