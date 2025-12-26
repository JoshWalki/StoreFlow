<?php

namespace App\Rules;

use App\Services\TurnstileService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidTurnstile implements ValidationRule
{
    protected TurnstileService $turnstileService;

    public function __construct()
    {
        $this->turnstileService = app(TurnstileService::class);
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->turnstileService->isEnabled()) {
            return;
        }

        if (!$this->turnstileService->verify($value)) {
            $fail('The security verification failed. Please try again.');
        }
    }
}
