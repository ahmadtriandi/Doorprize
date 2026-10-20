<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->get('doorprize_admin')) {
            return $next($request);
        }

        if ($request->session()->get('doorprize_operator')) {
            return redirect()->route('operator.home')
                ->with('error', 'Akun operator tidak punya akses ke halaman itu.');
        }

        return redirect()->route('login')->with('error', 'Silakan masuk terlebih dahulu.');
    }
}
