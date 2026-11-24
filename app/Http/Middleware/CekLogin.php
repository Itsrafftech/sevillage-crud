<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekLogin
{
    /**
     * Memastikan pengguna sudah login (ada sesi id_karyawan) sebelum
     * mengakses halaman internal.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('id_karyawan')) {
            return redirect()->route('internal.login')
                ->with('pesan_error', 'Silakan login terlebih dahulu.');
        }

        return $next($request);
    }
}
