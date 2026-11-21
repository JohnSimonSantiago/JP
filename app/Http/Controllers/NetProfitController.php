<?php

namespace App\Http\Controllers;

use App\Models\ConsumablePurchase;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\LoungeSession;
use App\Models\Membership;
use App\Models\Payout;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Net profit = real money in − real money out, for a range of Manila days.
//
// Revenue (money in):
//   lounge bills  lounge_sessions.total_bill (completed)        on checked_out_at
//   buy time      consumable_purchases.amount, payment 'cash'   on created_at
//                 (cash or GCash; paid-with-Coins is NOT new money and is left out)
//   memberships   memberships.amount_paid (gifted = 0)          on approved_at
// Costs (money out):
//   expenses      expenses.amount (deleted ones left out)       on spent_at
//   shop payouts  payouts.amount                                on created_at (the day paid out)
// Shown separately: owed to shops = unremitted claims right now (same rule as the payout page).
class NetProfitController extends Controller
{
    private const TZ_OFFSET_HOURS = 8; // Manila is UTC+8 all year (no daylight saving)

    public function show(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date_format:Y-m-d',
            'to'   => 'nullable|date_format:Y-m-d',
        ]);

        $from = $request->from ? Carbon::parse($request->from, 'Asia/Manila')->startOfDay()->utc() : null;
        $to   = $request->to ? Carbon::parse($request->to, 'Asia/Manila')->endOfDay()->utc() : null;

        // Each source: [query, date column, amount SQL]
        $sources = [
            'lounge'      => [LoungeSession::where('status', 'completed'), 'checked_out_at', 'total_bill'],
            'buy_time'    => [ConsumablePurchase::where('payment_method', 'cash'), 'created_at', 'amount'],
            'memberships' => [Membership::whereNotNull('amount_paid'), 'approved_at', 'amount_paid'],
            'expenses'    => [Expense::query(), 'spent_at', 'amount'],
            'payouts'     => [Payout::query(), 'created_at', 'amount'],
        ];

        $inRange = function ($query, string $col) use ($from, $to) {
            if ($from) $query->where($col, '>=', $from);
            if ($to)   $query->where($col, '<=', $to);
            return $query;
        };

        // Totals: one SUM per source
        $totals = [];
        foreach ($sources as $key => [$query, $col, $amount]) {
            $totals[$key] = (float) $inRange(clone $query, $col)->sum($amount);
        }
        $revenue = $totals['lounge'] + $totals['buy_time'] + $totals['memberships'];
        $costs   = $totals['expenses'] + $totals['payouts'];

        // Chart buckets: by Manila day for up to ~2 months, by Manila month for longer / all time
        $spanDays = ($from && $to) ? $from->diffInDays($to) + 1 : null;
        $groupBy  = ($spanDays !== null && $spanDays <= 62) ? 'day' : 'month';
        $bucketSql = fn (string $col) => $groupBy === 'day'
            ? "DATE(DATE_ADD($col, INTERVAL " . self::TZ_OFFSET_HOURS . " HOUR))"
            : "DATE_FORMAT(DATE_ADD($col, INTERVAL " . self::TZ_OFFSET_HOURS . " HOUR), '%Y-%m')";

        $buckets = [];
        foreach ($sources as $key => [$query, $col, $amount]) {
            $rows = $inRange(clone $query, $col)
                ->whereNotNull($col)
                ->selectRaw($bucketSql($col) . " AS bucket, SUM($amount) AS total")
                ->groupBy('bucket')
                ->pluck('total', 'bucket');
            foreach ($rows as $bucket => $total) {
                $buckets[$bucket][$key] = (float) $total;
            }
        }
        ksort($buckets);
        $series = [];
        foreach ($buckets as $bucket => $v) {
            $in  = ($v['lounge'] ?? 0) + ($v['buy_time'] ?? 0) + ($v['memberships'] ?? 0);
            $out = ($v['expenses'] ?? 0) + ($v['payouts'] ?? 0);
            $series[] = ['bucket' => $bucket, 'revenue' => $in, 'costs' => $out, 'net' => $in - $out];
        }

        // Expenses per category (for the costs breakdown)
        $byCategory = $inRange(Expense::query(), 'spent_at')
            ->selectRaw('expense_category_id, SUM(amount) AS total')
            ->groupBy('expense_category_id')
            ->get();
        $names = ExpenseCategory::whereIn('id', $byCategory->pluck('expense_category_id'))->get(['id', 'name', 'color'])->keyBy('id');
        $expenseCategories = $byCategory->map(fn ($r) => [
            'name'  => $names[$r->expense_category_id]->name ?? 'Unknown',
            'color' => $names[$r->expense_category_id]->color ?? null,
            'total' => (float) $r->total,
        ])->sortByDesc('total')->values();

        // Owed to shops right now: completed, unremitted claims (same rule as PayoutController)
        $owed = (float) Purchase::where('status', 'completed')
            ->where('payout_status', 'unremitted')
            ->sum(DB::raw('COALESCE(shop_claim_amount, 0) * quantity'));

        // Paid memberships still missing an amount (run memberships:backfill-amounts)
        $missingAmounts = Membership::where('source', 'applied')
            ->whereIn('status', ['approved', 'expired'])
            ->whereNull('amount_paid')
            ->count();

        $money = fn ($v) => round((float) $v, 2);

        return response()->json([
            'success'  => true,
            'revenue'  => [
                'lounge'      => $money($totals['lounge']),
                'buy_time'    => $money($totals['buy_time']),
                'memberships' => $money($totals['memberships']),
                'total'       => $money($revenue),
            ],
            'costs'    => [
                'expenses'           => $money($totals['expenses']),
                'shop_payouts'       => $money($totals['payouts']),
                'total'              => $money($costs),
                'expense_categories' => $expenseCategories,
            ],
            'net_profit'                 => $money($revenue - $costs),
            'owed_to_shops'              => $money($owed),
            'group_by'                   => $groupBy,
            'series'                     => $series,
            'memberships_missing_amount' => $missingAmounts,
        ]);
    }
}
