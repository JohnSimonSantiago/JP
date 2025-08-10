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
public function signUp(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $newUser = new User();
        $newUser->name = $request->name;
        $newUser->password = Hash::make($request->password);
        $newUser->level = 1;
        $newUser->points = 0;
        $newUser->is_premium = false;

        $newUser->save();

        return response()->json(['message' => 'User created successfully']);
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
     * Get user profile with membership data
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
                    'level' => $user->level,
                    'points' => $user->points,
                    'is_premium' => $isPremium,
                    'profile_image' => $user->profile_image,
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
     * Upload profile image
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

            // Store new image
            $file = $request->file('profile_image');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profiles', $filename, 'public');

            // Update user record
            $user->update(['profile_image' => $filename]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image updated successfully',
                'filename' => $filename
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
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'level' => 'sometimes|integer|min:1',
                'points' => 'sometimes|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $user->update($request->only(['name', 'level', 'points']));

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()
            ]);

        } catch (\Exception $e) {
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
}