<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
    $newUser->password = Hash::make($request->password); // always hash passwords
    $newUser->level = 1; // default
    $newUser->points = 0; // default
    $newUser->is_premium = false; // default

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


public function getProfile()
{
    $user = auth()->user();
    $membership = $user->memberships()
        ->where('end_date', '>=', now())
        ->orderBy('end_date', 'desc')
        ->first();

    return response()->json([
        'user' => $user,
        'membership' => $membership
    ]);
}
public function uploadProfileImage(Request $request)
{
    $request->validate([
        'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $user = auth()->user();
    
    // Delete old image if exists
    if ($user->profile_image) {
        Storage::delete('public/profiles/' . $user->profile_image);
    }

    // Store new image
    $imageName = time() . '.' . $request->profile_image->extension();
    $request->profile_image->storeAs('public/profiles', $imageName);

    // Update user record
    $user->update(['profile_image' => $imageName]);

    return response()->json(['filename' => $imageName]);
}

}
