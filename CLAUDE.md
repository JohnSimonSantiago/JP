# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Level Lounge (JP)

Web platform for Level Lounge, a gaming lounge in Laoag City. It handles lounge sessions (time in/out), memberships, consumable time, the shop/marketplace (MyShop), loyalty, points, leaderboards, trades, bets, and the coins wheel.
The Expo/React Native mobile app (LevelUpApp, a separate repo) uses this same Laravel backend, so API changes must not break it.

## Stack
- Laravel 10 + MySQL, running in Docker (containers `Jp-Php`, `Jp-Web`)
- Vue 3 (Options API), Vite, Tailwind CSS, PrimeVue, PrimeIcons, Axios
- Auth: Sanctum bearer token from `localStorage.getItem("auth-token")`
- Production: DigitalOcean droplet "Starlight" behind Cloudflare (levelinglounge.com)
- Timezone: data is stored in UTC, but "a day" means a Manila calendar day (`Carbon::parse($date, 'Asia/Manila')->startOfDay()->utc()`)

## Commands (local dev, WSL)
- After controller/route changes: `php artisan route:clear && docker restart Jp-Php`
- After Vue changes: `npm run build`
- Migrations: `docker exec Jp-Php php artisan migrate`
- Vite dev server (hot reload): `npm run dev`
- Tests: `docker exec Jp-Php php artisan test` (single test: `--filter=TestName`). Only the Laravel example tests exist, so most verification is manual in the browser.
- Formatter: `docker exec Jp-Php ./vendor/bin/pint` (Laravel Pint is installed but not enforced)

## Architecture
- **SPA shell:** `routes/web.php` serves `landing` at `/` and a catch-all that returns the `app` view. Vue Router (`resources/js/routes.js`) owns all page routing. Add new web routes ABOVE the catch-all.
- **Frontend:** `resources/js/app.js` mounts Vue with Pinia, PrimeVue (aura-light-green theme, ToastService), and v-calendar. Every page is wrapped in `Layout/Layout1.vue`. `@` is an alias for `resources/js`. Pages live in `resources/js/pages/`; MyShop tabs are in `pages/MyShop/` and reports are in `pages/reports/`.
- **Roles:** `users.role` is `admin`, `staff`, `shop_owner`, or a regular user. Backend middleware aliases: `admin` and `staff_or_admin` (`app/Http/Middleware`). Frontend guards in `routes.js` (`requiresShopOwner`, etc.) read `localStorage.user` and only affect the UI. The real check is always the backend middleware.
- **API layout (`routes/api.php`):** public routes first, then `auth:sanctum` groups, then nested `admin` and `staff_or_admin` (`/lounge/*`) groups. Point-shop routes sit in their own block at the bottom.
- **Scheduler (`app/Console/Kernel.php`):** expires memberships daily, sends lounge push reminders every minute (hourly reminder and the warning before the next 30-min block), and runs `lounge:notify-consumable` (warnings when the consumable balance is low). Push notifications go through `App\Services\PushNotificationService` to the user's `push_token` (from the mobile app).
- **Key business logic locations:** lounge billing is in `LoungeController::calculateBill()`, shop discounts are in `Shop::calculateDiscount()` (`app/Models/Shop.php`), and lounge prices come from the `LoungePricing` model (edited on `LoungeSettings.vue`).

## Deploy (server)
- The server pulls only from GitLab (on the server, `origin` points to GitLab). Locally, `origin` is GitHub and `gitlab` is GitLab: push to both (`git push origin main && git push gitlab main`).
- `docker/nginx/default.conf` is git-tracked and holds the server-specific config: `git stash` → `git pull` → `git stash pop`, then `docker restart Jp-Web`.

## Rules
- Always read the existing file before editing. Never guess the structure.
- Build backend-first: migration → model → controller → route → Vue. Do one piece at a time and confirm it works before moving on.
- In `routes/api.php`, wildcard routes must come AFTER literal routes (otherwise a string like "walk-in-order" gets treated as an ID).
- New columns: check `$fillable` and `$casts` (missing entries fail silently).
- `Shop::calculateDiscount()` is the single source of truth for discount math. Extend it, don't duplicate it.
- Walk-in/cash orders never enter the discount or payout system.
- Store `receipt_number` on sessions when they're created (needed for reprints).
- `??` doesn't catch "0" strings; use `> 0` checks when summing money fields.

## Lounge billing (LoungeController)
- Walk-in: 30-min blocks with a 5-min grace period, bundles, and a day-rate cap (`calculateBill`)
- Level 1 members: pay hourly or use consumable minutes (the balance can go negative)
- Level 2/3 members: free
- One active session per member (locked in a DB transaction)

## RFID time in/out (in progress)
- ESP32-S3 + RFID reader on USB (COM5, 115200 baud), already running its own firmware that prints `Card: 43 A8 7D 1A` on each tap
- `LoungeSessionsDashboard.vue` reads it with Chrome Web Serial. One tap = time in if outside, time out (opens the bill window) if inside. Unknown card → link modal.
- `users.rfid_uid` (unique, normalized like "43A87D1A"); endpoints `POST /api/lounge/rfid/lookup` and `/rfid/link`

## Communication
- Keep explanations short and non-technical, with analogies. Give a clear recommendation, not a list of open-ended options.
