<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\reservation;
use Illuminate\Support\Facades\Auth;


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
    public function createUser(Request $request){
        $newUser = new User();

        $newUser->id = $request->id;
        $newUser->roleId = $request->roleId;
        $newUser->name = $request->name;
        $newUser->email = $request->email;
        $newUser->password = $request->password;

        $res = $newUser->save();

        return redirect('/user');
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

    public function getUsers(){
        return User::all();
        }

    public function updateUser(Request $request){
        // dd($request->userPayload["name"]);
        $user = User::findOrFail($request->editingUserId);

        $user->name = $request->userPayload["name"];
        $user->email = $request->userPayload["email"];
        $user->roleId = $request->userPayload["roleId"];

        $user->save();

        return $user;
    }


    public function deleteUser(Request $request){
        // dd($request->id);
        $deleteUser = User::find($request->id);

        $res = $deleteUser->delete();
        return $res;
    }

}
