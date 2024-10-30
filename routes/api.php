<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Group routes with CORS middleware
Route::middleware(['cors'])->group(function () {
    // Group API routes
    Route::middleware('api')->group(function () {
        
        // Test MongoDB connection
        Route::get('/test-mongo', function() {
            try {
                $mongoUri = env('DB_URI');
                $mongoDatabase = env('DB_DATABASE');
                
                if (is_null($mongoDatabase)) {
                    return response()->json(['error' => 'Database name is null. Check your environment variables.'], 500);
                }

                $mongoClient = new \MongoDB\Client($mongoUri);
                $database = $mongoClient->selectDatabase($mongoDatabase);
                $database->command(['ping' => 1]);
                
                return response()->json(['message' => 'MongoDB connection successful.']);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        });

        // Simple test endpoint
        Route::get('/test', function () {
            return response()->json(['message' => 'This is a test endpoint.']);
        });
        
        // Authentication routes
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        
        // Protected user routes
        Route::middleware(['auth:sanctum'])->group(function() {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/profile', [AuthController::class, 'profile']);
            Route::put('/profile/update', [AuthController::class, 'updateProfile']);
            Route::get('/dashboard', [DashboardController::class, 'index']);
    
            // Finder routes
            Route::get('/experts', [UsersController::class, 'getExperts']);
            Route::get('/surveyors', [UsersController::class, 'getSurveyors']);  
            
            // Consultation requests
            Route::get('/consultation/getAllRequests', [ConsultationController::class, 'getFinderRequests']);
            Route::post('/request-consultation/expert', [ConsultationController::class, 'requestExpertConsultation']);
            Route::post('/request-consultation/surveyor', [ConsultationController::class, 'requestSurveyorConsultation']);
            Route::put('/consultation/updateRequest/{id}', [ConsultationController::class, 'updateRequest']);
            Route::delete('/consultation/deleteRequest/{id}', [ConsultationController::class, 'deleteRequest']);
    
            // Expert routes
            Route::get('/consultation/requests/expert', [ConsultationController::class, 'getExpertConsultationRequests']);
            
            // Surveyor routes
            Route::get('/consultation/requests/surveyor', [ConsultationController::class, 'getSurveyorConsultationRequests']);
            
            // Both expert and surveyor routes
            Route::post('/consultation/accept/{id}', [ConsultationController::class, 'acceptRequest']);
            Route::post('/consultation/decline/{id}', [ConsultationController::class, 'declineRequest']);
        });
        
        // Admin routes
        Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
            Route::get('/admin/getAllUsers', [AdminController::class, 'getAllUsers']);
            Route::get('/admin/dashboard', [AdminController::class, 'index']);
            Route::get('/admin/getAllUpdates', [AdminController::class, 'getAllUpdates']);
            Route::post('/admin/postUpdate', [AdminController::class, 'postUpdate']);
            Route::put('/admin/editUpdate/{id}', [AdminController::class, 'editUpdate']);
            Route::delete('/admin/deleteUpdate/{id}', [AdminController::class, 'deleteUpdate']);
            Route::delete('/admin/deleteUser/{id}', [AdminController::class, 'terminateUser']);
        });
        
        // Retrieve authenticated user
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->middleware('auth:sanctum');
    });
});
