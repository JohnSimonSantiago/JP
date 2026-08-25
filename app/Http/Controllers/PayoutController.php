<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Payout;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    /**
     * Show a shop's payout balance: how much it's owed right now,
     * plus its history of past payouts.
     * Accessible by the shop owner or an admin.
     */
    public function show(Shop $shop)
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$shop->canBeEditedBy($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Balance = sum of (shop_claim_amount * quantity) for completed, unremitted orders
        $unremitted = Purchase::where('shop_id', $shop->id)
            ->where('status', 'completed')
            ->where('payout_status', 'unremitted')
            ->get();

        $balance = (float) $unremitted->sum(fn($p) => $p->shop_claim_amount * $p->quantity);
        $orderCount = $unremitted->count();

        $history = Payout::where('shop_id', $shop->id)
            ->with('processedBy:id,name')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success'          => true,
            'balance'          => $balance,
            'claimable_orders' => $orderCount,
            'history'          => $history,
        ]);
    }

    /**
     * Process a payout claim: pay the shop everything it's currently owed,
     * mark those completed orders as remitted, and record the payout.
     * Admin only (you're the one handing over the money).
     */
    public function claim(Request $request, Shop $shop)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only an admin can process payouts'], 403);
        }

        $request->validate([
            'note' => 'nullable|string|max:255',
        ]);

        try {
            $payout = DB::transaction(function () use ($shop, $user, $request) {
                // Lock the rows we're about to pay so nothing sneaks in mid-claim
                $orders = Purchase::where('shop_id', $shop->id)
                    ->where('status', 'completed')
                    ->where('payout_status', 'unremitted')
                    ->lockForUpdate()
                    ->get();

                if ($orders->isEmpty()) {
                    throw new \Exception('Nothing to pay out — balance is ₱0.');
                }

                $amount = (float) $orders->sum(fn($p) => $p->shop_claim_amount * $p->quantity);

                $payout = Payout::create([
                    'shop_id'      => $shop->id,
                    'amount'       => $amount,
                    'order_count'  => $orders->count(),
                    'processed_by' => $user->id,
                    'note'         => $request->input('note'),
                ]);

                // Stamp each covered order so it can never be paid twice
                Purchase::whereIn('id', $orders->pluck('id'))->update([
                    'payout_status' => 'remitted',
                    'payout_id'     => $payout->id,
                ]);

                return $payout;
            });

            return response()->json([
                'success' => true,
                'message' => 'Payout of ₱' . number_format($payout->amount, 2) . ' recorded.',
                'payout'  => $payout->load('processedBy:id,name'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}