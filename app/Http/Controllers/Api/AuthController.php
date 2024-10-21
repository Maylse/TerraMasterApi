<?php

namespace App\Http\Controllers\Api;

use App\Models\User; 
use App\Models\Surveyor; 
use App\Models\Finder; 
use App\Http\Controllers\Controller;
use App\Models\LandExpert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Functions for Login, Register, Profile, and Logout

 public function login(Request $request): JsonResponse{
    // Validate the incoming request data
    $request->validate([
        'email' => 'required|email|max:255',
        'password' => 'required|string|min:8|max:255',
    ]);

    // Attempt to find the user by email
    $user = User::where('email', $request->email)->first();

    // Check if the user exists and the password is correct
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'The provided credentials are incorrect'
        ], 401);
    }

    // Generate a token for the authenticated user
    $token = $user->createToken($user->name . ' Auth-Token')->plainTextToken;

    // Return success response with token and user details
    return response()->json([
        'message' => 'Login Successful',
        'token_type' => 'Bearer',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'user_type' => $user->user_type,
            // You can include additional info here if needed
        ]
    ], 200);
}

    public function register(Request $request): JsonResponse
    {
        try {
            // Validate the request input, including user_type
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'password' => 'required|string|min:8|max:255',
                'user_type' => 'required|in:finder,expert,surveyor', // Allow all user types
                'certification_id' => 'required_if:user_type,surveyor,expert|string|unique:land_experts,certification_id|unique:surveyors,certification_id', // Required for surveyor and expert
                'license_number' => 'required_if:user_type,surveyor,expert|string|unique:land_experts,license_number|unique:surveyors,license_number', // Required for surveyor and expert
                'pricing' => 'required_if:user_type,surveyor,expert|numeric', // Required for surveyor and expert
            ]);
            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => $request->user_type,
            ]);
    
            // Create expert or surveyor based on user type
            if ($user->user_type === 'expert') {
                // Create an expert account
                LandExpert::create([
                    'user_id' => $user->id,
                    'license_number' => $request->license_number,
                    'certification_id' => $request->certification_id,
                    'pricing' => $request->pricing,
                ]);
            } elseif ($user->user_type === 'surveyor') {
                Log::info('Creating Surveyor:', [
                    'user_id' => $user->id,
                    'certification_id' => $request->certification_id,
                    'license_number' => $request->license_number,
                    'pricing' => $request->pricing,
                ]);
                // Create a surveyor account
                Surveyor::create([
                    'user_id' => $user->id,
                    'certification_id' => $request->certification_id,
                    'license_number' => $request->license_number,
                    'pricing' => $request->pricing,
                ]);
            } elseif ($user->user_type === 'finder') {
                Log::info('Creating Finder:', [
                    'user_id' => $user->id,
                    'name' => $request->name,
                ]);
                // Create a finder account
                Finder::create([
                    'user_id' => $user->id,
                    'name' => $request->name, // Save the name in the finders table
                ]);
            }
            // Generate token for the new user
            $token = $user->createToken($user->name . ' Auth-Token')->plainTextToken;
            return response()->json([
                'message' => 'Registration Successful',
                'token_type' => 'Bearer',
                'token' => $token,
                'user' => $user,
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors
            return response()->json([
                'message' => 'Database error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            // Handle general errors
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function profile(Request $request)
    {
        if ($request->user()) {
            return response()->json([
                'message' => 'Profile Fetched.',
                'data' => $request->user()
            ], 200);
        } else {
            return response()->json([
                'message' => 'Not authenticated.',
            ], 401);
        }
    }
    public function updateProfile(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id . '|max:255',
            'password' => 'nullable|string|min:8|max:255|confirmed', // Include confirmed field for password confirmation
        ]);
    
        // Get the authenticated user
        $user = $request->user();
    
        // Update the user's profile information
        $user->update([
            'name' => $request->name ?? $user->name, // Update name if provided
            'email' => $request->email ?? $user->email, // Update email if provided
            'password' => $request->password ? Hash::make($request->password) : $user->password, // Update password if provided
        ]);
    
        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ], 200);
    }


    public function logout(Request $request)
    {
        // Retrieve the authenticated user
        $user = $request->user(); // This gets the currently authenticated user
    
        // Check if the user exists
        if ($user) {
            // Delete all tokens for the user to log them out
            $user->tokens()->delete();
            
            return response()->json([
                'message' => 'Logged out successfully.'
            ], 200);
        } else {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }
    }
}
