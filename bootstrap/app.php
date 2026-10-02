<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Bahasa & mata uang storefront (panel admin Filament punya stack middleware sendiri).
        $middleware->web(append: [
            \App\Http\Middleware\SetPreferensiToko::class,
        ]);
        // Global (termasuk panel admin Filament): dev/demo tidak boleh masuk Google.
        $middleware->append(\App\Http\Middleware\LarangIndeks::class);

        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->validateCsrfTokens(except: ['webhook/*']);
        $middleware->redirectUsersTo(fn () => route('akun'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
