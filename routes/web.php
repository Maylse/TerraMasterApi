<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    Log::info('Home route accessed.');

    try {
        // Attempt to return the welcome view
        Log::info('Attempting to return welcome view.');
        return view('welcome');
    } catch (\Exception $e) {
        // Log any exceptions
        Log::error('Error returning welcome view: ' . $e->getMessage());
        return response()->json(['error' => 'Something went wrong.'], 500);
    }
});
