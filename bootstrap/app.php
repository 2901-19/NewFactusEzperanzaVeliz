<?php

use App\Http\Middleware\CheckPermiso;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\RegistrarLanzador;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
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
            'rol' => CheckRole::class,
            'permiso' => CheckPermiso::class,
        ]);

        $middleware->web(append: [
            RegistrarLanzador::class,
        ]);

        $middleware->prependToPriorityList(
            AuthenticatesRequests::class,
            RegistrarLanzador::class,
        );

        $middleware->validateCsrfTokens(except: [
            'lanzador/cerrar-sesion',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
