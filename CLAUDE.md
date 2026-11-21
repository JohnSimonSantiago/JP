# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Level Lounge (JP)

Web platform for Level Lounge, a gaming lounge in Laoag City. It handles lounge sessions (time in/out, RFID/QR scanning, a kiosk screen), memberships, consumable time, the shop/marketplace (MyShop), loyalty, points, leaderboards, trades, bets, the Coins wheel, and admin expenses.
The Expo/React Native mobile app (LevelUpApp, a separate repo) uses this same Laravel backend, so API changes must not break it.

## Stack
- Laravel 10 + MySQL, running in Docker (containers `Jp-Php`, `Jp-Web`)
- Vue 3 (Options API), Vite, Tailwind CSS, PrimeVue, PrimeIcons, Axios
- Auth: Sanctum bearer token from `localStorage.getItem("auth-token")`
- Production: DigitalOcean droplet "Starlight" behind Cloudflare (levelinglounge.com)
- Timezone: data is stored in UTC, but "a day" means a Manila calendar day (`Carbon::parse($date, 'Asia/Manila')->startOfDay()->utc()`)

## Commands (local dev, WSL)
- Local site: **http://localhost:8161** (`Jp-Web`). Port 80 on WSL is an unrelated Apache.
- Docker Desktop must be running with WSL integration on, or `docker` isn't found.
- After controller/route changes: `php artisan route:clear && docker restart Jp-Php`
- After Vue changes: `npm run build`. A leftover `public/hot` file (from `npm run dev`) makes the local page load the Vite dev server instead of the build; delete it or run `npm run dev`.
- Migrations: `docker exec Jp-Php php artisan migrate`
- Vite dev server (hot reload): `npm run dev`
- Tests: `docker exec Jp-Php php artisan test` (single test: `--filter=TestName`). Only the Laravel example tests exist, so verification is manual (browser) or `tinker` scripts inside a `DB::beginTransaction()` / `DB::rollBack()`.
- Formatter: `docker exec Jp-Php ./vendor/bin/pint` (Laravel Pint is installed but not enforced)

## Architecture
- **SPA shell:** `routes/web.php` serves `landing` at `/` and a catch-all (`/{vue?}`) that returns the `app` view. Vue Router (`resources/js/routes.js`) owns all page routing. Add new web routes ABOVE the catch-all. The catch-all also answers unknown `/api/...` and `/storage/...` GETs with the app's HTML (status 200), so a 200 there doesn't mean a file or route exists.
- **Frontend:** `resources/js/app.js` mounts Vue with Pinia, PrimeVue (aura-light-green theme, ToastService, global `Chart`), and v-calendar. Pages wrap themselves in `<Layout>` (`Layout/Layout1.vue`); `LoungeDisplay.vue` doesn't (kiosk, no menus). `@` is an alias for `resources/js`. Pages live in `resources/js/pages/`; MyShop tabs are in `pages/MyShop/` and reports in `pages/reports/`. Shared pieces: `components/DateRangePicker.vue` (emits `{from, to}` as `YYYY-MM-DD` or nulls; optional `initial-mode`), `components/CoinIcon.vue` / `CoinAmount.vue`, `utils/coins.js`, `lounge/scanner.js`, `config/features.js`.
- **Roles:** `users.role` is `admin`, `staff`, `shop_owner`, `kiosk`, or a regular user. Backend middleware aliases: `admin`, `staff_or_admin`, and `kiosk_or_staff` (`app/Http/Middleware`). Frontend guards in `routes.js` read `localStorage.user` and only affect the UI; the real check is always the backend middleware. The `kiosk` role is the "Lounge Kiosk" account used by paired kiosk screens; hide it from people lists with `User::notKiosk()` (not a global scope: Sanctum must still find it).
- **API layout (`routes/api.php`):** public routes first, then `auth:sanctum` groups, then nested `admin` and `staff_or_admin` (`/lounge/*`) groups, then a small `kiosk_or_staff` group (`POST /lounge/scan` only). Point-shop routes sit in their own block at the bottom.
- **Scheduler (`app/Console/Kernel.php`):** expires memberships daily, sends lounge push reminders every minute (hourly reminder and the warning before the next 30-min block), and runs `lounge:notify-consumable` (warnings when the consumable balance is low). Push notifications go through `App\Services\PushNotificationService` to the user's `push_token` (from the mobile app).
- **Key business logic locations:** lounge billing is in `LoungeController::calculateBill()`; check-in and checkout run through `startSession()` and `runCheckout()` (the endpoints and `/lounge/scan` all use them); the group override split is `splitWholePesos()`; shop discounts are in `Shop::calculateDiscount()` (`app/Models/Shop.php`); lounge prices and the standard membership prices come from the `LoungePricing` model (edited on `LoungeSettings.vue`); net profit is `NetProfitController`.
- **Coins = `users.cash`:** the wallet is shown as "Coins" (1 Coin = ₱1) everywhere in the web UI, with `CoinAmount` / `formatCoins()`. Display only: the column, model attribute and API keys stay `cash` (the mobile app depends on them). Real-money amounts (bills, rates, reports, owner earnings) stay ₱.
- **Feature switches:** `resources/js/config/features.js`. `RECEIPTS_ENABLED = false` turns off the 80mm check-in slip printing (CN numbers are still reserved).

