<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LoungeSession;
use App\Models\LoungePricing;
use App\Models\ConsumablePurchase;
use App\Models\LoungeReceipt;
use App\Models\CoinWheelSetting;
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
                'message' => 'Not enough Coins.',
            ], 422);
        }

        $minutesToAdd = $hours * 60;

        if ($request->payment_method === 'balance') {
            $user->decrement('cash', $amount);
        }

        $user->increment('consumable_minutes', $minutesToAdd);

        // Wheel spins: 1 per hour, cash purchases only, and only while the wheel is ON.
        // Uses first() instead of current() so a missing settings row never blocks a sale.
        $wheelOn = (bool) (CoinWheelSetting::first()?->is_enabled ?? false);
        $spinsAwarded = ($wheelOn && $request->payment_method === 'cash') ? $hours : 0;

        if ($spinsAwarded > 0) {
            $user->increment('spin_credits', $spinsAwarded);
        }

        $user->refresh();

        // Buying time pays off a negative balance first (increment above). Once the
        // balance is back to 0 or more, they're off the "Needs payment" list.
        if ($user->consumable_minutes >= 0) {
            $this->clearPaymentPending($user->id);
        }

        ConsumablePurchase::create([
            'user_id'        => $user->id,
            'amount'         => $amount,
            'minutes_added'  => $minutesToAdd,
            'spins_awarded'  => $spinsAwarded,
            'payment_method' => $request->payment_method,
            'purchased_by'   => Auth::id(),
        ]);

        $message = "Added {$hours} hour(s) for ₱{$amount}.";
        if ($spinsAwarded > 0) {
            $message .= " +{$spinsAwarded} wheel spin(s).";
        }

        return response()->json([
            'success'       => true,
            'message'       => $message,
            'spins_awarded' => $spinsAwarded,
            'user' => [
                'id'                 => $user->id,
                'name'               => $user->name,
                'cash'               => $user->cash,
                'consumable_minutes' => $user->consumable_minutes,
                'spin_credits'       => $user->spin_credits,
            ],
        ]);
    }

    public function overwriteConsumableBalance(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'minutes' => 'required|integer|min:-100000|max:100000',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->level !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Only Level 1 members have consumable time.',
            ], 422);
        }

        $user->update(['consumable_minutes' => $request->minutes]);

        // Set to 0 or more by staff: nothing owed anymore, off the "Needs payment" list
        if ($user->consumable_minutes >= 0) {
            $this->clearPaymentPending($user->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Balance updated.',
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

        $purchases = $query->get();

        return response()->json([
            'success'        => true,
            'purchases'      => $purchases,
            'total_cash'     => $purchases->where('payment_method', 'cash')->sum('amount'),
            'total_balance'  => $purchases->where('payment_method', 'balance')->sum('amount'),
            'total_amount'   => $purchases->sum('amount'),
            'total_minutes'  => $purchases->sum('minutes_added'),
        ]);
    }

    public function consumableTopSpenders(Request $request)
    {
        $query = ConsumablePurchase::query();

        // Same Manila-day handling as the history endpoint
        if ($request->from) {
            $query->where('created_at', '>=', Carbon::parse($request->from, 'Asia/Manila')->startOfDay()->utc());
        }
        if ($request->to) {
            $query->where('created_at', '<=', Carbon::parse($request->to, 'Asia/Manila')->endOfDay()->utc());
        }

        $rows = $query
            ->selectRaw('user_id, SUM(amount) as total_spent, SUM(minutes_added) as total_minutes, COUNT(*) as purchase_count')
            ->groupBy('user_id')
            ->with('user:id,name,username')
            ->orderByDesc('total_spent')
            ->get()
            ->map(function ($r) {
                return [
                    'user_id'        => $r->user_id,
                    'name'           => $r->user->name ?? 'Unknown',
                    'username'       => $r->user->username ?? null,
                    'total_spent'    => (float) $r->total_spent,
                    'total_minutes'  => (int) $r->total_minutes,
                    'purchase_count' => (int) $r->purchase_count,
                ];
            });

        return response()->json([
            'success' => true,
            'users'   => $rows,
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

        return $this->startSession($request->only([
            'customer_name', 'user_id', 'customer_type', 'group_id',
            'billing_mode', 'school', 'receipt_number',
        ]));
    }

    // The one time-in path: the Check In window and /scan both use it.
    // $in = already-validated check-in fields.
    private function startSession(array $in)
    {
        // Every field present (missing ones = null), so the code below can read them directly
        $in = array_merge(array_fill_keys([
            'customer_name', 'user_id', 'customer_type', 'group_id',
            'billing_mode', 'school', 'receipt_number',
        ], null), $in);

        $userLevel = 1;
        $isFree = false;
        $billingMode = $in['billing_mode'] ?? 'hourly';

        if ($in['user_id']) {
            $user = User::find($in['user_id']);
            $userLevel = $user->level ?? 1;
            $isFree = in_array($userLevel, [2, 3]);
        }

        // Consumable mode is Level 1 only — balance may be 0 or negative, it just counts down past zero
        if ($billingMode === 'consumable') {
            if (!$in['user_id'] || $userLevel !== 1 || !isset($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Consumable time is only available for Level 1 members.',
                ], 422);
            }

        }

        // Free (Level 2/3) and walk-ins always stay hourly
        if ($isFree || $in['customer_type'] === 'walk_in') {
            $billingMode = 'hourly';
        }

        // Level 1 members ALWAYS use consumable minutes, never hourly, even with a
        // 0 or negative balance (it just counts down further). Enforced here so the
        // web page and the mobile kiosk behave the same.
        if ($in['user_id'] && $in['customer_type'] === 'member' && !$isFree && $userLevel === 1) {
            $billingMode = 'consumable';
        }

        // One active session per member. The row lock makes a second simultaneous
        // check-in (kiosk + web at the same second) wait, then see the first one.
        $session = DB::transaction(function () use ($in, $userLevel, $isFree, $billingMode) {
            if ($in['user_id']) {
                User::where('id', $in['user_id'])->lockForUpdate()->first();

                $alreadyIn = LoungeSession::where('user_id', $in['user_id'])
                    ->where('status', 'active')
                    ->exists();

                if ($alreadyIn) {
                    return null;
                }
            }

            return LoungeSession::create([
                'customer_name'  => $in['customer_name'],
                'school'         => $in['school'],
                'user_id'        => $in['user_id'],
                'customer_type'  => $in['customer_type'],
                'user_level'     => $userLevel,
                'group_id'       => $in['group_id'],
                'receipt_number' => $in['receipt_number'],
                'checked_in_at'  => now(),
                'status'         => 'active',
                'is_free'        => $isFree,
                'billing_mode'   => $billingMode,
                'checked_in_by'  => Auth::id(),
            ]);
        });

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => "{$in['customer_name']} is already checked in.",
            ], 422);
        }

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
        return $this->runCheckout(
            $id,
            $request->query('preview') === '1',
            $request->query('solo') === '1',     // just this person, even if in a group
            $request->input('override_total'),
        );
    }

    // The one time-out path: the bill window, group checkout and /scan all use it.
    // $isPreview = work out the bill without saving.
    private function runCheckout($id, bool $isPreview, bool $soloOut, $overrideTotal = null)
    {
        $session = LoungeSession::findOrFail($id);

        if ($session->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Session already completed'], 400);
        }

        $checkedOutAt = now();
        $pricing = LoungePricing::first();


        // Gather everyone at this "table". Solo session = group of one.
        $sessions = ($session->group_id && !$soloOut)
            ? LoungeSession::with('user')->where('group_id', $session->group_id)
                ->where('status', 'active')
                // Time-in order (same as the group card): ties in the override split go to the first members
                ->orderBy('checked_in_at')->orderBy('id')
                ->get()
            : collect([$session->load('user')]);

        $lineItems = [];
        $computedTotal = 0;

        foreach ($sessions as $s) {
            $line = [
                'id'            => $s->id,
                'user_id'       => $s->user_id,
                'customer_name' => $s->customer_name,
                'checked_in_at' => $s->checked_in_at, // display only: each person's own time in
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

        // Admin override — final amount the operator actually charges, always in whole pesos
        $isOverride = $overrideTotal !== null && $overrideTotal !== '';
        $finalTotal = $isOverride
            ? (int) round(max(0, (float) $overrideTotal))
            : $computedTotal;

        // What each person actually pays. Without an override that's just their own bill.
        // With an override, it's split in whole pesos by splitWholePesos() (the only place
        // that does this split; preview and final time out both use it).
        $shares = $isOverride
            ? $this->splitWholePesos($finalTotal, array_column($lineItems, 'bill'))
            : array_column($lineItems, 'bill');
        foreach ($lineItems as $i => $line) {
            $lineItems[$i]['share'] = $shares[$i];
        }

        if ($isPreview) {
            return response()->json([
                'success'         => true,
                'line_items'      => $lineItems,
                'computed_total'  => $computedTotal,
                'total_bill'      => $finalTotal,
                'is_group'        => $sessions->count() > 1,
            ]);
        }

        $pendingIds = []; // sessions that ended with a negative balance
        foreach ($sessions as $s) {
            $line = collect($lineItems)->firstWhere('id', $s->id);

            if ($s->billing_mode === 'consumable') {
                // Deduct elapsed minutes from the member's balance (can go negative, no cap)
                $owes = false;
                if ($s->user) {
                    $s->user->decrement('consumable_minutes', $line['minutes_used']);
                    $owes = $s->user->consumable_minutes < 0;
                }

                // Still negative after timing out -> onto the "Needs payment" list.
                // One entry per member: an older unpaid entry is replaced by this one.
                if ($owes) {
                    $this->clearPaymentPending($s->user_id);
                    $pendingIds[] = $s->id;
                }

                $s->update([
                    'checked_out_at'     => $checkedOutAt,
                    'status'             => 'completed',
                    'total_bill'         => 0,
                    'payment_pending_at' => $owes ? $checkedOutAt : null,
                ]);
                continue;
            }

            $share = $s->is_free ? 0 : $line['share'];

            $s->update([
                'checked_out_at' => $checkedOutAt,
                'status'         => 'completed',
                'total_bill'     => $share,
            ]);
        }

        foreach ($lineItems as $i => $line) {
            $lineItems[$i]['payment_pending'] = in_array($line['id'], $pendingIds);
        }

        return response()->json([
            'success'    => true,
            'total_bill' => $finalTotal,
            'line_items' => $lineItems,
        ]);
    }

    // ─── Needs payment ──────────────────────────────────────────────────────────

    // Members who timed out with a negative balance and haven't paid yet.
    public function needsPayment()
    {
        return response()->json(['success' => true, 'needs_payment' => $this->needsPaymentList()]);
    }

    private function needsPaymentList(): array
    {
        $rows = LoungeSession::with('user:id,name,username,level,consumable_minutes,cash')
            ->whereNotNull('payment_pending_at')
            ->orderBy('payment_pending_at')
            ->get();

        $list = [];
        foreach ($rows as $s) {
            $balance = (int) ($s->user->consumable_minutes ?? 0);

            // Already back to 0+ some other way (e.g. balance edited): tidy up, skip
            if (!$s->user || $balance >= 0) {
                $s->update(['payment_pending_at' => null]);
                continue;
            }

            $list[] = [
                'session_id'         => $s->id,
                'user_id'            => $s->user_id,
                'name'               => $s->user->name,
                'checked_in_at'      => $s->checked_in_at,
                'checked_out_at'     => $s->checked_out_at,
                'payment_pending_at' => $s->payment_pending_at,
                'minutes_used'       => (int) Carbon::parse($s->checked_in_at)->diffInMinutes($s->checked_out_at),
                'balance'            => $balance,     // e.g. -25
                'owed_minutes'       => -$balance,    // e.g. 25
                'user'               => $s->user,     // for the Buy time window
            ];
        }

        return $list;
    }

    // "Pay next visit": off the list now; the balance stays negative and is paid
    // off automatically by their next time purchase.
    public function payLater($id)
    {
        $session = LoungeSession::with('user:id,name')->findOrFail($id);

        if (!$session->payment_pending_at) {
            return response()->json(['success' => false, 'message' => 'This member is not on the Needs payment list.'], 422);
        }

        $this->clearPaymentPending($session->user_id);

        $name = $session->user->name ?? $session->customer_name;
        return response()->json(['success' => true, 'message' => "{$name} will pay next visit."]);
    }

    // Take a member off the "Needs payment" list (all of their unpaid entries).
    private function clearPaymentPending(?int $userId): void
    {
        if (!$userId) return;
        LoungeSession::where('user_id', $userId)
            ->whereNotNull('payment_pending_at')
            ->update(['payment_pending_at' => null]);
    }

    // ─── Override split ─────────────────────────────────────────────────────────

    // Splits a whole-peso total across people in proportion to their own bills,
    // with no centavos, and the shares always add up to exactly $total.
    //   1. Everyone gets the whole-peso part of their fair share (rounded down).
    //   2. The pesos left over go ₱1 each to whoever lost the most in step 1.
    //      Ties (e.g. equal bills) go to the first people in the list (time-in order).
    // Equal bills, ₱250: 3 people = 84, 83, 83 · 4 people = 63, 63, 62, 62.
    // People with a ₱0 bill (free / consumable time) always get ₱0.
    private function splitWholePesos(int $total, array $bills): array
    {
        // Bills in centavos so the math stays in whole numbers (no float rounding)
        $weights = array_map(fn ($b) => max(0, (int) round(((float) $b) * 100)), $bills);
        $sum = array_sum($weights);

        if ($sum === 0 || $total <= 0) {
            return array_fill(0, count($bills), 0);
        }

        $shares = [];
        $remainders = [];
        foreach ($weights as $i => $w) {
            $shares[$i] = intdiv($total * $w, $sum);
            $remainders[$i] = ($total * $w) % $sum;
        }

        // Biggest rounding loss first; on a tie, the earlier person first
        $order = array_keys($weights);
        usort($order, fn ($a, $b) => [$remainders[$b], $a] <=> [$remainders[$a], $b]);

        $leftover = $total - array_sum($shares);
        foreach ($order as $i) {
            if ($leftover <= 0) break;
            if ($weights[$i] === 0) continue;
            $shares[$i]++;
            $leftover--;
        }

        return $shares;
    }

    // ─── Billing Calculator ─────────────────────────────────────────────────────

    private function calculateBill(int $elapsedMinutes, LoungePricing $pricing): array
    {
        $dayRate      = $pricing->day_rate;
        $bundleRate   = $pricing->bundle_rate;
        $bundleHrs    = $pricing->bundle_hours;
        $hourlyRate   = $pricing->hourly_rate;
        $halfHourRate = $pricing->half_hour_rate;

        // Grace: under 5 minutes past each 30-min mark before the next block starts
        // e.g. 34 min = 30 min block, 35 min = full hour; 1h04m = 1 hr, 1h05m = 1h30m
        $fullHours     = floor($elapsedMinutes / 60);
        $remainingMins = $elapsedMinutes - ($fullHours * 60);

        if ($remainingMins < 5) {
            $extraHalfHours = 0;              // within grace, ignore the leftover
        } elseif ($remainingMins < 35) {
            $extraHalfHours = 1;              // half-hour block (+5 min grace)
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

        // A partial run into a bundle should never cost more than just buying
        // the whole bundle. e.g. 2h30m must cap at the 3-hour bundle price.
        if ($billableHalf > 0 || ($remainingHours > 0 && $remainingHours < $bundleHrs)) {
            $bundlesIfRoundedUp = $bundles + 1;
            $priceIfRoundedUp   = $bundlesIfRoundedUp * $bundleRate;
            $withBundles        = min($withBundles, $priceIfRoundedUp);
        }

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

        return response()->json(['success' => true, 'number' => $this->nextReceiptNumber($request->group_id)]);
    }

    // Next CN number for this Manila month (shared by /reserve-receipt and /scan).
    private function nextReceiptNumber(?string $groupId): int
    {

        $now   = Carbon::now('Asia/Manila');
        $year  = (int) $now->format('Y');
        $month = (int) $now->format('n');

        // Lock so two simultaneous check-ins can't grab the same number.
        $number = DB::transaction(function () use ($year, $month, $groupId) {
            $last = LoungeReceipt::where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->max('number');

            $next = ($last ?? 0) + 1;

            LoungeReceipt::create([
                'year'     => $year,
                'month'    => $month,
                'number'   => $next,
                'group_id' => $groupId,
            ]);

            return $next;
        });

        return $number;
    }

    // ─── RFID cards ─────────────────────────────────────────────────────────────

    // "43 a8 7d 1a" -> "43A87D1A" so spacing or letter case never causes a mismatch
    private function normalizeUid(string $uid): string
    {
        return strtoupper(preg_replace('/[^0-9A-Fa-f]/', '', $uid));
    }

    // A card was tapped: whose card is it, and are they already inside?
    // (Kept for existing callers; /scan/lookup does the same for cards and QR codes.)
    public function rfidLookup(Request $request)
    {
        $request->validate(['uid' => 'required|string|max:64']);
        $uid = $this->normalizeUid($request->uid);

        if ($uid === '') {
            return response()->json(['success' => false, 'message' => 'Invalid card.'], 422);
        }

        return $this->scanResult('rfid', ['uid' => $uid]);
    }

    // One endpoint for anything scanned at the counter: an RFID card ID or a
    // member QR (levelup://user/{id}). The format decides which lookup runs.
    public function scanLookup(Request $request)
    {
        $request->validate(['code' => 'required|string|max:255']);
        $code = $this->detectScanCode($request->code);

        if (!$code) {
            return response()->json([
                'success' => false,
                'source'  => null,
                'message' => 'Unknown code — not a card or a member QR.',
            ], 422);
        }

        return $this->scanResult($code['source'], $code);
    }

    // What was scanned? The only place that decides between QR and RFID.
    //   "levelup://user/123"            -> ['source' => 'qr',   'user_id' => 123]
    //   "43 A8 7D 1A" / "43A87D1A"      -> ['source' => 'rfid', 'uid' => '43A87D1A']
    //   anything else                   -> null
    private function detectScanCode(string $raw): ?array
    {
        $raw = trim($raw);

        // Member QR. Only the "user/<id>" ending is required, so a keyboard
        // scanner that mistypes the "levelup://" part still works.
        if (preg_match('~(?:^|[^a-z0-9])user/(\d{1,10})/?$~i', $raw, $m)) {
            return ['source' => 'qr', 'user_id' => (int) $m[1]];
        }

        // RFID card ID: 4, 7 or 10 bytes in hex, with optional spaces, colons or dashes
        $hex = preg_replace('/[\s:\-]/', '', $raw);
        if (preg_match('/^[0-9A-Fa-f]{8,20}$/', $hex)) {
            return ['source' => 'rfid', 'uid' => strtoupper($hex)];
        }

        return null;
    }

    // Who does a detected code belong to? (QR = user ID, RFID = linked card)
    private function findScanUser(array $code): ?User
    {
        $query = User::select('id', 'name', 'username', 'level', 'consumable_minutes', 'cash');
        return $code['source'] === 'qr'
            ? $query->where('id', $code['user_id'])->first()
            : $query->where('rfid_uid', $code['uid'])->first();
    }

    // "Juan Dela Cruz" -> "Juan C."  (public screens: first name + last initial)
    private function shortName(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        if (count($parts) <= 1) return $parts[0] ?? '';
        return $parts[0] . ' ' . mb_strtoupper(mb_substr(end($parts), 0, 1)) . '.';
    }

    // ─── Scan (time in/out) ─────────────────────────────────────────────────────

    // THE time in/out decision for a scanned card or member QR, used by the staff
    // dashboard and by kiosk screens. Reuses startSession() and runCheckout().
    //   result: in | out | unknown | see_staff | duplicate
    public function scan(Request $request)
    {
        $request->validate(['code' => 'required|string|max:255']);
        $code = $this->detectScanCode($request->code);

        if (!$code) {
            return response()->json([
                'success' => false,
                'result'  => 'unknown',
                'source'  => null,
                'message' => 'Unknown code — not a card or a member QR.',
            ], 422);
        }

        $base = ['success' => true, 'source' => $code['source'], 'uid' => $code['uid'] ?? null];
        $user = $this->findScanUser($code);

        if (!$user) {
            return response()->json($base + [
                'result'  => 'unknown',
                'message' => $code['source'] === 'qr'
                    ? 'Unknown QR — not a member.'
                    : 'Unknown card — link it to a member.',
            ]);
        }

        $who = [
            'id'           => $user->id,
            'name'         => $user->name,
            'display_name' => $this->shortName($user->name),
            'level'        => $user->level,
        ];
        $base['user'] = $who;

        // Same person within a few seconds = one scan, even across screens/devices
        $window = now()->subSeconds(10);
        $justIn = LoungeSession::where('user_id', $user->id)->where('status', 'active')
            ->where('checked_in_at', '>=', $window)->exists();
        $justOut = LoungeSession::where('user_id', $user->id)->where('status', 'completed')
            ->where('checked_out_at', '>=', $window)->exists();
        if ($justIn || $justOut) {
            return response()->json($base + [
                'result'  => 'duplicate',
                'message' => $justIn ? "{$user->name} just timed in." : "{$user->name} just timed out.",
            ]);
        }

        $active = LoungeSession::where('user_id', $user->id)->where('status', 'active')->first();

        // ── Not inside: time in ──
        if (!$active) {
            $receipt = $this->nextReceiptNumber(null); // saved on the session for reprints
            $res = $this->startSession([
                'customer_name'  => $user->name,
                'user_id'        => $user->id,
                'customer_type'  => 'member',
                'receipt_number' => $receipt,
            ]);
            $data = $res->getData(true);

            if (!($data['success'] ?? false)) {
                return response()->json($base + [
                    'result'  => 'see_staff',
                    'message' => $data['message'] ?? 'Could not time in.',
                ]);
            }

            $session = $data['session'];
            $balance = (int) $user->consumable_minutes;
            $mode = $session['is_free'] ? 'free' : $session['billing_mode']; // free | consumable

            return response()->json($base + [
                'result'         => 'in',
                'mode'           => $mode,
                'balance'        => $mode === 'consumable' ? $balance : null,
                'owes_minutes'   => $mode === 'consumable' && $balance < 0 ? -$balance : 0,
                'session_id'     => $session['id'],
                'receipt_number' => $session['receipt_number'],
                'message'        => "{$user->name} timed in.",
            ]);
        }

        // ── Inside, on an old hourly session (from before "members always consumable") ──
        if ($active->billing_mode !== 'consumable' && !$active->is_free) {
            return response()->json($base + [
                'result'     => 'see_staff',
                'reason'     => 'hourly_bill',
                'session_id' => $active->id,
                'message'    => "{$user->name} pays by the hour — please see staff to time out.",
            ]);
        }

        // ── Inside: time out (free or consumable), just this person ──
        $out = $this->runCheckout($active->id, false, true)->getData(true);
        $line = $out['line_items'][0] ?? [];
        $balance = (int) $user->fresh()->consumable_minutes;
        $mode = !empty($line['is_free']) ? 'free' : 'consumable';
        $owes = $mode === 'consumable' && $balance < 0;

        return response()->json($base + [
            'result'          => 'out',
            'mode'            => $mode,
            'used'            => $line['duration'] ?? null,
            'balance'         => $mode === 'consumable' ? $balance : null,
            'owes_minutes'    => $owes ? -$balance : 0,
            'payment_pending' => (bool) ($line['payment_pending'] ?? false),
            'session_id'      => $active->id,
            'message'         => $owes
                ? "{$user->name} timed out — owes " . $this->minutesText(-$balance) . '. Please see staff.'
                : "{$user->name} timed out.",
        ]);
    }

    // 95 -> "1h 35m"
    private function minutesText(int $minutes): string
    {
        return intdiv($minutes, 60) . 'h ' . ($minutes % 60) . 'm';
    }

    // ─── Kiosk screens ──────────────────────────────────────────────────────────

    // Staff on /lounge/display: "Set up this screen as a kiosk". Returns a token for
    // the Lounge Kiosk account, which can only use the kiosk routes.
    public function kioskPair(Request $request)
    {
        $kiosk = User::where('role', 'kiosk')->first();
        if (!$kiosk) {
            $kiosk = new User();
            $kiosk->name = 'Lounge Kiosk';
            $kiosk->email = 'kiosk@levelinglounge.local';
            $kiosk->password = bcrypt(\Illuminate\Support\Str::random(64)); // nobody logs in as the kiosk
            $kiosk->role = 'kiosk';
            $kiosk->level = 1;
            $kiosk->points = 0;
            $kiosk->stars = 0;
            $kiosk->is_approved = false; // the login form refuses it; only paired tokens work
            $kiosk->save();
        }

        $token = $kiosk->createToken('kiosk:' . ($request->user()->name ?? 'staff'))->plainTextToken;

        return response()->json([
            'success' => true,
            'token'   => $token,
            'message' => 'This screen is now a kiosk.',
        ]);
    }

    // Staff: un-pair every kiosk screen (they go back to "Not set up").
    public function kioskRevokeAll()
    {
        $kiosk = User::where('role', 'kiosk')->first();
        $count = $kiosk ? $kiosk->tokens()->count() : 0;
        if ($kiosk) $kiosk->tokens()->delete();

        return response()->json(['success' => true, 'message' => "Removed {$count} kiosk screen(s)."]);
    }

    // Kiosk/staff: who is waiting to pay, as short names for the public screen.
    public function kioskWaiting()
    {
        $names = collect($this->needsPaymentList())
            ->map(fn ($row) => $this->shortName($row['name']))
            ->values();

        return response()->json(['success' => true, 'waiting' => $names]);
    }

    // Shared answer for every scan: who is it, and are they already inside?
    // Same shape for RFID and QR (plus "source"), so the page runs one time in/out flow.
    private function scanResult(string $source, array $code)
    {
        $user = $this->findScanUser($code);

        $base = [
            'success' => true,
            'source'  => $source,
            'uid'     => $code['uid'] ?? null, // card ID, needed to link an unknown card
        ];

        if (!$user) {
            return response()->json($base + [
                'linked'  => false,
                'message' => $source === 'qr'
                    ? 'Unknown QR — not a member.'
                    : 'Unknown card — link it to a member.',
            ]);
        }

        $session = LoungeSession::with('user:id,name,level,profile_image,consumable_minutes')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        return response()->json($base + [
            'linked'         => true,
            'user'           => $user,
            'active_session' => $session,
        ]);
    }

    // Link a card to a member. One card per member: a new card replaces the old one.
    public function rfidLink(Request $request)
    {
        $request->validate([
            'uid'     => 'required|string|max:64',
            'user_id' => 'required|exists:users,id',
        ]);
        $uid = $this->normalizeUid($request->uid);

        if ($uid === '') {
            return response()->json(['success' => false, 'message' => 'Invalid card.'], 422);
        }

        $owner = User::where('rfid_uid', $uid)->first();
        if ($owner && $owner->id != $request->user_id) {
            return response()->json([
                'success' => false,
                'message' => "This card already belongs to {$owner->name}.",
            ], 422);
        }

        $user = User::findOrFail($request->user_id);
        $user->rfid_uid = $uid; // set directly, so it doesn't need to be in $fillable
        $user->save();

        return response()->json(['success' => true, 'message' => "Card linked to {$user->name}."]);
    }
}