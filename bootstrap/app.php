<?php

use App\Http\Middleware\AjaxFormResponses;
use App\Http\Middleware\RolAcceso;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias para proteger rutas por rol:  ->middleware('rol:administrador,coordinador')
        $middleware->alias([
            'rol' => RolAcceso::class,
        ]);
 
        // Formularios sin recarga (respuestas JSON para peticiones AJAX)
        $middleware->web(append: [
            AjaxFormResponses::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
