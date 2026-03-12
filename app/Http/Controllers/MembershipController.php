<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MembershipController extends Controller
{
    public function adminIndex()
    {
        $memberships = Membership::with('user:id,name,email,profile_image')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'memberships' => $memberships
        ]);
    }

    public function approve($id)
    {
        $membership = Membership::with('user')->findOrFail($id);

        if ($membership->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Membership is not pending'], 400);
        }

        $membership->update(['status' => 'approved']);
        $membership->user->update(['is_premium' => true]);

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

        return response()->json([
            'success' => true,
            'message' => "Membership rejected for {$membership->user->name}",
            'membership' => $membership->fresh('user')
        ]);
    }
}