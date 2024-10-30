<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Allow the specific origin
        $response->headers->set('Access-Control-Allow-Origin', 'https://terramasterapi.onrender.com');

        // Specify allowed methods
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');

        // Specify allowed headers
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        // Handle pre-flight requests (OPTIONS)
        if ($request->isMethod('OPTIONS')) {
            return response()->json([], 200);
        }

        return $response;
    }
}
