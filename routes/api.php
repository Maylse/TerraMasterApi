<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ConsultationController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//ENTRY POINTS
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

//USERS ROUTES
Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile/update', [AuthController::class, 'updateProfile']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    //FOR FINDERS
    Route::get('/experts', [UsersController::class, 'getExperts']);
    Route::get('/surveyors', [UsersController::class, 'getSurveyors']);  
        //CONSULTATION REQUEST
        Route::get('/consultation/getAllRequests', [ConsultationController::class, 'getFinderRequests']);
        Route::post('/request-consultation/expert', [ConsultationController::class, 'requestExpertConsultation']);
        Route::post('/request-consultation/surveyor', [ConsultationController::class, 'requestSurveyorConsultation']);
        Route::put('/consultation/updateRequest/{id}', [ConsultationController::class, 'updateRequest']);
        Route::delete('/consultation/deleteRequest/{id}', [ConsultationController::class, 'deleteRequest']);

    //FOR EXPERTS
    Route::get('/consultation/requests/expert', [ConsultationController::class, 'getExpertConsultationRequests']);
    //FOR SURVEYORS
    Route::get('/consultation/requests/surveyor', [ConsultationController::class, 'getSurveyorConsultationRequests']);
    //FOR BOTH EXPERTS AND SURVEYORS
    Route::post('/consultation/accept/{id}', [ConsultationController::class, 'acceptRequest']);
    Route::post('/consultation/decline/{id}', [ConsultationController::class, 'declineRequest']);
});

//ADMIN ROUTES
Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::get('/admin/getAllUsers', [AdminController::class, 'getAllUsers']); // Add this line
    Route::get('/admin/dashboard', [AdminController::class, 'index']);
    Route::get('/admin/getAllUpdates', [AdminController::class, 'getAllUpdates']);
    Route::post('/admin/postUpdate', [AdminController::class, 'postUpdate']);
    Route::delete('/admin/deleteUpdate/{id}', [AdminController::class, 'deleteUpdate']);
    //TERMINATE A USER
    Route::delete('/admin/deleteUser/{id}', [AdminController::class, 'terminateUser']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
