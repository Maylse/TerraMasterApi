<?php

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IsAdmin; // Import your middleware
use App\Http\Middleware\CorsMiddleware; // Import your CORS middleware
use Illuminate\Support\Facades\Log; // Import the Log facade

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register your middleware here
        $middleware->alias([
            'is_admin' => IsAdmin::class,
            'cors' => CorsMiddleware::class,
        ]);
        
        // Middleware groups
        $middleware->group('api', [
            'throttle:api',
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'bindings',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            Log::error($e->getMessage(), [
                'exception' => $e,
                'url' => $request->url(),
                'method' => $request->method(),
                'request' => $request->all(),
            ]);

            if ($e instanceof NotFoundHttpException && $request->is('api/*')) {
                return response()->json(['message' => 'Record not found.'], 404);
            }

            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        });
    })->create();
