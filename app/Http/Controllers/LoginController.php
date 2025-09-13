<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        if (User::where("name", $request->name)->exists()) {
            $user = User::where("name", $request->name)->first();
            
            if (Hash::check($request->password, $user->password)) {
                
                // Check if user is approved - NEW APPROVAL CHECK
                if (!$user->is_approved) {
                    return response()->json([
                        'message' => 'Your account is pending approval. Please contact an administrator.',
                        'type' => 'approval_pending'
                    ], 403);
                }
                
                // For API authentication, create a token
                $token = $user->createToken('auth-token')->plainTextToken;
                
                // Also log in for web routes if needed
                Auth::login($user);
                
                $role = $user->role;
                return response()->json([
                    'message' => 'Login successful', 
                    'role' => $role,
                    'token' => $token,  // Include token for API calls
                    'user' => $user
                ], 200);
            } else {
                return response()->json(['message' => 'Invalid password'], 401);
            }
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }

    public function checkLogin()
    {
        // For API routes, check if user is authenticated via Sanctum
        if (request()->expectsJson()) {
            return response()->json([
                'authenticated' => Auth::guard('sanctum')->check(),
                'user' => Auth::guard('sanctum')->user()
            ]);
        }
        
        // For web routes, use session authentication
        return Auth::check();
    }

   public function logout(Request $request)
{
    // For API, revoke current token
    if (request()->expectsJson() && Auth::guard('sanctum')->check()) {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }
    }
    
    // For web, logout from session
    Auth::logout();
    
    return response()->json(['message' => 'Logged out successfully']);
}
}