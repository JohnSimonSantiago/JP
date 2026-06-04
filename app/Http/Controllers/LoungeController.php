<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LoungeSession;
use App\Models\LoungePricing;
use Carbon\Carbon;

class LoungeController extends Controller
{
    // ─── Pricing ────────────────────────────────────────────────────────────────

    public function getPricing()
    {
        $pricing = LoungePricing::first();
        return response()->json(['success' => true, 'pricing' => $pricing]);
    }

    public function updatePricing(Request $request)
    {
        $request->validate([
            'hourly_rate' => 'required|numeric|min:0',
            'bundle_rate' => 'required|numeric|min:0',
            'bundle_hours' => 'required|integer|min:1',
            'day_rate'    => 'required|numeric|min:0',
        ]);

        $pricing = LoungePricing::first();
        $pricing->update($request->only(['hourly_rate', 'bundle_rate', 'bundle_hours', 'day_rate']));

        return response()->json(['success' => true, 'pricing' => $pricing]);
    }

    // ─── Check In ───────────────────────────────────────────────────────────────

    public function checkIn(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'user_id'        => 'nullable|exists:users,id',
            'customer_type'  => 'required|in:member,walk_in',
        ]);

        $userLevel = 1;
        $isFree = false;

        if ($request->user_id) {
            $user = User::find($request->user_id);
            $userLevel = $user->level ?? 1;
            $isFree = in_array($userLevel, [2, 3]);
        }

        $session = LoungeSession::create([
            'customer_name'  => $request->customer_name,
            'user_id'        => $request->user_id,
            'customer_type'  => $request->customer_type,
            'user_level'     => $userLevel,
            'checked_in_at'  => now(),
            'status'         => 'active',
            'is_free'        => $isFree,
            'checked_in_by'  => Auth::id(),
        ]);

        return response()->json(['success' => true, 'session' => $session]);
    }

    // ─── Active Sessions ────────────────────────────────────────────────────────

    public function activeSessions()
    {
        $sessions = LoungeSession::with('user:id,name,level,profile_image')
            ->where('status', 'active')
            ->orderBy('checked_in_at', 'asc')
            ->get()
            ->map(function ($session) {
                $session->elapsed_seconds = Carbon::parse($session->checked_in_at)->diffInSeconds(now());
                return $session;
            });

        return response()->json(['success' => true, 'sessions' => $sessions]);
    }

    // ─── Checkout ───────────────────────────────────────────────────────────────

    public function checkOut(Request $request, $id)
    {
        $session = LoungeSession::findOrFail($id);

        if ($session->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Session already completed'], 400);
        }

        $isPreview = $request->query('preview') === '1';
        $checkedOutAt = now();
        $totalBill = 0;
        $billBreakdown = null;

        if (!$session->is_free) {
            $pricing = LoungePricing::first();
            $elapsedMinutes = Carbon::parse($session->checked_in_at)->diffInMinutes($checkedOutAt);

            $rawHours = $elapsedMinutes / 60;
            $floorHours = floor($rawHours);
            $remainingMins = $elapsedMinutes - ($floorHours * 60);
            $billableHours = $remainingMins <= 10 ? $floorHours : $floorHours + 1;
            $billableHours = max(1, $billableHours);

            $result = $this->calculateBill($billableHours, $pricing);
            $totalBill = $result['total'];
            $billBreakdown = $result['breakdown'];
        }

        // Preview mode — just return the bill without saving
        if ($isPreview) {
            return response()->json([
                'success'        => true,
                'total_bill'     => $totalBill,
                'bill_breakdown' => $billBreakdown,
            ]);
        }

        $session->update([
            'checked_out_at' => $checkedOutAt,
            'status'         => 'completed',
            'total_bill'     => $totalBill,
        ]);

        return response()->json([
            'success'        => true,
            'session'        => $session,
            'total_bill'     => $totalBill,
            'bill_breakdown' => $billBreakdown,
        ]);
    }

    // ─── Billing Calculator ─────────────────────────────────────────────────────

    private function calculateBill(int $hours, LoungePricing $pricing): array
    {
        $dayRate    = $pricing->day_rate;
        $bundleRate = $pricing->bundle_rate;
        $bundleHrs  = $pricing->bundle_hours;
        $hourlyRate = $pricing->hourly_rate;

        // How many full bundles fit
        $bundles        = floor($hours / $bundleHrs);
        $remainingHours = $hours % $bundleHrs;

        $withBundles = ($bundles * $bundleRate) + ($remainingHours * $hourlyRate);
        $total       = min($withBundles, $dayRate);

        $breakdown = $total == $dayRate
            ? "Day rate applied (₱{$dayRate})"
            : "{$bundles} bundle(s) × ₱{$bundleRate} + {$remainingHours} hr(s) × ₱{$hourlyRate}";

        return ['total' => $total, 'breakdown' => $breakdown];
    }

// ─── User Stats ─────────────────────────────────────────────────────────────

    public function myStats(Request $request)
    {
        $userId = Auth::id();

        $sessions = LoungeSession::where('user_id', $userId)
            ->where('status', 'completed')
            ->get();

        $totalMinutes = $sessions->sum(function ($s) {
            return Carbon::parse($s->checked_in_at)->diffInMinutes($s->checked_out_at);
        });

        $totalHours = floor($totalMinutes / 60);
        $remainingMins = $totalMinutes % 60;

        return response()->json([
            'success' => true,
            'total_visits' => $sessions->count(),
            'total_time' => "{$totalHours}h {$remainingMins}m",
            'total_minutes' => $totalMinutes,
        ]);
    }

    // ─── Session History ────────────────────────────────────────────────────────

    public function sessionHistory(Request $request)
    {
        $query = LoungeSession::with('user:id,name')
            ->where('status', 'completed')
            ->orderBy('checked_out_at', 'desc');

        if ($request->date) {
            $query->whereDate('checked_in_at', $request->date);
        }

        $sessions = $query->get();

        return response()->json(['success' => true, 'sessions' => $sessions]);
    }

    public function myActiveSession(Request $request)
{
    $session = LoungeSession::where('user_id', Auth::id())
        ->where('status', 'active')
        ->first();

    return response()->json(['success' => true, 'session' => $session]);
}
}