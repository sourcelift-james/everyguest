<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasGroup
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()->group) {
            return response('Unauthorized access.', 401);
        }
        return $next($request);
    }
}
