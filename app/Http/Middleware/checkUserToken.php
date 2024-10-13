<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            // The request is not authenticated, return a response indicating authentication is required.
            return response()->error(['message' => 'Unauthorized.'],  401);
        }

        // The request is authenticated, proceed with the request.
        return $next($request);
    }
}
