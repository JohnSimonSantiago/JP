<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Membership;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MembershipController extends Controller
{
    public function adminIndex()
    {
        $memberships = Membership::with('user:id,name,email,profile_image')
    ->orderBy('created_at', 'desc')
    ->limit(50)
    ->get();

        return response()->json([
            'success' => true,
            'memberships' => $memberships
        ]);
    }

    public function pendingApplications()
    {
        $memberships = Membership::with('user:id,name,email,profile_image')
            ->where('status', 'pending')
            ->where('source', 'applied')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'memberships' => $memberships
        ]);
    }

    public function activeMemberships()
    {
        $memberships = Membership::with('user:id,name,email,profile_image,valid_id')
            ->where('status', 'approved')
            ->where('end_date', '>', now())
            ->orderBy('end_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'memberships' => $memberships
        ]);
    }

    public function history(Request $request)
    {
        $query = Membership::with('user:id,name,email,profile_image')
            ->orderBy('created_at', 'desc');

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $memberships = $query->get();

        // Revenue = paid (applied) approvals only; gifts are free
        $priceMap = ['level_2' => 2500, 'level_3' => 3000];
        $revenue = $memberships
            ->where('status', 'approved')
            ->where('source', 'applied')
            ->sum(fn ($m) => $priceMap[$m->type] ?? 0);

        return response()->json([
            'success' => true,
            'memberships' => $memberships,
            'revenue' => $revenue,
        ]);
    }

    public function approve($id)
    {
        $membership = Membership::with('user')->findOrFail($id);

        if ($membership->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Membership is not pending'], 400);
        }

$start = now();
        $end = $start->copy()->addMonth();

        $membership->update([
            'status' => 'approved',
            'start_date' => $start,
            'end_date' => $end,
        ]);
        $levelMap = ['level_2' => 2, 'level_3' => 3];
        $cashMap  = ['level_2' => 1000, 'level_3' => 1500];
        $membership->user->update([
            'is_premium' => true,
            'level' => $levelMap[$membership->type] ?? 1,
        ]);
        // Paid perk: credit cash on approval (gifts do NOT grant this)
        $membership->user->increment('cash', $cashMap[$membership->type] ?? 0);

        // Notify the user their membership was approved
        try {
            if ($membership->user->push_token) {
                PushNotificationService::send(
                    $membership->user->push_token,
                    'Premium Approved 🎉',
                    "Your {$membership->type} membership is now active. Enjoy!",
                    ['type' => 'membership_approved', 'membership_id' => $membership->id]
                );
            }
        } catch (\Exception $e) {
            \Log::error('Membership approved push failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Membership approved for {$membership->user->name}",
            'membership' => $membership->fresh('user')
        ]);
    }

    public function reject($id)
    {
        $membership = Membership::with('user')->findOrFail($id);

        if ($membership->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Membership is not pending'], 400);
        }

        $membership->update(['status' => 'rejected']);

        // Notify the user their application was rejected
        try {
            if ($membership->user->push_token) {
                PushNotificationService::send(
                    $membership->user->push_token,
                    'Membership Update',
                    "Your {$membership->type} application wasn't approved this time. Contact support for details.",
                    ['type' => 'membership_rejected', 'membership_id' => $membership->id]
                );
            }
        } catch (\Exception $e) {
            \Log::error('Membership rejected push failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => "Membership rejected for {$membership->user->name}",
            'membership' => $membership->fresh('user')
        ]);
    }
    public function gift(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type'    => 'required|in:level_2,level_3',
            'days'    => 'required|integer|min:1|max:3650',
        ]);

        $user  = User::findOrFail($request->user_id);

        $existing = Membership::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('end_date', '>', now())
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => "User already has an active membership until " . Carbon::parse($existing->end_date)->format('M d, Y') . ".",
            ], 422);
        }

        $start = now();
        $end   = $start->copy()->addDays($request->days);

        $membership = Membership::create([
            'user_id'    => $user->id,
            'type'       => $request->type,
            'status'     => 'approved',
            'source'     => 'gifted',
            'start_date' => $start,
            'end_date'   => $end,
        ]);

        $levelMap = ['level_2' => 2, 'level_3' => 3];
        $user->update([
            'is_premium' => true,
            'level'      => $levelMap[$request->type],
        ]);

        return response()->json([
            'success'    => true,
            'message'    => "Membership gifted to {$user->name} for {$request->days} days",
            'membership' => $membership->fresh('user'),
        ]);
    }

    public function expireOldMemberships()
{
    $expired = Membership::with('user')
        ->where('status', 'approved')
        ->where('end_date', '<', Carbon::now())
        ->get();

    foreach ($expired as $m) {
        $m->update(['status' => 'expired']);
        $m->user->update(['is_premium' => false, 'level' => 1]);
    }

    return response()->json(['success' => true, 'expired' => $expired->count()]);
}
}