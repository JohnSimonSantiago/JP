<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Lets a paired kiosk screen (role "kiosk") OR staff/admin through.
// Only used on the few routes a kiosk needs (scan, waiting list). Every other
// staff route keeps 'staff_or_admin', which a kiosk account always fails.
class KioskOrStaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
        }

        if (!in_array($user->role, ['admin', 'staff', 'kiosk'])) {
            return response()->json(['success' => false, 'message' => 'Kiosk or staff access required'], 403);
        }

        return $next($request);
    }
}
