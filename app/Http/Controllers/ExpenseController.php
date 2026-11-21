<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// Admin → Expenses. Every route using this controller is admin-only (admin middleware).
// Receipts are PRIVATE: saved on the "local" disk (storage/app/expense-receipts),
// which no URL can reach, and served only through receipt() below.
class ExpenseController extends Controller
{
    private const RECEIPT_DISK = 'local';
    private const RECEIPT_DIR  = 'expense-receipts';
    private const PER_PAGE     = 20;

    // ─── Categories ─────────────────────────────────────────────────────────────

    // All categories (active first), with how many expenses and templates use each one.
    //   expenses_count     = current expenses (what the page shows)
    //   all_expenses_count = including deleted ones (blocks deleting the category)
    //   templates_count    = recurring templates using it (blocks deleting; warned on archive)
    public function categories()
    {
        $categories = ExpenseCategory::withCount([
                'expenses',
                'expenses as all_expenses_count' => fn ($q) => $q->withTrashed(),
                'templates',
            ])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => $c->setAttribute(
                'can_delete',
                $c->all_expenses_count === 0 && $c->templates_count === 0,
            ));

        return response()->json(['success' => true, 'categories' => $categories]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100|unique:expense_categories,name',
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $category = ExpenseCategory::create($data + ['is_active' => true]);

        return response()->json(['success' => true, 'category' => $category], 201);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = ExpenseCategory::findOrFail($id);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100', Rule::unique('expense_categories', 'name')->ignore($category->id)],
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $category->update($data);

        return response()->json(['success' => true, 'category' => $category]);
    }

    // Archive / unarchive. Archived = hidden from "add expense"; old expenses keep it.
    public function toggleCategory($id)
    {
        $category = ExpenseCategory::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        return response()->json([
            'success'  => true,
            'category' => $category,
            'message'  => $category->is_active ? "{$category->name} is active again." : "{$category->name} archived.",
        ]);
    }

