<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LoungeSession;
use App\Models\LoungePricing;
use App\Models\ConsumablePurchase;
use App\Models\LoungeReceipt;
use Illuminate\Support\Facades\DB;
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
            'hourly_rate'    => 'required|numeric|min:0',
            'half_hour_rate' => 'required|numeric|min:0',
            'bundle_rate'    => 'required|numeric|min:0',
            'bundle_hours'   => 'required|integer|min:1',
            'day_rate'       => 'required|numeric|min:0',
        ]);

        $pricing = LoungePricing::first();
        $pricing->update($request->only([
            'hourly_rate',
            'half_hour_rate',
            'bundle_rate',
            'bundle_hours',
            'day_rate',
        ]));

        return response()->json(['success' => true, 'pricing' => $pricing]);
    }

    // ─── Consumable Time (Level 1 only) ────────────────────────────────────────

    public function searchLevel1Users(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100']);

        $query = User::where('level', 1);

        if ($request->q) {
            $query->where(function ($sub) use ($request) {
                $sub->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('username', 'like', '%' . $request->q . '%');
            });
        }

        $users = $query->select('id', 'name', 'username', 'cash', 'consumable_minutes')
            ->limit(20)
            ->get();

        return response()->json(['success' => true, 'users' => $users]);
    }

    public function buyConsumableTime(Request $request)
    {
        $request->validate([
            'user_id'        => 'required|exists:users,id',
            'hours'          => 'required|integer|min:1|max:24',
            'payment_method' => 'required|in:cash,balance',
        ]);

        $hours  = $request->hours;
        $blocks = intdiv($hours, 3);          // ₱100 per 3-hour block
        $extra  = $hours % 3;                 // ₱40 per leftover hour
        $amount = ($blocks * 100) + ($extra * 40);

        $user = User::findOrFail($request->user_id);

        if ($user->level !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Only Level 1 members can buy consumable time.',
            ], 422);
        }

        if ($request->payment_method === 'balance' && $user->cash < $amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient app balance.',
            ], 422);
        }

        $minutesToAdd = $hours * 60;

        if ($request->payment_method === 'balance') {
            $user->decrement('cash', $amount);
        }

        $user->increment('consumable_minutes', $minutesToAdd);
        $user->refresh();

        ConsumablePurchase::create([
            'user_id'        => $user->id,
            'amount'         => $amount,
            'minutes_added'  => $minutesToAdd,
            'payment_method' => $request->payment_method,
            'purchased_by'   => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Added {$hours} hour(s) for ₱{$amount}.",
            'user' => [
                'id'                 => $user->id,
                'name'               => $user->name,
                'cash'               => $user->cash,
                'consumable_minutes' => $user->consumable_minutes,
            ],
        ]);
    }

    public function consumableBalances(Request $request)
    {
        $users = User::where('level', 1)
            ->where('consumable_minutes', '!=', 0)
            ->select('id', 'name', 'username', 'cash', 'consumable_minutes')
            ->orderBy('consumable_minutes', 'asc')
            ->get();

        return response()->json([
            'success'        => true,
            'users'          => $users,
            'total_minutes'  => $users->where('consumable_minutes', '>', 0)->sum('consumable_minutes'),
            'owing_count'    => $users->where('consumable_minutes', '<', 0)->count(),
        ]);
    }

    public function consumableHistory(Request $request)
    {
        $query = ConsumablePurchase::with(['user:id,name', 'purchasedBy:id,name'])
            ->orderBy('created_at', 'desc');

        // Timestamps are stored in UTC, but "a day" means a Manila calendar day
        if ($request->from) {
            $query->where('created_at', '>=', Carbon::parse($request->from, 'Asia/Manila')->startOfDay()->utc());
        }

        if ($request->to) {
            $query->where('created_at', '<=', Carbon::parse($request->to, 'Asia/Manila')->endOfDay()->utc());
        }

        $purchases = $query->limit(200)->get();

        return response()->json([
            'success'        => true,
            'purchases'      => $purchases,
            'total_cash'     => $purchases->where('payment_method', 'cash')->sum('amount'),
            'total_balance'  => $purchases->where('payment_method', 'balance')->sum('amount'),
            'total_amount'   => $purchases->sum('amount'),
            'total_minutes'  => $purchases->sum('minutes_added'),
        ]);
    }

    // ─── Check In ───────────────────────────────────────────────────────────────

    public function checkIn(Request $request)
    {
        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'user_id'        => 'nullable|exists:users,id',
            'customer_type'  => 'required|in:member,walk_in',
            'group_id'       => 'nullable|string',
            'billing_mode'   => 'nullable|in:hourly,consumable',
            'school'         => 'nullable|string|max:255',
            'receipt_number' => 'nullable|integer',
        ]);

        $userLevel = 1;
        $isFree = false;
        $billingMode = $request->billing_mode ?? 'hourly';

        if ($request->user_id) {
            $user = User::find($request->user_id);
            $userLevel = $user->level ?? 1;
            $isFree = in_array($userLevel, [2, 3]);
        }

        // Consumable mode only makes sense for Level 1 members with a positive balance
        if ($billingMode === 'consumable') {
            if (!$request->user_id || $userLevel !== 1 || !isset($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Consumable time is only available for Level 1 members.',
                ], 422);
            }

            if ($user->consumable_minutes <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This member has no consumable time balance. Please buy time first.',
                ], 422);
            }
        }

        // Free (Level 2/3) and walk-ins always stay hourly
        if ($isFree || $request->customer_type === 'walk_in') {
            $billingMode = 'hourly';
        }

        $session = LoungeSession::create([
            'customer_name'  => $request->customer_name,
            'school'         => $request->school,
            'user_id'        => $request->user_id,
            'customer_type'  => $request->customer_type,
            'user_level'     => $userLevel,
            'group_id'       => $request->group_id,
            'receipt_number' => $request->receipt_number,
            'checked_in_at'  => now(),
            'status'         => 'active',
            'is_free'        => $isFree,
            'billing_mode'   => $billingMode,
            'checked_in_by'  => Auth::id(),
        ]);

        return response()->json(['success' => true, 'session' => $session]);
    }

    // ─── Active Sessions ────────────────────────────────────────────────────────

    public function activeSessions()
    {
        $sessions = LoungeSession::with('user:id,name,level,profile_image,consumable_minutes')
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
            ? LoungeSession::with('user')->where('group_id', $session->group_id)
                ->where('status', 'active')->get()
            : collect([$session->load('user')]);

        $lineItems = [];
        $computedTotal = 0;

        foreach ($sessions as $s) {
            $line = [
                'id'            => $s->id,
                'customer_name' => $s->customer_name,
                'is_free'       => (bool) $s->is_free,
                'billing_mode'  => $s->billing_mode,
                'duration'      => null,
                'bill'          => 0,
                'breakdown'     => null,
                'minutes_used'  => null,
                'new_balance'   => null,
            ];

            $elapsedMinutes = Carbon::parse($s->checked_in_at)->diffInMinutes($checkedOutAt);
            $h = floor($elapsedMinutes / 60);
            $m = $elapsedMinutes % 60;
            $line['duration'] = $h > 0 ? "{$h}h {$m}m" : "{$m}m";

            if ($s->billing_mode === 'consumable') {
                $line['minutes_used'] = $elapsedMinutes;
                $line['new_balance'] = ($s->user?->consumable_minutes ?? 0) - $elapsedMinutes;
                $line['breakdown'] = "{$elapsedMinutes} min deducted from consumable balance";
            } elseif (!$s->is_free) {
                $result = $this->calculateBill($elapsedMinutes, $pricing);
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

            if ($s->billing_mode === 'consumable') {
                // Deduct elapsed minutes from the member's balance (can go negative, no cap)
                if ($s->user) {
                    $s->user->decrement('consumable_minutes', $line['minutes_used']);
                }

                $s->update([
                    'checked_out_at' => $checkedOutAt,
                    'status'         => 'completed',
                    'total_bill'     => 0,
                ]);
                continue;
            }

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

    private function calculateBill(int $elapsedMinutes, LoungePricing $pricing): array
    {
        $dayRate      = $pricing->day_rate;
        $bundleRate   = $pricing->bundle_rate;
        $bundleHrs    = $pricing->bundle_hours;
        $hourlyRate   = $pricing->hourly_rate;
        $halfHourRate = $pricing->half_hour_rate;

        // Grace: 10 free minutes past each hour mark before the next block starts
        $fullHours     = floor($elapsedMinutes / 60);
        $remainingMins = $elapsedMinutes - ($fullHours * 60);

        if ($remainingMins <= 10) {
            $extraHalfHours = 0;              // within grace, ignore the leftover
        } elseif ($remainingMins <= 40) {
            $extraHalfHours = 1;              // half-hour block (+10 min grace)
        } else {
            $extraHalfHours = 2;              // rounds up to a full hour
        }

        $billableHours = $fullHours + intdiv($extraHalfHours, 2);
        $billableHalf  = $extraHalfHours % 2;

        // Nobody pays less than one half-hour block
        if ($billableHours === 0 && $billableHalf === 0) {
            $billableHalf = 1;
        }

        // Fit as many bundles as possible into the full hours
        $bundles        = floor($billableHours / $bundleHrs);
        $remainingHours = $billableHours % $bundleHrs;

        $withBundles = ($bundles * $bundleRate)
            + ($remainingHours * $hourlyRate)
            + ($billableHalf * $halfHourRate);

        $total = min($withBundles, $dayRate);

        if ($total == $dayRate && $withBundles > $dayRate) {
            $breakdown = "Day rate applied (₱{$dayRate})";
        } else {
            $parts = [];
            if ($bundles > 0)        $parts[] = "{$bundles} bundle(s) × ₱{$bundleRate}";
            if ($remainingHours > 0) $parts[] = "{$remainingHours} hr(s) × ₱{$hourlyRate}";
            if ($billableHalf > 0)   $parts[] = "30 min × ₱{$halfHourRate}";
            $breakdown = implode(' + ', $parts);
        }

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

        // Timestamps are stored in UTC, but "a day" means a Manila calendar day
        if ($request->from) {
            $query->where('checked_in_at', '>=', Carbon::parse($request->from, 'Asia/Manila')->startOfDay()->utc());
        }

        if ($request->to) {
            $query->where('checked_in_at', '<=', Carbon::parse($request->to, 'Asia/Manila')->endOfDay()->utc());
        }

        if (!$request->from && !$request->to && $request->date) {
            $query->where('checked_in_at', '>=', Carbon::parse($request->date, 'Asia/Manila')->startOfDay()->utc())
                ->where('checked_in_at', '<=', Carbon::parse($request->date, 'Asia/Manila')->endOfDay()->utc());
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

    // ─── Reserve next monthly CN number (one per check-in event) ─────────────────

    public function reserveReceiptNumber(Request $request)
    {
        $request->validate(['group_id' => 'nullable|string']);

        $now   = Carbon::now('Asia/Manila');
        $year  = (int) $now->format('Y');
        $month = (int) $now->format('n');

        // Lock so two simultaneous check-ins can't grab the same number.
        $number = DB::transaction(function () use ($year, $month, $request) {
            $last = LoungeReceipt::where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->max('number');

            $next = ($last ?? 0) + 1;

            LoungeReceipt::create([
                'year'     => $year,
                'month'    => $month,
                'number'   => $next,
                'group_id' => $request->group_id,
            ]);

            return $next;
        });

        return response()->json(['success' => true, 'number' => $number]);
    }
}