## Deploy (server)
- The server pulls only from GitLab (on the server, `origin` points to GitLab). Locally, `origin` is GitHub and `gitlab` is GitLab: push to both (`git push origin main && git push gitlab main`).
- `docker/nginx/default.conf` is git-tracked and holds the server-specific config: `git stash` → `git pull` → `git stash pop`, then `docker exec Jp-Web nginx -t` before `docker restart Jp-Web`.
- After pulling: `docker exec Jp-Php php artisan migrate` (if there are migrations), `php artisan route:clear && docker restart Jp-Php`, `npm run build`.

## Rules
- Always read the existing file before editing. Never guess the structure.
- Build backend-first: migration → model → controller → route → Vue. Do one piece at a time and confirm it works before moving on.
- In `routes/api.php`, wildcard routes must come AFTER literal routes (otherwise a string like "walk-in-order" gets treated as an ID). New `{id}` routes also get `->whereNumber('id')`.
- New columns: check `$fillable` and `$casts` (missing entries fail silently). `decimal:2` casts come back as strings ("1500.50"); convert with `Number()` in Vue.
- `Shop::calculateDiscount()` is the single source of truth for discount math. Extend it, don't duplicate it.
- Shop counter (walk-in) orders paid in **cash** never enter the discount or payout system (the cash stays with the shop). Counter orders paid with a member's **Coins** do, exactly like online Coins orders (`calculateDiscount()`, frozen discount slices, `shop_claim_amount`, `unremitted`).
- Store `receipt_number` on sessions when they're created (needed for reprints).
- `??` doesn't catch "0" strings; use `> 0` checks when summing money fields.
- Private files (expense receipts, valid IDs) go on the `local` disk (`storage/app/...`) and are served by an authenticated route; never on the `public` disk. Replacing/removing a file deletes the old one only after the database save succeeds.
- Money splits are always whole pesos and must add up exactly (see `splitWholePesos()`).

## Lounge billing (LoungeController)
- Walk-in: 30-min blocks with a 5-min grace period, bundles, and a day-rate cap (`calculateBill`)
- Level 1 members: ALWAYS consumable minutes, even at 0 or a negative balance (enforced in `startSession()`, so the web and the mobile kiosk behave the same). The balance can go negative at checkout; buying time (`buyConsumableTime`) adds minutes, so it pays a negative balance off first.
- Level 2/3 members: free
- One active session per member (locked in a DB transaction)
- Group override total: split in whole pesos, proportional to each person's bill (ties to the earliest time-in). Without an override, each person pays their own bill.
- Staff can move a person between groups / solo without checking out (`assignGroup`): only `group_id` changes, and an old group left with one person is dissolved.

