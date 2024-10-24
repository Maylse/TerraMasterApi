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
use App\Mail\PasswordResetMail; // Import the mailable class
use Illuminate\Support\Facades\Mail; // Ensure Mail is imported
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Functions for Login, Register, Profile, and Logout

 public function login(Request $request): JsonResponse
 {
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
                'certification_id' => 'required_if:user_type,surveyor,expert|string|unique:land_experts,certification_id|unique:surveyors,certification_id',
                'license_number' => 'required_if:user_type,surveyor,expert|string|unique:land_experts,license_number|unique:surveyors,license_number',
                'pricing' => 'required_if:user_type,surveyor,expert|numeric',
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

// In your AuthController
public function forgotPassword(Request $request): JsonResponse
{
    // Validate the incoming request data
    $request->validate(['email' => 'required|email']);

    // Find the user by email
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'No user found with this email address.'], 404);
    }

    // Create a token
    $token = bin2hex(random_bytes(32));

    // Store the token in the password_resets collection
    PasswordReset::updateOrCreate(
        ['email' => $request->email],
        ['token' => $token, 'created_at' => now()]
    );

    // Send email with only the reset token
    Mail::to($user->email)->send(new PasswordResetMail($token)); // Ensure this mail class is set up

    return response()->json(['message' => 'Password reset token has been sent to your email.'], 200);
}
public function resetPassword(Request $request): JsonResponse
{
    // Validate the incoming request data
    $request->validate([
        'email' => 'required|email',
        'token' => 'required|string',
        'password' => 'required|string|min:8|confirmed', // Include password confirmation
    ]);

    // Check the token in the password_resets collection
    $passwordReset = PasswordReset::where('email', $request->email)
        ->where('token', $request->token)
        ->first();

    if (!$passwordReset) {
        return response()->json(['message' => 'Invalid token or email.'], 400);
    }

    // Update the user's password
    $user = User::where('email', $request->email)->first();
    $user->password = bcrypt($request->password); // Ensure you hash the password
    $user->save();

    // Optionally, delete the token after it has been used
    $passwordReset->delete();

    return response()->json(['message' => 'Password has been successfully reset.'], 200);
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