<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;

class CheckBanned
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

        if (auth('sanctum')->check() && auth('sanctum')->user()->banned) {
            $user = auth('sanctum')->user();
            $user=User::findOrFail($user->id);
            $banHistory = $user?->histories()->latest()->firstOrFail();

            if ($banHistory && Carbon::now()->lessThan($banHistory->end_date)) {
				$start_date = Carbon::parse($banHistory->start_date);
				$end_date = Carbon::parse($banHistory->end_date);				
                $banned_days = $start_date->diffInDays($end_date);
                // Auth::logout();
                auth('sanctum')->user()->currentAccessToken()->delete();

                if ($banned_days > 14) {
                    $message = 'Your account has been suspended. Please contact the administrator.';
                } else {
                    $message = 'Your account has been suspended for ' . $banned_days . ' ' . Str::plural('day', $banned_days) . '. Please contact the administrator.';
                }

                return response()->json(["message" => $message]);
            }
        } else {
            return $next($request);
        }
    }
}
