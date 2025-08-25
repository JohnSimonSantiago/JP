<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class UserController extends Controller
{

    public function profile(Request $request)
{
    try {
        // Get the currently authenticated user (based on the bearer token)
        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Get membership if it exists
        $membership = $user->memberships()->latest()->first();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'bio' => $user->bio,
                'level' => $user->level,
                'stars' => $user->stars,
                'points' => $user->points,
                'cash' => $user->cash,
                'is_premium' => $user->is_premium,
                'profile_image' => $user->profile_image,
                'gender' => $user->gender,
                'birthday' => $user->birthday,
                'address' => $user->address,
                'privacy_settings' => $user->privacy_settings,
                'role' => $user->role,
                'created_at' => $user->created_at,  // Add this line
                'member_since' => $user->created_at  // Add this line as alias
            ],
            'membership' => $membership
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch profile',
            'error' => $e->getMessage()
        ], 500);
    }
}
    
    /**
     * Get current authenticated user data - NEW METHOD FOR LEADERBOARD
     */
    public function getCurrentUser(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated',
                    'user' => null
                ], 401);
            }

            $user = Auth::user();

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'bio' => $user->bio, // ADDED: Include bio
                    'email' => $user->email ?? null,
                    'role' => $user->role ?? 'user',
                    'level' => $user->level ?? 1,
                    'stars' => $user->stars ?? $user->points ?? 100,
                    'points' => $user->points ?? $user->stars ?? 100,
                    'is_premium' => $user->is_premium ?? false,
                    'profile_image' => $user->profile_image,
                    'gender' => $user->gender,
                    'birthday' => $user->birthday,
                    'address' => $user->address,
                    'created_at' => $user->created_at,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch current user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user data'
            ], 500);
        }
    }

    public function signUp(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.unique' => 'This username is already taken.',
            'name.required' => 'Username is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        try {
            $newUser = new User();
            $newUser->name = $request->name;
            $newUser->password = Hash::make($request->password);
            $newUser->level = 1;
            $newUser->stars = 100; // FIXED: Use stars instead of points
            $newUser->points = 100; // Keep both for compatibility
            $newUser->is_premium = false;
            $newUser->role = 'user'; // Set default role

            $newUser->save();

            return response()->json([
                'message' => 'User created successfully',
                'user' => [
                    'id' => $newUser->id,
                    'name' => $newUser->name,
                    'level' => $newUser->level,
                    'stars' => $newUser->stars,
                    'points' => $newUser->points,
                    'is_premium' => $newUser->is_premium,
                    'role' => $newUser->role,
                ]
            ], 201);
                
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create user',
                'error' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function validatePassword(Request $request)
    {
        // Get the user ID from the reservation
        $reservation = Reservation::find($request->ID);
        if (!$reservation) {
            return response()->json(['valid' => false, 'message' => 'Reservation not found!'], 404);
        }
    
        // Fetch the user based on the reservation's user_id
        $user = User::find($reservation->user_id);
        if (!$user) {
            return response()->json(['valid' => false, 'message' => 'User not found!'], 404);
        }
    
        // Check if the provided password matches the stored password
        if (Hash::check($request->password, $user->password)) {
            return response()->json(['valid' => true]);
        }
    
        return response()->json(['valid' => false]);
    }
    
    /**
     * Get user profile with membership data - UPDATED
     */
public function getProfile()
{
    try {
        $user = Auth::user();
        
        // Get the most recent active membership
        $membership = Membership::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Update is_premium status based on membership
        $isPremium = false;
        if ($membership && $membership->status === 'approved') {
            $now = Carbon::now();
            $endDate = Carbon::parse($membership->end_date);
            
            if ($endDate->isAfter($now)) {
                $isPremium = true;
            } else {
                // Membership expired, update status
                $membership->update(['status' => 'expired']);
            }
        }

        // Update user's is_premium status
        if ($user->is_premium !== $isPremium) {
            $user->update(['is_premium' => $isPremium]);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'bio' => $user->bio,
                'level' => $user->level,
                'stars' => $user->stars ?? $user->points ?? 100,
                'points' => $user->points ?? $user->stars ?? 100,
                'cash' => $user->cash ?? 0.00,
                'is_premium' => $isPremium,
                'profile_image' => $user->profile_image,
                'gender' => $user->gender,
                'birthday' => $user->birthday,
                'address' => $user->address,
                'privacy_settings' => $user->privacy_settings,
                'role' => $user->role ?? 'user',
                'created_at' => $user->created_at,        // ADD THIS LINE
                'member_since' => $user->created_at       // ADD THIS LINE
            ],
            'membership' => $membership ? [
                'id' => $membership->id,
                'type' => $membership->type,
                'status' => $membership->status,
                'start_date' => $membership->start_date,
                'end_date' => $membership->end_date,
                'created_at' => $membership->created_at,
            ] : null
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch profile data',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Upload profile image - FIXED VERSION
     */
    public function uploadProfileImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            // Delete old profile image if exists
            if ($user->profile_image) {
                Storage::disk('public')->delete('profiles/' . $user->profile_image);
            }

            // Store new image with proper extension handling
            $file = $request->file('profile_image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . $user->id . '.' . $extension;
            
            // Store the file
            $file->storeAs('profiles', $filename, 'public');

            // Update user record with the complete filename (including extension)
            $user->update(['profile_image' => $filename]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image updated successfully',
                'filename' => $filename,
                'debug_info' => [
                    'original_name' => $file->getClientOriginalName(),
                    'extension' => $extension,
                    'stored_as' => $filename,
                    'storage_path' => 'profiles/' . $filename
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile - UPDATED WITH PRIVACY SETTINGS AND BETTER VALIDATION
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Build validation rules
            $rules = [
                'bio' => 'sometimes|nullable|string|max:500', // ADDED: Bio validation
                'level' => 'sometimes|integer|min:1',
                'stars' => 'sometimes|integer|min:0',
                'points' => 'sometimes|integer|min:0',
                'gender' => 'sometimes|nullable|in:male,female,other,prefer_not_to_say',
                'birthday' => 'sometimes|nullable|date|before:today',
                'address' => 'sometimes|nullable|string|max:1000',
                'privacy_settings' => 'sometimes|array',
                'privacy_settings.bio' => 'sometimes|in:public,private', // ADDED: Bio privacy
                'privacy_settings.gender' => 'sometimes|in:public,private',
                'privacy_settings.birthday' => 'sometimes|in:public,private', 
                'privacy_settings.address' => 'sometimes|in:public,private',
            ];

            // Add name validation with unique check (excluding current user)
            if ($request->has('name')) {
                $rules['name'] = 'required|string|max:255|unique:users,name,' . $user->id;
            }

            $validator = Validator::make($request->all(), $rules, [
                'name.unique' => 'This username is already taken by another user. Please choose a different one.',
                'name.required' => 'Username is required.',
                'name.max' => 'Username cannot be longer than 255 characters.',
                'bio.max' => 'Bio cannot be longer than 500 characters.', // ADDED: Bio error message
                'birthday.before' => 'Birthday must be in the past.',
                'gender.in' => 'Please select a valid gender option.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Log the data being updated (for debugging)
            \Log::info('Updating user profile', [
                'user_id' => $user->id,
                'data' => $request->only(['name', 'bio', 'level', 'stars', 'points', 'gender', 'birthday', 'address', 'privacy_settings'])
            ]);

            // Update the user - ADDED bio to the fillable fields
            $user->update($request->only(['name', 'bio', 'level', 'stars', 'points', 'gender', 'birthday', 'address', 'privacy_settings']));

            // Refresh the user from database to get updated values
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'bio' => $user->bio, // ADDED: Include bio in response
                    'level' => $user->level,
                    'stars' => $user->stars,
                    'points' => $user->points,
                    'is_premium' => $user->is_premium,
                    'profile_image' => $user->profile_image,
                    'gender' => $user->gender,
                    'birthday' => $user->birthday,
                    'address' => $user->address,
                    'privacy_settings' => $user->privacy_settings,
                    'role' => $user->role,
                    'created_at' => $user->created_at,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Profile update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get membership history
     */
    public function getMembershipHistory()
    {
        try {
            $user = Auth::user();
            
            $memberships = Membership::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'memberships' => $memberships
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch membership history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new membership application
     */
    public function createMembership(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:monthly,annual',
                'start_date' => 'required|date|after_or_equal:today',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            // Check if user already has a pending or approved membership
            $existingMembership = Membership::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('end_date', '>', Carbon::now())
                ->first();

            if ($existingMembership) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have an active or pending membership application'
                ], 400);
            }

            // Calculate end date based on type
            $startDate = Carbon::parse($request->start_date);
            $endDate = $request->type === 'monthly' 
                ? $startDate->copy()->addMonth()
                : $startDate->copy()->addYear();

            $membership = Membership::create([
                'user_id' => $user->id,
                'type' => $request->type,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Membership application submitted successfully',
                'membership' => $membership
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create membership application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leaderboard - UPDATED for consistency
     */
     public function getLeaderboard()
    {
        try {
            // Get all users ordered by stars (descending), then by level (descending)
            $users = User::orderBy('stars', 'desc')
                        ->orderBy('level', 'desc')
                        ->orderBy('created_at', 'asc') // Earlier users win ties
                        ->select(['id', 'name', 'bio', 'level', 'stars', 'is_premium', 'profile_image', 'gender', 'birthday']) // ADDED bio
                        ->get();

            return response()->json([
                'success' => true,
                'users' => $users,
                'total_users' => $users->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leaderboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users by name - UPDATED
     */
     public function searchUsers(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'users' => []
                ]);
            }

            $users = User::where('name', 'LIKE', '%' . $query . '%')
                        ->select(['id', 'name', 'bio', 'level', 'stars', 'is_premium', 'profile_image', 'gender', 'birthday']) // ADDED bio
                        ->limit(10)
                        ->get();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate user age from birthday - Helper method
     */
    public function calculateAge($birthday)
    {
        if (!$birthday) return null;
        
        $birthDate = Carbon::parse($birthday);
        return $birthDate->age;
    }

    /**
     * Get user statistics with age calculation - NEW METHOD
     */
      public function getUserStats(Request $request)
    {
        try {
            $user = Auth::user();
            
            $stats = [
                'id' => $user->id,
                'name' => $user->name,
                'bio' => $user->bio, // ADDED: Include bio
                'level' => $user->level,
                'stars' => $user->stars,
                'points' => $user->points,
                'is_premium' => $user->is_premium,
                'profile_image' => $user->profile_image,
                'gender' => $user->gender,
                'birthday' => $user->birthday,
                'age' => $this->calculateAge($user->birthday),
                'address' => $user->address,
                'role' => $user->role,
                'account_age_days' => Carbon::parse($user->created_at)->diffInDays(Carbon::now()),
                'created_at' => $user->created_at,
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get public profile of any user by ID - UPDATED WITH PRIVACY SETTINGS
     */
     public function getPublicProfile($userId)
    {
        try {
            $user = User::find($userId);
            $currentUser = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Get the most recent active membership (for premium status)
            $membership = Membership::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where('end_date', '>', Carbon::now())
                ->orderBy('created_at', 'desc')
                ->first();

            // Get privacy settings with defaults
            $privacySettings = $user->privacy_settings ?? [
                'bio' => 'public',
                'gender' => 'public',
                'birthday' => 'public', 
                'address' => 'private'
            ];

            // Build public profile respecting privacy settings
            $publicProfile = [
                'id' => $user->id,
                'name' => $user->name,
                'level' => $user->level,
                'stars' => $user->stars,
                'is_premium' => $membership ? true : false,
                'profile_image' => $user->profile_image,
                'role' => $user->role,
                'member_since' => $user->created_at,
            ];

            // Add bio if public or if viewing own profile
            if (($privacySettings['bio'] ?? 'public') === 'public' || ($currentUser && $currentUser->id === $user->id)) {
                $publicProfile['bio'] = $user->bio;
            }

            // Add gender if public or if viewing own profile
            if (($privacySettings['gender'] ?? 'public') === 'public' || ($currentUser && $currentUser->id === $user->id)) {
                $publicProfile['gender'] = $user->gender;
            }

            // Add birthday/age if public or if viewing own profile
            if (($privacySettings['birthday'] ?? 'public') === 'public' || ($currentUser && $currentUser->id === $user->id)) {
                $publicProfile['birthday'] = $user->birthday;
                if ($user->birthday) {
                    $publicProfile['age'] = $this->calculateAge($user->birthday);
                }
            }

            // Add address if public or if viewing own profile
            if (($privacySettings['address'] ?? 'private') === 'public' || ($currentUser && $currentUser->id === $user->id)) {
                $publicProfile['address'] = $user->address;
            }

            return response()->json([
                'success' => true,
                'user' => $publicProfile,
                'membership' => $membership ? [
                    'type' => $membership->type,
                    'status' => $membership->status,
                    'start_date' => $membership->start_date,
                    'end_date' => $membership->end_date,
                ] : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}