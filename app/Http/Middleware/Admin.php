<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Http\Request;

class Admin
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
        if (Auth::user()->role !== 'ADMIN' && Auth::user()->role !== 'GADMIN' && Auth::user()->role !== 'CADMIN' && Auth::user()->role !== 'EADMIN')
        {
            return response(json_encode(['error' => 'Unauthorised']), 401)
            ->header('Content-Type', 'text/json');
        }

        return $next($request);
    }
}
