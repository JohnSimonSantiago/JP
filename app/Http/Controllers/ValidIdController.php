<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ValidIdFiles;
use Illuminate\Http\Request;

// Serves valid ID files, which have no public URL (see App\Services\ValidIdFiles).
class ValidIdController extends Controller
{
    // GET /api/admin/users/{user}/valid-id   (admin middleware)
    public function adminShow(User $user)
    {
        return ValidIdFiles::response($user->valid_id)
            ?? response()->json(['success' => false, 'message' => 'No valid ID on file for this user.'], 404);
    }

    // GET /api/user/valid-id   (logged-in user: only their OWN file)
    public function mine(Request $request)
    {
        return ValidIdFiles::response($request->user()->valid_id)
            ?? response()->json(['success' => false, 'message' => 'You have no valid ID on file.'], 404);
    }
}