    // Only a category with NO expenses at all (deleted ones count too) can be deleted.
    public function destroyCategory($id)
    {
        $category = ExpenseCategory::findOrFail($id);

        if ($category->expenses()->withTrashed()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "{$category->name} has expenses, so it can't be deleted. Archive it instead.",
            ], 422);
        }

        if ($category->templates()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Used by a template — change or delete the template first.',
            ], 422);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => "{$category->name} deleted."]);
    }

    // ─── Expenses ───────────────────────────────────────────────────────────────

    // List with filters, newest spent_at first, 20 per page. Also returns the total
    // and the total per category for the SAME filters (all pages, not just this one).
    //   from / to   = "YYYY-MM-DD" Manila days (from the DateRangePicker); null = all time
    //   category_id = one category; search = text in the description
    public function index(Request $request)
    {
        $request->validate([
            'from'        => 'nullable|date_format:Y-m-d',
            'to'          => 'nullable|date_format:Y-m-d',
            'category_id' => 'nullable|integer',
            'search'      => 'nullable|string|max:100',
        ]);

        $filtered = $this->filteredQuery($request);

        $page = (clone $filtered)
            ->with(['category:id,name,color,is_active', 'creator:id,name', 'updater:id,name'])
            ->orderByDesc('spent_at')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE);

        $total = (clone $filtered)->sum('amount');

        $byCategory = (clone $filtered)
            ->selectRaw('expense_category_id, SUM(amount) AS total, COUNT(*) AS count')
            ->groupBy('expense_category_id')
            ->get();
        $categories = ExpenseCategory::whereIn('id', $byCategory->pluck('expense_category_id'))
            ->get(['id', 'name', 'color', 'is_active'])
            ->keyBy('id');
        $categoryTotals = $byCategory
            ->map(fn ($row) => [
                'id'        => $row->expense_category_id,
                'name'      => $categories[$row->expense_category_id]->name ?? 'Unknown',
                'color'     => $categories[$row->expense_category_id]->color ?? null,
                'is_active' => (bool) ($categories[$row->expense_category_id]->is_active ?? false),
                'total'     => number_format((float) $row->total, 2, '.', ''),
                'count'     => (int) $row->count,
            ])
            ->sortByDesc(fn ($c) => (float) $c['total'])
            ->values();

        return response()->json([
            'success'         => true,
            'expenses'        => $page->items(),
            'pagination'      => [
                'current_page' => $page->currentPage(),
                'last_page'    => $page->lastPage(),
                'per_page'     => $page->perPage(),
                'total'        => $page->total(),
            ],
            'total_amount'    => number_format((float) $total, 2, '.', ''),
            'category_totals' => $categoryTotals,
        ]);
    }

    // Summary cards. FIXED periods in Manila time; they do NOT follow the list filters.
    //   this_month           = the 1st of this month up to the end of today
    //   last_month           = the whole previous month
    //   last_month_same_days = last month from the 1st up to the same day number
    //                          (capped at its last day), so the comparison is fair
    //   change_percent       = this_month vs last_month_same_days; null when that is ₱0
    public function summary()
    {
        $now        = Carbon::now('Asia/Manila');
        $thisStart  = $now->copy()->startOfMonth();
        $thisEnd    = $now->copy()->endOfDay();
        $lastStart  = $thisStart->copy()->subMonthNoOverflow();
        $lastEnd    = $lastStart->copy()->endOfMonth();
        $sameDayEnd = $lastStart->copy()->day(min($now->day, $lastStart->daysInMonth))->endOfDay();

        // One SUM per period, in the database (spent_at is stored in UTC)
        $sum = fn (Carbon $from, Carbon $to) => (float) Expense::whereBetween('spent_at', [
            $from->copy()->utc(), $to->copy()->utc(),
        ])->sum('amount');

        $thisMonth = $sum($thisStart, $thisEnd);
        $lastMonth = $sum($lastStart, $lastEnd);
        $lastSame  = $sum($lastStart, $sameDayEnd);

        $money = fn (float $v) => number_format($v, 2, '.', '');

        return response()->json([
            'success'              => true,
            'this_month'           => $money($thisMonth),
            'last_month'           => $money($lastMonth),
            'last_month_same_days' => $money($lastSame),
            'change_percent'       => $lastSame > 0 ? round(($thisMonth - $lastSame) / $lastSame * 100, 1) : null,
            'labels'               => [
                'this_month' => $thisStart->format('F Y'),                                       // "November 2026"
                'last_month' => $lastStart->format('F Y'),                                       // "October 2026"
                'compare'    => $lastStart->format('M') . ' 1–' . $sameDayEnd->day,              // "Oct 1–21"
                'today'      => $thisStart->format('M') . ' 1–' . $now->day,                     // "Nov 1–21"
            ],
        ]);
    }

    private function filteredQuery(Request $request)
    {
        $q = Expense::query(); // soft-deleted expenses are left out automatically

        // A Manila calendar day, stored as UTC (see CLAUDE.md)
        if ($request->from) {
            $q->where('spent_at', '>=', Carbon::parse($request->from, 'Asia/Manila')->startOfDay()->utc());
        }
        if ($request->to) {
            $q->where('spent_at', '<=', Carbon::parse($request->to, 'Asia/Manila')->endOfDay()->utc());
        }
        if ($request->category_id) {
            $q->where('expense_category_id', $request->category_id);
        }
        if ($request->search) {
            $q->where('description', 'like', '%' . $request->search . '%');
        }

        return $q;
    }

    public function store(Request $request)
    {
        $data = $this->validateExpense($request);

        $category = ExpenseCategory::findOrFail($data['expense_category_id']);
        if (!$category->is_active) {
            return response()->json([
                'success' => false,
                'message' => "{$category->name} is archived. Pick an active category.",
            ], 422);
        }

        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store(self::RECEIPT_DIR, self::RECEIPT_DISK)
            : null;

        try {
            $expense = Expense::create([
                'expense_category_id' => $category->id,
                'expense_template_id' => $data['expense_template_id'] ?? null, // "Log it"
                'amount'              => $data['amount'],
                'description'         => $data['description'] ?? null,
                'spent_at'            => $this->manilaToUtc($data['spent_at']),
                'receipt_path'        => $receiptPath,
                'created_by'          => $request->user()->id,
            ]);
        } catch (\Throwable $e) {
            if ($receiptPath) Storage::disk(self::RECEIPT_DISK)->delete($receiptPath); // no orphan files
            throw $e;
        }

        return response()->json([
            'success' => true,
            'expense' => $expense->load(['category:id,name,color,is_active', 'creator:id,name']),
        ], 201);
    }

    // Edit. "receipt" replaces the photo; "remove_receipt=1" removes it.
    // Either way the old file is deleted. An expense may keep its archived category,
    // but can't be moved INTO a different archived one.
    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $data = $this->validateExpense($request) + $request->validate([
            'remove_receipt' => 'nullable|boolean',
        ]);

        $category = ExpenseCategory::findOrFail($data['expense_category_id']);
        if (!$category->is_active && $category->id !== $expense->expense_category_id) {
            return response()->json([
                'success' => false,
                'message' => "{$category->name} is archived. Pick an active category.",
            ], 422);
        }

        $oldPath = $expense->receipt_path;
        $newPath = $oldPath;
        if ($request->hasFile('receipt')) {
            $newPath = $request->file('receipt')->store(self::RECEIPT_DIR, self::RECEIPT_DISK);
        } elseif (!empty($data['remove_receipt'])) {
            $newPath = null;
        }

        try {
            $expense->update([
                'expense_category_id' => $category->id,
                // keep the template link unless the form sends one (or sends it empty)
                'expense_template_id' => array_key_exists('expense_template_id', $data)
                    ? $data['expense_template_id']
                    : $expense->expense_template_id,
                'amount'              => $data['amount'],
                'description'         => $data['description'] ?? null,
                'spent_at'            => $this->manilaToUtc($data['spent_at']),
                'receipt_path'        => $newPath,
                'updated_by'          => $request->user()->id,
            ]);
        } catch (\Throwable $e) {
            if ($newPath && $newPath !== $oldPath) Storage::disk(self::RECEIPT_DISK)->delete($newPath);
            throw $e;
        }

        // Saved: now the replaced/removed photo can go
        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk(self::RECEIPT_DISK)->delete($oldPath);
        }

        return response()->json([
            'success' => true,
            'expense' => $expense->fresh()->load(['category:id,name,color,is_active', 'creator:id,name', 'updater:id,name']),
        ]);
    }

    // Soft delete. The receipt file is kept, so the expense can be restored later.
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return response()->json(['success' => true, 'message' => 'Expense deleted.']);
    }

    // The receipt photo, for admins only (the file has no public URL).
    public function receipt($id)
    {
        $expense = Expense::findOrFail($id);
        $path = $expense->receipt_path;

        if (!$path || !Storage::disk(self::RECEIPT_DISK)->exists($path)) {
            return response()->json(['success' => false, 'message' => 'No receipt for this expense.'], 404);
        }

        return Storage::disk(self::RECEIPT_DISK)->response($path, null, [
            'Cache-Control' => 'private, no-store', // never kept by Cloudflare or shared caches
        ]);
    }

    // ─── Recurring templates ────────────────────────────────────────────────────
    // Saved presets for monthly bills. They NEVER create expenses by themselves;
    // "Log it" pre-fills the Add Expense form and links the expense to the template.

    public function templates()
    {
        $templates = ExpenseTemplate::with('category:id,name,color,is_active')
            ->withCount('expenses')
            ->orderByDesc('is_active')
            ->orderBy('due_day')
            ->orderBy('id')
            ->get();

        return response()->json(['success' => true, 'templates' => $templates]);
    }

    public function storeTemplate(Request $request)
    {
        $data = $this->validateTemplate($request);

        $category = ExpenseCategory::findOrFail($data['expense_category_id']);
        if (!$category->is_active) {
            return response()->json([
                'success' => false,
                'message' => "{$category->name} is archived. Pick an active category.",
            ], 422);
        }

        $template = ExpenseTemplate::create($data + ['is_active' => true]);

        return response()->json(['success' => true, 'template' => $template->load('category:id,name,color,is_active')], 201);
    }

    // A template may keep its archived category, but can't be moved INTO a different archived one.
    public function updateTemplate(Request $request, $id)
    {
        $template = ExpenseTemplate::findOrFail($id);
        $data = $this->validateTemplate($request);

        $category = ExpenseCategory::findOrFail($data['expense_category_id']);
        if (!$category->is_active && $category->id !== $template->expense_category_id) {
            return response()->json([
                'success' => false,
                'message' => "{$category->name} is archived. Pick an active category.",
            ], 422);
        }

        $template->update($data);

        return response()->json(['success' => true, 'template' => $template->fresh()->load('category:id,name,color,is_active')]);
    }

    public function toggleTemplate($id)
    {
        $template = ExpenseTemplate::findOrFail($id);
        $template->update(['is_active' => !$template->is_active]);

        return response()->json([
            'success'  => true,
            'template' => $template,
            'message'  => $template->is_active ? 'Template turned on.' : 'Template turned off.',
        ]);
    }

    // Past expenses are kept; they just lose the link (nullOnDelete).
    public function destroyTemplate($id)
    {
        $template = ExpenseTemplate::findOrFail($id);
        $count = $template->expenses()->withTrashed()->count();
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => $count > 0
                ? "Template deleted. Its {$count} past expense(s) are kept."
                : 'Template deleted.',
        ]);
    }

    // "Due this month" (Manila): every ACTIVE template with its status this month.
    //   logged  = an expense with this template_id has spent_at this month (deleted ones don't count)
    //   overdue = not logged and the due date has passed (the due day itself is not overdue)
    //   due     = not logged, not yet past due
    // due_day past the month's last day = the last day (31 in February = Feb 28/29).
    // Order: overdue first, then due, then logged; by due day inside each group.
    public function dueThisMonth()
    {
        $now        = Carbon::now('Asia/Manila');
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();
        $lastDay    = $monthStart->daysInMonth;

        $templates = ExpenseTemplate::active()
            ->with('category:id,name,color,is_active')
            ->get();

        // One query: what was logged this month, per template
        $logged = Expense::whereIn('expense_template_id', $templates->pluck('id'))
            ->whereBetween('spent_at', [$monthStart->copy()->utc(), $monthEnd->copy()->utc()])
            ->selectRaw('expense_template_id, SUM(amount) AS total, COUNT(*) AS count, MAX(spent_at) AS last_spent_at')
            ->groupBy('expense_template_id')
            ->get()
            ->keyBy('expense_template_id');

        $order = ['overdue' => 0, 'due' => 1, 'logged' => 2];

        $items = $templates->map(function ($t) use ($monthStart, $lastDay, $now, $logged) {
            $dueDay  = min($t->due_day, $lastDay);
            $dueDate = $monthStart->copy()->day($dueDay);
            $log     = $logged[$t->id] ?? null;

            $status = $log ? 'logged' : ($now->copy()->startOfDay()->gt($dueDate) ? 'overdue' : 'due');

            return [
                'id'                => $t->id,
                'description'       => $t->description,
                'default_amount'    => $t->default_amount,
                'due_day'           => $t->due_day,
                'due_date'          => $dueDate->toDateString(),       // Manila date, e.g. "2026-11-25"
                'due_label'         => $dueDate->format('M j'),        // "Nov 25"
                'status'            => $status,
                'category'          => $t->category,
                'category_archived' => !($t->category->is_active ?? false),
                'logged_amount'     => $log ? number_format((float) $log->total, 2, '.', '') : null,
                'logged_count'      => $log ? (int) $log->count : 0,
                'logged_at'         => $log ? Carbon::parse($log->last_spent_at, 'UTC')->toIso8601String() : null,
                '_due_day'          => $dueDay,
            ];
        })
            ->sortBy(fn ($i) => [$order[$i['status']], $i['_due_day'], $i['id']])
            ->values()
            ->map(function ($i) { unset($i['_due_day']); return $i; });

        return response()->json([
            'success' => true,
            'month'   => $monthStart->format('F Y'),
            'items'   => $items,
        ]);
    }

    private function validateTemplate(Request $request): array
    {
        return $request->validate([
            'expense_category_id' => 'required|integer|exists:expense_categories,id',
            'description'         => 'nullable|string|max:255',
            'default_amount'      => 'nullable|numeric|gt:0|max:99999999.99',
            'due_day'             => 'required|integer|between:1,31',
        ]);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'expense_category_id' => 'required|integer|exists:expense_categories,id',
            'expense_template_id' => 'nullable|integer|exists:expense_templates,id',
            'amount'              => 'required|numeric|gt:0|max:99999999.99',
            'description'         => 'nullable|string|max:255',
            'spent_at'            => 'required|date', // Manila date & time from the form, e.g. "2026-11-21T14:30"
            'receipt'             => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120', // 5 MB
        ]);
    }

    // The form sends Manila time; the database keeps UTC.
    private function manilaToUtc(string $value): Carbon
    {
        return Carbon::parse($value, 'Asia/Manila')->utc();
    }
}
