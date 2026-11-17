
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

        // Evita redirecciones a una ruta login inexistente.
        $middleware->redirectGuestsTo(
            fn (Request $request) =>
                $request->is('api/*') ? null : route('login')
        );

        // Middleware administrativo.
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        // Las peticiones API deben responder con JSON.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, \Throwable $exception) =>
                $request->is('api/*') || $request->expectsJson()
        );

    })->create();
