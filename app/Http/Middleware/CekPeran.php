<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekPeran
{
    /**
     * Membatasi route hanya untuk peran tertentu, mis. peran:fo,fin
     */
    public function handle(Request $request, Closure $next, string ...$peranDiizinkan): Response
    {
        $peran = session('peran');

        if (! $peran || ! in_array($peran, $peranDiizinkan, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
