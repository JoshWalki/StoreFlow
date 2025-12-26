<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    protected string $secretKey;
    protected string $verifyUrl;
    protected bool $enabled;

    public function __construct()
    {
        $this->secretKey = config('services.turnstile.secret_key');
        $this->verifyUrl = config('services.turnstile.verify_url');
        $this->enabled = config('services.turnstile.enabled', true);
    }

    /**
     * Verify Turnstile token with CloudFlare
     *
     * @param string|null $token
     * @param string|null $remoteIp
     * @return bool
     */
    public function verify(?string $token, ?string $remoteIp = null): bool
    {
        // Skip verification if disabled (for testing/development)
        if (!$this->enabled) {
            Log::info('Turnstile verification skipped (disabled)');
            return true;
        }

        // Token is required
        if (empty($token)) {
            Log::warning('Turnstile verification failed: No token provided');
            return false;
        }

        try {
            $response = Http::asForm()->post($this->verifyUrl, [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $remoteIp ?? request()->ip(),
            ]);

            $result = $response->json();

            if (!$response->successful() || !isset($result['success'])) {
                Log::error('Turnstile API error', [
                    'status' => $response->status(),
                    'response' => $result,
                ]);
                return false;
            }

            if (!$result['success']) {
                Log::warning('Turnstile verification failed', [
                    'error_codes' => $result['error-codes'] ?? [],
                    'ip' => $remoteIp ?? request()->ip(),
                ]);
            }

            return $result['success'];

        } catch (\Exception $e) {
            Log::error('Turnstile verification exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Fail secure: return false on exceptions
            return false;
        }
    }

    /**
     * Check if Turnstile is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
