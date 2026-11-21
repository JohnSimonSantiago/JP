<?php

namespace App\Console\Commands;

use App\Models\Membership;
use Carbon\Carbon;
use Illuminate\Console\Command;

// One-time: fill memberships.amount_paid / approved_at for rows made before those
// columns existed. Safe to run more than once (only touches rows with no amount yet).
//
//   php artisan memberships:backfill-amounts        # dry run: shows what it would set
//   php artisan memberships:backfill-amounts --go   # save it
//
// How a membership is classified:
//   source = gifted                           -> ₱0 (gifts are free)
//   source = applied, approved or expired     -> ₱2,500 (Level 2) / ₱3,000 (Level 3)
//   source = applied, pending or rejected     -> left empty (nothing was paid)
// approved_at = start_date (approval sets start_date to that day).
// FLAGGED for you to check (still filled in): an applied membership whose length isn't
// the usual 1 month that approval sets, or one with no start date.
class BackfillMembershipAmounts extends Command
{
    protected $signature = 'memberships:backfill-amounts {--go : Save the amounts (without this it is a dry run)}';
    protected $description = 'Fill amount_paid / approved_at on old memberships (dry run unless --go)';

    // The prices paid memberships have always had (since paid applications began, Aug 8 2026)
    private const PAID_PRICES = ['level_2' => 2500.00, 'level_3' => 3000.00];

    public function handle(): int
    {
        $rows = Membership::with('user:id,name')
            ->whereNull('amount_paid')
            ->orderBy('id')
            ->get();

        $plan = [];
        $flags = [];
        $skipped = 0;

        foreach ($rows as $m) {
            $paidStatus = in_array($m->status, ['approved', 'expired']);

            if ($m->source === 'gifted') {
                if (!$paidStatus) { $skipped++; continue; }
                $amount = 0.00;
            } elseif ($m->source === 'applied') {
                if (!$paidStatus) { $skipped++; continue; } // pending / rejected: nothing paid
                if (!isset(self::PAID_PRICES[$m->type])) {
                    $flags[] = [$m->id, $m->user->name ?? "user {$m->user_id}", $m->type, 'unknown type, not filled'];
                    continue;
                }
                $amount = self::PAID_PRICES[$m->type];

                $days = ($m->start_date && $m->end_date) ? $m->start_date->diffInDays($m->end_date) : null;
                if ($days === null) {
                    $flags[] = [$m->id, $m->user->name ?? "user {$m->user_id}", $m->type, 'no start/end date'];
                } elseif ($days < 28 || $days > 31) {
                    $flags[] = [$m->id, $m->user->name ?? "user {$m->user_id}", $m->type, "lasts {$days} days (usual: 1 month)"];
                }
            } else {
                $flags[] = [$m->id, $m->user->name ?? "user {$m->user_id}", $m->type, "unknown source \"{$m->source}\", not filled"];
                continue;
            }

            $plan[] = [
                'membership'  => $m,
                'amount'      => $amount,
                'approved_at' => $m->start_date ? Carbon::parse($m->start_date->toDateString(), 'UTC') : $m->created_at,
            ];
        }

        $go = (bool) $this->option('go');
        $this->line($go ? '<info>Saving.</info>' : '<comment>DRY RUN: nothing will be changed.</comment>');

        $paid = collect($plan)->filter(fn ($p) => $p['amount'] > 0);
        $this->table(['', 'count', 'amount'], [
            ['Gifted -> ₱0', collect($plan)->where('amount', 0)->count(), '₱0.00'],
            ['Paid -> standard price', $paid->count(), '₱' . number_format($paid->sum('amount'), 2)],
            ['Pending / rejected (left empty)', $skipped, ''],
            ['Flagged for you to check', count($flags), ''],
        ]);

        if ($paid->isNotEmpty()) {
            $this->line('Paid memberships (assumed standard price):');
            $this->table(['#', 'member', 'type', 'status', 'amount', 'approved on'], $paid->map(fn ($p) => [
                $p['membership']->id,
                $p['membership']->user->name ?? "user {$p['membership']->user_id}",
                $p['membership']->type,
                $p['membership']->status,
                '₱' . number_format($p['amount'], 2),
                $p['approved_at']->toDateString(),
            ])->all());
        }
        if ($flags) {
            $this->warn('Flagged (check these; fix an amount in Membership Management if needed):');
            $this->table(['#', 'member', 'type', 'why'], $flags);
        }

        if (!$go) {
            $this->line('Run with --go to save.');
            return self::SUCCESS;
        }

        foreach ($plan as $p) {
            $p['membership']->update([
                'amount_paid' => $p['amount'],
                'approved_at' => $p['approved_at'],
            ]);
        }
        $this->info('Saved ' . count($plan) . ' membership(s).');

        return self::SUCCESS;
    }
}
