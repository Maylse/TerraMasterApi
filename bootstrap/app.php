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
            'cors' => CorsMiddleware::class, // Register the CORS middleware
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle NotFoundHttpException
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Record not found.'
                ], 404);
            }
        });

        // Log other exceptions for further debugging
        $exceptions->render(function (\Exception $e, Request $request) {
            // Log the exception details
            Log::error('Exception occurred: ' . $e->getMessage(), [
                'request' => $request->all(),
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'message' => 'An internal error occurred.',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        });
    })
    ->create()
    ->middleware('cors'); // Apply the CORS middleware globally
