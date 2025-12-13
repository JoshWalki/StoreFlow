<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * The URIs that should be excluded from trimming.
     *
     * @var array<int, string>
     */
    protected $exceptUrls = [
        'api/webhooks/*',
        'webhooks/*',
    ];

    /**
     * Determine if the request has a URI that should be excluded.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldTrimRequest($request): bool
    {
        foreach ($this->exceptUrls as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, $next)
    {
        if (!$this->shouldTrimRequest($request)) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
