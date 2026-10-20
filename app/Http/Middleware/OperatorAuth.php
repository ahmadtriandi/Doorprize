<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Mengizinkan operator maupun panitia. */
class OperatorAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->get('doorprize_admin') || $request->session()->get('doorprize_operator')) {
            return $next($request);
        }

        return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
    }
}
