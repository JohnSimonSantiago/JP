<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CoinController extends Controller
{
    /**
     * Weights are out of 1000 (like 1000 raffle tickets).
     * 5 = 94%, 10 = 5%, 15 = 1%
     * Slice size on the wheel matches its weight.
     */
    private const WHEEL = [
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 10, 'weight' => 25],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 15, 'weight' => 10],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 10, 'weight' => 25],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
        ['value' => 5,  'weight' => 47],
    ];

    /**
     * Search any approved member (not just Level 1).
     */
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

        $users = $query->select('id', 'name', 'username', 'level', 'cash')
            ->limit(20)
            ->get();

        return response()->json(['success' => true, 'users' => $users]);
    }

    /**
     * Read spin history back from the file, newest first.
     */
    public function history()
    {
        $path = storage_path('app/coin-spins.jsonl');

        if (!file_exists($path)) {
            return response()->json([
                'success'     => true,
                'spins'       => [],
                'total_coins' => 0,
                'total_spins' => 0,
            ]);
        }

        $spins = collect(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))
            ->map(fn ($line) => json_decode($line, true))
            ->filter()
            ->reverse()
            ->values();

        return response()->json([
            'success'     => true,
            'spins'       => $spins->take(200)->values(),
            'total_coins' => $spins->sum('amount'),
            'total_spins' => $spins->count(),
        ]);
    }

    /**
     * Look up one member by ID (used by the QR scan).
     */
    public function member(User $user)
    {
        return response()->json([
            'success' => true,
            'user'    => $user->only(['id', 'name', 'username', 'level', 'cash']),
        ]);
    }

    /**
     * Wheel layout for the frontend to draw.
     */
    public function wheel()
    {
        return response()->json([
            'success' => true,
            'slices'  => self::WHEEL,
        ]);
    }

    /**
     * Server decides the result, then adds it to the member's cash.
     */
    public function spin(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Draw one of 1000 tickets, then find which slice owns it
        $roll  = random_int(1, 1000);
        $index = 0;
        foreach (self::WHEEL as $i => $slice) {
            $roll -= $slice['weight'];
            if ($roll <= 0) {
                $index = $i;
                break;
            }
        }
        $amount = self::WHEEL[$index]['value'];

        $member = DB::transaction(function () use ($request, $amount) {
            $member = User::lockForUpdate()->findOrFail($request->user_id);
            $member->increment('cash', $amount);
            return $member->fresh();
        });

        // Spin history, one JSON line per spin in storage/app (no table needed)
        file_put_contents(
            storage_path('app/coin-spins.jsonl'),
            json_encode([
                'member_id'   => $member->id,
                'member_name' => $member->name,
                'amount'      => $amount,
                'new_cash'    => $member->cash,
                'admin_id'    => $request->user()->id,
                'admin_name'  => $request->user()->name,
                'created_at'  => now('Asia/Manila')->toIso8601String(),
            ]) . "\n",
            FILE_APPEND | LOCK_EX
        );

        return response()->json([
            'success'     => true,
            'slice_index' => $index,   // frontend lands the wheel on this slice
            'amount'      => $amount,
            'new_cash'    => $member->cash,
            'member'      => [
                'id'   => $member->id,
                'name' => $member->name,
            ],
        ]);
    }
}