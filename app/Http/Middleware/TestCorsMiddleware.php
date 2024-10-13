<?php
namespace App\Http\Middleware;

use Closure;

class TestCorsMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $origin = $request->header('Origin');

        if ($origin) {
            $response->header('Access-Control-Allow-Origin', $origin);
        }

        $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                 ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        return $response;
    }
}
