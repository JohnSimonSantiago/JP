<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Membership;

class MembershipController extends Controller
{
// Route: POST /api/user/request-premium
public function requestPremium()
{
    $membership = Membership::create([
        'user_id' => auth()->id(),
        'start_date' => now(), // Will be updated when approved
        'end_date' => now()->addMonth(), // Will be updated when approved
        'type' => 'monthly',
        'status' => 'pending'
    ]);
    
    return response()->json(['membership' => $membership]);
}
}
