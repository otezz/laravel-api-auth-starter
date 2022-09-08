<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSuspended
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
        if (auth()->check() && (auth()->user()->is_suspended)) {
            auth()->user()->tokens()->delete();

            return response(['message' => 'Your Account is suspended, please contact Admin.'], 401);
        }

        return $next($request);
    }
}
