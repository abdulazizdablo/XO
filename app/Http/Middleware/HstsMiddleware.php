<?php

namespace App\Http\Middleware;

use Closure;

class HstsMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // HSTS configuration
        $hstsMaxAge = config('app.hsts_max_age', 31536000); // 1 year in seconds
        $includeSubDomains = config('app.hsts_include_subdomains', true);
        $preload = config('app.hsts_preload', false);

        $hstsHeaderValue = "max-age=$hstsMaxAge";
        if ($includeSubDomains) {
            $hstsHeaderValue .= "; includeSubDomains";
        }
        if ($preload) {
            $hstsHeaderValue .= "; preload";
        }
        $response->header('Strict-Transport-Security', $hstsHeaderValue);

        // X-Frame-Options configuration
        $xFrameOptionsValue = config('app.x_frame_options', 'SAMEORIGIN');
        $response->header('X-Frame-Options', $xFrameOptionsValue);

        return $response;
    }
}
