<?php

namespace App\Http\Controllers;

use App\Models\CoinSpin;
use App\Models\CoinWheelSetting;
use App\Models\SpinGrant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CoinController extends Controller
{
    private const MAX_TOTAL_SLICES = 40;

    // ─── Wheel helpers ──────────────────────────────────────────────────────────

    /**
     * Turn the prize list into the slices drawn on the wheel.
     * Each amount's slices are spaced evenly around the circle so rare
     * amounts don't bunch up. LOOKS ONLY — the odds come from "chance".
     */
    private function buildSlices(array $prizes): array
    {
        usort($prizes, fn ($a, $b) => $a['amount'] <=> $b['amount']);

        $slots = [];
        foreach ($prizes as $p) {
            $n = max(1, (int) $p['slices']);
            for ($k = 0; $k < $n; $k++) {
                $slots[] = [
                    'value' => (int) $p['amount'],
                    'pos'   => ($k + 0.5) / $n,
                    'n'     => $n,
                ];
            }
        }

        // By position around the wheel; on a tie the rarer amount goes first
        usort($slots, fn ($a, $b) => [$a['pos'], $a['n']] <=> [$b['pos'], $b['n']]);

        return array_map(fn ($s) => ['value' => $s['value']], $slots);
    }

    /**
     * Draw one of 10,000 raffle tickets.
     * Chances are percentages with up to 2 decimals (e.g. 0.5% = 50 tickets).
     */
    private function pickAmount(array $prizes): int
    {
        $roll = random_int(1, 10000);

        foreach ($prizes as $p) {
            $roll -= (int) round($p['chance'] * 100);
            if ($roll <= 0) {
                return (int) $p['amount'];
            }
        }

        return (int) $prizes[array_key_last($prizes)]['amount']; // rounding safety net
    }

    /** Land on any slice that shows the winning amount. */
    private function pickSliceIndex(array $slices, int $amount): int
    {
        $matches = array_keys(array_filter($slices, fn ($s) => $s['value'] === $amount));

        return $matches[array_rand($matches)];
    }

    // ─── Admin: settings ────────────────────────────────────────────────────────

    public function settings()
    {
        $wheel = CoinWheelSetting::current();

        return response()->json([
            'success'      => true,
            'is_enabled'   => $wheel->is_enabled,
            'prizes'       => $wheel->prizes,
            'unused_spins' => (int) User::sum('spin_credits'),
        ]);
    }

    public function toggle(Request $request)
    {
        $request->validate(['is_enabled' => 'required|boolean']);

        $wheel = CoinWheelSetting::current();
        $wheel->update(['is_enabled' => $request->boolean('is_enabled')]);

        return response()->json([
            'success'    => true,
            'is_enabled' => $wheel->is_enabled,
            'message'    => $wheel->is_enabled ? 'Wheel is now ON.' : 'Wheel is now OFF.',
        ]);
    }

    public function updatePrizes(Request $request)
    {
        $request->validate([
            'prizes'          => 'required|array|min:1|max:10',
            'prizes.*.amount' => 'required|integer|min:1|max:1000|distinct',
            'prizes.*.chance' => 'required|numeric|min:0.01|max:100',
            'prizes.*.slices' => 'required|integer|min:1|max:30',
        ], [
            'prizes.*.amount.distinct' => 'Each coin amount can only appear once.',
        ]);

        $prizes = collect($request->prizes)
            ->map(fn ($p) => [
                'amount' => (int) $p['amount'],
                'chance' => round((float) $p['chance'], 2),
                'slices' => (int) $p['slices'],
            ])
            ->sortBy('amount')
            ->values()
            ->all();

        $totalTickets = array_sum(array_map(fn ($p) => (int) round($p['chance'] * 100), $prizes));
        if ($totalTickets !== 10000) {
            return response()->json([
                'success' => false,
                'message' => 'Chances must add up to exactly 100%. Right now they add up to ' . ($totalTickets / 100) . '%.',
            ], 422);
        }

        $totalSlices = array_sum(array_column($prizes, 'slices'));
        if ($totalSlices < 2 || $totalSlices > self::MAX_TOTAL_SLICES) {
            return response()->json([
                'success' => false,
                'message' => 'The wheel needs between 2 and ' . self::MAX_TOTAL_SLICES . ' slices in total.',
            ], 422);
        }

        $wheel = CoinWheelSetting::current();
        $wheel->update(['prizes' => $prizes]);

        return response()->json([
            'success' => true,
            'message' => 'Prizes saved.',
            'prizes'  => $wheel->prizes,
        ]);
    }

    // ─── Admin: members & bonus spins ───────────────────────────────────────────

    public function search(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100']);

        $query = User::approved();

        if ($request->q) {
            $query->where(function ($sub) use ($request) {
                $sub->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('username', 'like', '%' . $request->q . '%');
            });
        }

        $users = $query->select('id', 'name', 'username', 'level', 'cash', 'spin_credits')
            ->limit(20)
            ->get();

        return response()->json(['success' => true, 'users' => $users]);
    }

    /** Look up one member by ID (used by the QR scan). */
    public function member(User $user)
    {
        return response()->json([
            'success' => true,
            'user'    => $user->only(['id', 'name', 'username', 'level', 'cash', 'spin_credits']),
        ]);
    }

    /** Give (positive) or remove (negative) spins. */
    public function grant(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'spins'   => 'required|integer|min:-100|max:100|not_in:0',
            'reason'  => 'nullable|string|max:255',
        ], [
            'spins.not_in' => 'Spins cannot be 0.',
        ]);

        $spins = (int) $request->spins;

        $member = DB::transaction(function () use ($request, $spins) {
            $member = User::lockForUpdate()->findOrFail($request->user_id);

            if ($spins < 0 && $member->spin_credits + $spins < 0) {
                throw ValidationException::withMessages([
                    'spins' => "{$member->name} only has {$member->spin_credits} spin(s) to remove.",
                ]);
            }

            if ($spins > 0) {
                $member->increment('spin_credits', $spins);
            } else {
                $member->decrement('spin_credits', abs($spins));
            }

            SpinGrant::create([
                'user_id'    => $member->id,
                'spins'      => $spins,
                'reason'     => $request->reason,
                'granted_by' => $request->user()->id,
            ]);

            return $member->fresh();
        });

        $message = $spins > 0
            ? "Gave {$spins} spin(s) to {$member->name}."
            : 'Removed ' . abs($spins) . " spin(s) from {$member->name}.";

        return response()->json([
            'success'      => true,
            'message'      => $message,
            'spin_credits' => (int) $member->spin_credits,
            'unused_spins' => (int) User::sum('spin_credits'),
        ]);
    }

    public function history()
    {
        $spins = CoinSpin::orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(200)
            ->get();

        $grants = SpinGrant::with(['user:id,name', 'grantedBy:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(100)
            ->get()
            ->map(fn ($g) => [
                'id'          => $g->id,
                'member_name' => $g->user->name ?? 'Unknown',
                'spins'       => $g->spins,
                'reason'      => $g->reason,
                'admin_name'  => $g->grantedBy->name ?? '—',
                'created_at'  => $g->created_at,
            ]);

        return response()->json([
            'success'     => true,
            'spins'       => $spins,
            'grants'      => $grants,
            'total_coins' => (int) CoinSpin::sum('amount'),
            'total_spins' => CoinSpin::count(),
        ]);
    }

    // ─── Members: their own wheel ───────────────────────────────────────────────

    public function myWheel(Request $request)
    {
        $wheel   = CoinWheelSetting::first();
        $enabled = (bool) ($wheel?->is_enabled ?? false);

        return response()->json([
            'success'      => true,
            'enabled'      => $enabled,
            'slices'       => $enabled ? $this->buildSlices($wheel->prizes) : [],
            'spin_credits' => (int) $request->user()->spin_credits,
        ]);
    }

    /** Server decides the result; the wheel just animates to it. */
    public function memberSpin(Request $request)
    {
        $wheel = CoinWheelSetting::first();

        if (!$wheel || !$wheel->is_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'The wheel is closed right now.',
            ], 403);
        }

        $prizes = $wheel->prizes;
        $slices = $this->buildSlices($prizes);

        $result = DB::transaction(function () use ($request, $prizes, $slices) {
            $member = User::lockForUpdate()->findOrFail($request->user()->id);

            if ($member->spin_credits < 1) {
                return null;
            }

            $amount = $this->pickAmount($prizes);
            $index  = $this->pickSliceIndex($slices, $amount);

            $member->decrement('spin_credits');
            $member->increment('cash', $amount);
            $member->refresh();

            CoinSpin::create([
                'user_id'     => $member->id,
                'member_name' => $member->name,
                'amount'      => $amount,
                'new_cash'    => $member->cash,
            ]);

            return compact('member', 'amount', 'index');
        });

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'No spins left. Buy time to earn more!',
            ], 422);
        }

        return response()->json([
            'success'      => true,
            'slice_index'  => $result['index'],
            'amount'       => $result['amount'],
            'new_cash'     => $result['member']->cash,
            'spin_credits' => (int) $result['member']->spin_credits,
            // Sent back so the wheel redraws if the admin changed it mid-session
            'slices'       => $slices,
        ]);
    }
}