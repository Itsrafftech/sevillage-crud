<?php

use App\Http\Middleware\CekLogin;
use App\Http\Middleware\CekPeran;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'login' => CekLogin::class,
            'peran' => CekPeran::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            $pesan = \App\Services\PesanErrorService::dariException($e);

            if ($request->expectsJson()) {
                return response()->json(['pesan_error' => $pesan], 422);
            }

            return back()->withInput()->with('pesan_error', $pesan);
        });
    })->create();
