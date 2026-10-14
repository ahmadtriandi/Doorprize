<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->session()->get('doorprize_admin')) {
            return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
        }

        return $next($request);
    }
}
