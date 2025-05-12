<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use App\Enums\Roles;
use Illuminate\Support\Facades\Log;

class CheckIfEmployee
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

	public function handle(Request $request, Closure $next)
		{
			$employee = auth('api-employees')->user();
		
			if (!$employee) {
					return response()->json([
						'status' => 'error',
						'message' => 'You do not have the permission.',
					], 401);
				}

				return $next($request);
			}
}