## Lounge scanning (RFID + QR) and the kiosk
- Inputs: the ESP32-S3 RFID reader on USB (COM5, 115200 baud, prints `Card: 43 A8 7D 1A`), read with Chrome Web Serial; and the Clabel T27H QR scanner, which types the QR text + Enter into a hidden, self-refocusing input. Member QR codes are `levelup://user/{id}` (not signed).
- `resources/js/lounge/scanner.js` is the one shared scanner (both inputs, 5s same-code debounce, busy lock) used by `/lounge` (`LoungeSessionsDashboard.vue`) and `/lounge/display` (`LoungeDisplay.vue`).
- `POST /api/lounge/scan` makes the whole time in/out decision on the server (`LoungeController::scan`): `detectScanCode()` picks QR vs card, a 10s server-side duplicate guard, time in (saves the CN receipt number) or time out (solo). A time out that would leave a consumable balance below zero is NEVER done by a scan: the kiosk gets `see_staff` (reason `short`, session stays active); staff get `short`, which opens the "Not enough time" window on `/lounge` (Buy & Time Out / Time Out Without Buying). Old hourly sessions get `see_staff`.
- Unknown RFID card on `/lounge` → Link Card window (`users.rfid_uid`, unique, normalized like "43A87D1A"; `POST /lounge/rfid/link`).
- Kiosk: `/lounge/display` has no route guard. Staff pair a screen once ("Set up this screen as a kiosk" → `POST /lounge/kiosk/pair`), which stores `kiosk-token` in localStorage; the screen then runs with no staff login. A kiosk token can only call `/lounge/scan`. Unpair all from Lounge Settings (`/lounge/kiosk/revoke-all`); a revoked token shows "Kiosk not paired — please see staff". Only one tab can open the RFID reader; the kiosk owns it.
- `/lounge` refreshes every 10s (kiosk scans happen elsewhere) and still sends results to the display over BroadcastChannel (`level-lounge-display`) when both are open in the same Chrome.

## Expenses (admin only)
- Page `/admin/expenses` (`AdminExpenses.vue`), API under `/api/admin/expenses` (`ExpenseController`).
- Tables: `expense_categories` (archive with `is_active` instead of deleting once used), `expenses` (soft deletes, `spent_at` in UTC, `created_by`/`updated_by` nullable with nullOnDelete, `expense_template_id`), `expense_templates` (recurring presets: `due_day` 1–31, nullable `default_amount`). Starter categories: `ExpenseCategorySeeder` (6).
- A category can't be deleted while it has expenses (soft-deleted ones count) or templates. New expenses/templates can't use an archived category; existing ones keep theirs.
- Receipts: private, `storage/app/expense-receipts`, served by `GET /admin/expenses/{id}/receipt` (the page loads it as a blob with the token).
- Totals: the list returns the total and per-category totals for the filters (database SUM/GROUP BY); `/summary` gives fixed-period cards (this month so far vs last month up to the same day); `/due-this-month` gives template status (logged / due / overdue, `due_day` capped at the month's last day). Templates never create expenses by themselves.

## Valid IDs (private)
- Uploaded at signup (JPG/PNG only), stored on the `local` disk at `storage/app/valid_ids/...` (`users.valid_id` holds the path). All rules live in `App\Services\ValidIdFiles`.
- Served by `GET /api/admin/users/{user}/valid-id` (admin) and `GET /api/user/valid-id` (own ID only), `Cache-Control: private, no-store`. The web admin pages load them as a blob via `utils/validId.js`; the mobile app sends the bearer token with the image request.
- Deleting a user deletes their file. `php artisan valid-ids:make-private` (dry run by default; `--go`, `--delete-orphans`) moved the old public files; it stays for checking that nothing is on the public disk.

## Revenue and net profit (what counts as real money)
- **Revenue** = lounge bills (`lounge_sessions.total_bill`, completed, on `checked_out_at`) + buy time paid in cash/GCash (`consumable_purchases.amount`, `payment_method = 'cash'`, on `created_at`) + memberships (`memberships.amount_paid`, on `approved_at`).
- **Not revenue:** buy time paid with Coins (`payment_method = 'balance'`). Coins are never bought directly (they come from the membership credit, wheel prizes and refunds), so counting them again would double-count. Shop orders paid with Coins are a cost (payouts), not income. Shop cash counter orders belong to the shop owner. Points, stars, bets, trades and loyalty never involve money.
- **Costs** = expenses (`expenses.amount`, on `spent_at`) + shop payouts (`payouts.amount`, on the day paid out). "Owed to shops" (completed, unremitted claims) is shown separately, not as a cost.
- Every report must use these rules: the General Report's buy time revenue is cash only, and Statistics → Net Profit (`GET /admin/reports/net-profit`) uses them all.

## Memberships
- Monthly: approval sets a 1-month period; renewing is a new application after it ends. Gifted memberships have `source = 'gifted'`.
- `amount_paid` is frozen when approved ("Amount received", defaulting to the standard price in Lounge Settings, also used when the app approves without an amount); gifts save 0. Never compute membership revenue from a hardcoded price. Admins can correct a paid amount (`POST /admin/memberships/{id}/amount`).
- `php artisan memberships:backfill-amounts` (dry run by default, `--go`) filled old rows; it's safe to rerun.

## Communication
- Keep explanations short and non-technical, with analogies. Give a clear recommendation, not a list of open-ended options.
