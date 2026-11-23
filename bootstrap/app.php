<?php

use App\Http\Middleware\VerificarRol;
use App\Http\Middleware\VerificarUsuarioActivo;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Alias para usar en las rutas: ->middleware('rol:reclutador')
        $middleware->alias([
            'rol' => VerificarRol::class,
        ]);

        // Se ejecuta en todas las páginas web
        $middleware->web(append: [
            VerificarUsuarioActivo::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
