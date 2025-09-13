<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
            // Add debugging
            Log::info('Starting user approval process', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'current_approval_status' => $user->is_approved,
                'admin_user_id' => Auth::guard('sanctum')->id(),
                'admin_user_name' => Auth::guard('sanctum')->user()?->name
            ]);

            if ($user->is_approved) {
                Log::warning('User already approved', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'User is already approved'
                ], 400);
            }

            // Update user approval status
            $updateResult = $user->update(['is_approved' => true]);
            Log::info('User update result', [
                'user_id' => $user->id,
                'update_successful' => $updateResult,
                'new_approval_status' => $user->fresh()->is_approved
            ]);

            // Get admin info safely
            $adminUser = Auth::guard('sanctum')->user();
            $adminName = $adminUser ? $adminUser->name : 'Unknown Admin';
            
            Log::info("User {$user->name} (ID: {$user->id}) approved by admin {$adminName}");

            // Get fresh user data
            $freshUser = $user->fresh(['id', 'name', 'email', 'role', 'is_approved', 'created_at', 'profile_image']);
            
            Log::info('Returning success response', [
                'user_id' => $user->id,
                'fresh_user_approved' => $freshUser->is_approved
            ]);

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} has been approved",
                'user' => $freshUser
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving user: ' . $e->getMessage(), [
                'user_id' => $user->id ?? 'unknown',
                'exception_class' => get_class($e),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve user',
                'debug' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Revoke user approval
     */
    public function revoke(User $user)
    {
        try {
            Log::info('Starting user revoke process', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'current_approval_status' => $user->is_approved
            ]);

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

            $adminUser = Auth::guard('sanctum')->user();
            $adminName = $adminUser ? $adminUser->name : 'Unknown Admin';
            
            Log::info("User {$user->name} (ID: {$user->id}) approval revoked by admin {$adminName}");

            return response()->json([
                'success' => true,
                'message' => "Approval revoked for {$user->name}",
                'user' => $user->fresh(['id', 'name', 'email', 'role', 'is_approved', 'created_at', 'profile_image'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error revoking user approval: ' . $e->getMessage(), [
                'user_id' => $user->id ?? 'unknown',
                'exception_class' => get_class($e),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
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

            $adminUser = Auth::guard('sanctum')->user();
            $adminName = $adminUser ? $adminUser->name : 'Unknown Admin';

            Log::info("Bulk approval of {$updatedCount} users by admin {$adminName}: {$userNames}");

            return response()->json([
                'success' => true,
                'message' => "Approved {$updatedCount} users",
                'updated_count' => $updatedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk approval: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
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
            Log::error('Error fetching user details: ' . $e->getMessage(), [
                'user_id' => $user->id ?? 'unknown',
                'exception_class' => get_class($e),
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user details'
            ], 500);
        }
    }
}