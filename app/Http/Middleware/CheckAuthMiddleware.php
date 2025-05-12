<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
     $user = auth('sanctum')->user();

if (!$user || 
    $user->currentAccessToken()->expires_at?->isPast()) {
    return response()->error(['message' => 'Unauthorized'], 403);
}


        return $next($request);
    }
}
