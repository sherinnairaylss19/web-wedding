<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PelangganAuth
{
    public function handle(Request $request, Closure $next): Response
{
    if (!Auth::guard('pelanggan')->check()) {
        if ($request->isMethod('get')) {
            $request->session()->put('pelanggan.intended', $request->fullUrl());
        }

        return redirect()->route('pelanggan.login')
            ->with('info', 'Silakan login terlebih dahulu untuk mengirim testimoni.');
    }

    return $next($request);
}
}