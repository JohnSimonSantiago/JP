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
            'group_id'       => 'nullable|string',
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
            'group_id'       => $request->group_id,
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
        $pricing = LoungePricing::first();

        // "solo=1" forces just this one person, even if they're in a group.
        $soloOut = $request->query('solo') === '1';

        // Gather everyone at this "table". Solo session = group of one.
        $sessions = ($session->group_id && !$soloOut)
            ? LoungeSession::where('group_id', $session->group_id)
                ->where('status', 'active')->get()
            : collect([$session]);

        $lineItems = [];
        $computedTotal = 0;

        foreach ($sessions as $s) {
            $line = [
                'id'            => $s->id,
                'customer_name' => $s->customer_name,
                'is_free'       => (bool) $s->is_free,
                'duration'      => null,
                'bill'          => 0,
                'breakdown'     => null,
            ];

            $elapsedMinutes = Carbon::parse($s->checked_in_at)->diffInMinutes($checkedOutAt);
            $h = floor($elapsedMinutes / 60);
            $m = $elapsedMinutes % 60;
            $line['duration'] = $h > 0 ? "{$h}h {$m}m" : "{$m}m";

            if (!$s->is_free) {
                $floorHours = floor($elapsedMinutes / 60);
                $remainingMins = $elapsedMinutes - ($floorHours * 60);
                $billableHours = $remainingMins <= 10 ? $floorHours : $floorHours + 1;
                $billableHours = max(1, $billableHours);

                $result = $this->calculateBill($billableHours, $pricing);
                $line['bill'] = $result['total'];
                $line['breakdown'] = $result['breakdown'];
                $computedTotal += $result['total'];
            }

            $lineItems[] = $line;
        }

        // Admin override — final amount the operator actually charges
        $finalTotal = $request->has('override_total') && $request->override_total !== null
            ? (float) $request->override_total
            : $computedTotal;

        if ($isPreview) {
            return response()->json([
                'success'         => true,
                'line_items'      => $lineItems,
                'computed_total'  => $computedTotal,
                'total_bill'      => $finalTotal,
                'is_group'        => $sessions->count() > 1,
            ]);
        }

        // Distribute the final total: if overridden, scale each paid line proportionally
        $payable = $computedTotal > 0 ? $computedTotal : 1;
        foreach ($sessions as $s) {
            $line = collect($lineItems)->firstWhere('id', $s->id);
            $share = $s->is_free
                ? 0
                : round(($line['bill'] / $payable) * $finalTotal, 2);

            $s->update([
                'checked_out_at' => $checkedOutAt,
                'status'         => 'completed',
                'total_bill'     => $share,
            ]);
        }

        return response()->json([
            'success'    => true,
            'total_bill' => $finalTotal,
            'line_items' => $lineItems,
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

    // ─── Assign a session to a group ────────────────────────────────────────────

    public function assignGroup(Request $request, $id)
    {
        $request->validate(['group_id' => 'required|string']);

        $session = LoungeSession::findOrFail($id);
        $session->update(['group_id' => $request->group_id]);

        return response()->json(['success' => true, 'session' => $session]);
    }
}