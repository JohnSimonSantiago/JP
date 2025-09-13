<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{
    /**
     * Get all users for admin management
     */
    public function index()
    {
        try {
            $users = User::select([
                'id', 'name', 'email', 'role', 'is_approved', 
                'created_at', 'profile_image', 'level', 'points', 'stars'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'users' => $users,
                'stats' => [
                    'total' => $users->count(),
                    'pending' => $users->where('is_approved', false)->count(),
                    'approved' => $users->where('is_approved', true)->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching users for admin: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users'
            ], 500);
        }
    }

    /**
     * Approve a user
     */
    public function approve(User $user)
    {
        try {
            if ($user->is_approved) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already approved'
                ], 400);
            }

            $user->update(['is_approved' => true]);

            Log::info("User {$user->name} (ID: {$user->id}) approved by admin " . auth()->user()->name);

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} has been approved",
                'user' => $user->fresh(['id', 'name', 'email', 'role', 'is_approved', 'created_at', 'profile_image'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve user'
            ], 500);
        }
    }

    /**
     * Revoke user approval
     */
    public function revoke(User $user)
    {
        try {
            if (!$user->is_approved) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not currently approved'
                ], 400);
            }

            // Prevent revoking admin users
            if ($user->role === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot revoke approval for admin users'
                ], 403);
            }

            $user->update(['is_approved' => false]);

            // Revoke all tokens for the user so they're immediately logged out
            $user->tokens()->delete();

            Log::info("User {$user->name} (ID: {$user->id}) approval revoked by admin " . auth()->user()->name);

            return response()->json([
                'success' => true,
                'message' => "Approval revoked for {$user->name}",
                'user' => $user->fresh(['id', 'name', 'email', 'role', 'is_approved', 'created_at', 'profile_image'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error revoking user approval: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke approval'
            ], 500);
        }
    }

    /**
     * Bulk approve multiple users
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            $userIds = $request->user_ids;
            
            $updatedCount = User::whereIn('id', $userIds)
                ->where('is_approved', false)
                ->update(['is_approved' => true]);

            $users = User::whereIn('id', $userIds)->get(['id', 'name']);
            $userNames = $users->pluck('name')->join(', ');

            Log::info("Bulk approval of {$updatedCount} users by admin " . auth()->user()->name . ": {$userNames}");

            return response()->json([
                'success' => true,
                'message' => "Approved {$updatedCount} users",
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk approval: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk approve users'
            ], 500);
        }
    }

    /**
     * Get user details for admin review
     */
    public function show(User $user)
    {
        try {
            $user->load(['trades', 'bets', 'purchases']);

            return response()->json([
                'success' => true,
                'user' => $user,
                'stats' => [
                    'trades_count' => $user->trades->count(),
                    'bets_count' => $user->bets->count(),
                    'purchases_count' => $user->purchases->count(),
                    'account_age_days' => $user->created_at->diffInDays(now()),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching user details: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user details'
            ], 500);
        }
    }
}