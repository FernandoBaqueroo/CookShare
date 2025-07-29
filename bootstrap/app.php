<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.token.expiration' => \App\Http\Middleware\CheckTokenExpiration::class,
            'auth' => \App\Http\Middleware\Authenticate::class,
            'api.auth' => \App\Http\Middleware\ApiAuthentication::class,
        ]);
        
        // Remover middleware de sesión y CSRF de las rutas API
        $middleware->web(remove: [
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
        ]);
        
        // Configurar Sanctum solo para rutas que lo necesiten
        $middleware->api([
            // Removemos EnsureFrontendRequestsAreStateful para evitar CSRF en APIs
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
