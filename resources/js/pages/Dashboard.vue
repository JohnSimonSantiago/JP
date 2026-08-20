<template>
    <Layout>
        <div class="max-w-4xl mx-auto">
            <!-- Welcome Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    Welcome back, {{ user.name }}!
                </h1>
                <p class="text-sm text-gray-500">
                    Member since {{ formatDate(user.created_at) }}
                </p>
            </div>

            <!-- Session QR — shown for both time-in and time-out -->
            <div
                v-if="user.id"
                class="bg-white rounded-xl shadow-sm border border-indigo-100 mb-6 p-6 flex flex-col items-center"
            >
                <div class="flex items-center gap-2 mb-4">
                    <i class="pi pi-qrcode text-indigo-600"></i>
                    <p class="font-semibold text-gray-800">
                        {{ loungeSession ? "Time-Out Code" : "Time-In Code" }}
                    </p>
                </div>
                <div class="p-3 bg-white rounded-xl border border-gray-100">
                    <QrcodeVue
                        :value="`levelup://user/${user.id}`"
                        :size="180"
                        level="M"
                    />
                </div>
                <p class="text-sm font-medium text-gray-700 mt-4">
                    {{
                        loungeSession
                            ? "Show this to staff to time out."
                            : "Show this to staff to time in."
                    }}
                </p>
            </div>

            <!-- House Rules (collapsible) -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6 overflow-hidden"
            >
                <button
                    @click="showRules = !showRules"
                    class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-amber-50 rounded-full flex items-center justify-center flex-shrink-0"
                        >
                            <i class="pi pi-info-circle text-amber-500"></i>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-gray-800">
                                Lounge Rules & Rates
                            </p>
                            <p class="text-xs text-gray-400">
                                House rules, walk-in rates, and member billing
                            </p>
                        </div>
                    </div>
                    <i
                        class="pi text-gray-400"
                        :class="showRules ? 'pi-chevron-up' : 'pi-chevron-down'"
                    ></i>
                </button>

                <div v-if="showRules" class="px-5 pb-5 space-y-5">
                    <!-- House Rules -->
                    <div>
                        <h4
                            class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2"
                        >
                            House Rules
                        </h4>
                        <ul
                            class="text-sm text-gray-600 space-y-1.5 list-disc pl-5"
                        >
                            <li>
                                Outside food and drinks are welcome — no corkage
                                fee.
                            </li>
                            <li>Vaping is not allowed.</li>
                            <li>
                                Free WiFi, free charging, and free internet for
                                everyone in the lounge.
                            </li>
                            <li>
                                The time you pay for is the time you spend
                                inside the lounge — from check-in to check-out.
                                It is not based on how long you actually play or
                                watch.
                            </li>
                            <li>
                                Please keep track of your own time. If you say
                                you're staying for 1 hour, it's your
                                responsibility to watch the clock — staff will
                                not remind you.
                            </li>
                            <li>
                                If you go past your stated time, you're still
                                responsible for paying for the extra time, even
                                if no one reminded you.
                            </li>
                            <li>
                                Keep noise at a friendly level so everyone can
                                enjoy their session.
                            </li>
                            <li>
                                Clean up your area before leaving — throw out
                                trash and return borrowed items.
                            </li>
                            <li>
                                Staff reserves the right to end a session for
                                disruptive or abusive behavior.
                            </li>
                        </ul>
                    </div>

                    <!-- Walk-in Rates -->
                    <div>
                        <h4
                            class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2"
                        >
                            Walk-in Rates & Billing
                        </h4>
                        <ul
                            class="text-sm text-gray-600 space-y-1.5 list-disc pl-5"
                        >
                            <li>
                                Rates (per head): ₱25 / 30 min · ₱40 / hour ·
                                ₱100 / 3 hours · ₱200 / day.
                            </li>
                            <li>
                                Charged in 30-minute blocks, with 10 minutes of
                                grace on each block. Stay 34 minutes and you're
                                charged for 30; stay 42 minutes and you're
                                charged for a full hour.
                            </li>
                            <li>
                                The grace period is not extra playtime — it's
                                for packing up and heading out. Past the
                                10-minute mark, the next block begins.
                            </li>
                            <li>
                                Walk-in time cannot be paused, saved, or
                                continued later. Each visit is billed on its own
                                — earlier time is settled first and new visits
                                start fresh.
                            </li>
                            <li>
                                Example: 45 minutes, then a return visit of 1h
                                15m, costs ₱40 + ₱65 = ₱105. It cannot be
                                combined into one 2-hour session for ₱80.
                            </li>
                            <li>
                                Leftover time is not saved for walk-in
                                customers. Only members keep unused time.
                            </li>
                        </ul>
                    </div>

                    <!-- Member Rules -->
                    <div>
                        <h4
                            class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2"
                        >
                            Member Time & Billing
                        </h4>
                        <ul
                            class="text-sm text-gray-600 space-y-1.5 list-disc pl-5"
                        >
                            <li>
                                Rates (per head): ₱40 / hour · ₱100 / 3-hour
                                bundle.
                            </li>
                            <li>Member time is sold in whole hours only.</li>
                            <li>
                                To get the bundle price, all 3 hours must be
                                bought in one purchase. Buying 1 hour now and 2
                                later is charged ₱40 + ₱80, not ₱100.
                            </li>
                            <li>
                                Member time can be paused and continued for the
                                next visit. Unused time never expires — whatever
                                you don't use stays on your balance.
                            </li>
                            <li>
                                Time is not refundable and cannot be transferred
                                to another member.
                            </li>
                            <li>
                                Your balance counts down for the whole time
                                you're checked in — from time-in to time-out —
                                not just while playing.
                            </li>
                            <li>
                                Once your balance hits zero, it keeps counting
                                into the negative. Any negative balance must be
                                settled immediately before leaving.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Lounge Session Card (only if active session exists) -->
            <div
                v-if="loungeSession"
                class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-6"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center"
                        >
                            <i class="pi pi-home text-indigo-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-indigo-800">
                                You're in the Lounge
                            </p>
                            <p class="text-xs text-indigo-500">
                                Checked in at
                                {{ formatTime(loungeSession.checked_in_at) }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p
                            class="text-2xl font-mono font-bold"
                            :class="
                                isConsumableNegative
                                    ? 'text-red-600'
                                    : 'text-indigo-600'
                            "
                        >
                            {{ elapsedTime }}
                        </p>
                        <p
                            v-if="loungeSession.is_free"
                            class="text-xs text-yellow-600 font-medium"
                        >
                            Free Access
                        </p>
                        <p
                            v-else-if="
                                loungeSession.billing_mode === 'consumable'
                            "
                            class="text-xs"
                            :class="
                                isConsumableNegative
                                    ? 'text-red-500 font-medium'
                                    : 'text-indigo-400'
                            "
                        >
                            {{
                                isConsumableNegative
                                    ? "Over balance"
                                    : "Consumable time"
                            }}
                        </p>
                        <p v-else class="text-xs text-indigo-400">
                            Paid session
                        </p>
                    </div>
                </div>
                <div
                    v-if="
                        !loungeSession.is_free &&
                        loungeSession.billing_mode === 'consumable'
                    "
                    class="flex items-center gap-2 mt-3 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2"
                >
                    <i
                        class="pi pi-exclamation-triangle text-yellow-500 text-sm flex-shrink-0"
                    ></i>
                    <p class="text-xs text-yellow-700">
                        <span class="font-semibold"
                            >Time runs down automatically</span
                        ><br />
                        Your balance ticks down while you're checked in. Once it
                        hits zero it keeps counting into the negative, and any
                        negative balance must be settled immediately before
                        leaving the lounge.
                    </p>
                </div>
                <div
                    v-else-if="!loungeSession.is_free"
                    class="flex items-center gap-2 mt-3 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2"
                >
                    <i
                        class="pi pi-exclamation-triangle text-yellow-500 text-sm flex-shrink-0"
                    ></i>
                    <p class="text-xs text-yellow-700">
                        <span class="font-semibold">10-Minute Grace Period</span
                        ><br />
                        Time is charged in 30-minute blocks, and you get 10
                        minutes of grace on each one — go 38 minutes and you're
                        still only charged for 30. Note: the grace isn't extra
                        time to use — it's for packing up and heading out, not
                        for staying longer. Past 10 minutes and the next block
                        starts.
                    </p>
                </div>
            </div>

            <!-- Consumable Time Balance (Level 1 members, hidden during active consumable session) -->
            <div
                v-if="
                    user.level === 1 &&
                    !(
                        loungeSession &&
                        loungeSession.billing_mode === 'consumable'
                    )
                "
                class="rounded-xl p-4 mb-6 border"
                :class="
                    (user.consumable_minutes ?? 0) < 0
                        ? 'bg-red-50 border-red-200'
                        : 'bg-white border-gray-100 shadow-sm'
                "
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center"
                            :class="
                                (user.consumable_minutes ?? 0) < 0
                                    ? 'bg-red-100'
                                    : 'bg-indigo-50'
                            "
                        >
                            <i
                                class="pi pi-hourglass"
                                :class="
                                    (user.consumable_minutes ?? 0) < 0
                                        ? 'text-red-500'
                                        : 'text-indigo-500'
                                "
                            ></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Time Left</p>
                            <p class="text-xs text-gray-400">
                                Consumable lounge time
                            </p>
                        </div>
                    </div>
                    <p
                        class="text-xl font-mono font-bold"
                        :class="
                            (user.consumable_minutes ?? 0) < 0
                                ? 'text-red-600'
                                : 'text-indigo-600'
                        "
                    >
                        {{ formatMinutesBalance(user.consumable_minutes) }}
                    </p>
                </div>
                <div
                    v-if="(user.consumable_minutes ?? 0) < 0"
                    class="flex items-center gap-2 mt-3 bg-red-100 border border-red-200 rounded-lg px-3 py-2"
                >
                    <i
                        class="pi pi-exclamation-triangle text-red-500 text-sm flex-shrink-0"
                    ></i>
                    <p class="text-xs text-red-700">
                        <span class="font-semibold">Balance is negative</span
                        ><br />
                        You won't be able to check in on consumable time until
                        this is settled. Please see staff to buy more time.
                    </p>
                </div>
            </div>

            <!-- Profile + Membership Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Profile Card -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
                >
                    <h3
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3"
                    >
                        Profile
                    </h3>
                    <div class="flex items-center gap-4">
                        <img
                            v-if="user.profile_image"
                            :src="`/storage/profiles/${user.profile_image}`"
                            class="w-14 h-14 rounded-full object-cover border-2 border-blue-100"
                        />
                        <div
                            v-else
                            class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0"
                        >
                            <i class="pi pi-user text-blue-500 text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">
                                {{ user.name }}
                            </p>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <span
                                    class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full font-medium"
                                >
                                    Level {{ user.level || 1 }}
                                </span>
                                <span
                                    v-if="user.is_premium"
                                    class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full font-medium"
                                >
                                    Premium
                                </span>
                                <span
                                    class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full"
                                >
                                    {{ roleLabel }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Membership Card -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
                >
                    <h3
                        class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3"
                    >
                        Membership
                    </h3>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                :class="
                                    user.is_premium
                                        ? 'bg-yellow-100'
                                        : 'bg-gray-100'
                                "
                                class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                            >
                                <i
                                    :class="
                                        user.is_premium
                                            ? 'pi-crown text-yellow-600'
                                            : 'pi-user text-gray-500'
                                    "
                                    class="pi text-lg"
                                ></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{
                                        user.is_premium
                                            ? "Premium Member"
                                            : "Standard Member"
                                    }}
                                </p>
                                <p
                                    v-if="membership && membership.end_date"
                                    class="text-xs text-gray-400"
                                >
                                    Expires
                                    {{ formatDate(membership.end_date) }}
                                </p>
                                <p v-else class="text-xs text-gray-400">
                                    {{
                                        user.is_premium
                                            ? "Active"
                                            : "No active membership"
                                    }}
                                </p>
                            </div>
                        </div>
                        <span
                            v-if="user.is_premium"
                            class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full font-medium"
                        >
                            Active
                        </span>
                        <router-link
                            v-else
                            to="/profile"
                            class="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-medium hover:bg-blue-200 transition-colors"
                        >
                            Upgrade
                        </router-link>
                    </div>
                </div>
            </div>

            <!-- Lounge Stats + Quick Stats Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center"
                >
                    <i class="pi pi-clock text-indigo-400 text-xl mb-2"></i>
                    <p class="text-xl font-bold text-gray-800">
                        {{ loungeStats.total_time || "0h 0m" }}
                    </p>
                    <p class="text-xs text-gray-500">Total Lounge Time</p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center"
                >
                    <i class="pi pi-home text-indigo-400 text-xl mb-2"></i>
                    <p class="text-xl font-bold text-gray-800">
                        {{ loungeStats.total_visits || 0 }}
                    </p>
                    <p class="text-xs text-gray-500">Lounge Visits</p>
                </div>
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center"
                >
                    <i class="pi pi-trophy text-purple-400 text-xl mb-2"></i>
                    <p class="text-xl font-bold text-gray-800">
                        {{ userRank || "—" }}
                    </p>
                    <p class="text-xs text-gray-500">Current Rank</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
            >
                <h3
                    class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4"
                >
                    Recent Activity
                </h3>

                <div
                    v-if="recentActivity.length === 0"
                    class="text-center py-6 text-gray-400 text-sm"
                >
                    No recent activity yet.
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="item in recentActivity"
                        :key="item.id + item.type"
                        class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                :class="
                                    item.type === 'trade'
                                        ? 'bg-blue-100'
                                        : 'bg-green-100'
                                "
                                class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"
                            >
                                <i
                                    :class="
                                        item.type === 'trade'
                                            ? 'pi-arrow-right-arrow-left text-blue-600'
                                            : 'pi-ticket text-green-600'
                                    "
                                    class="pi text-xs"
                                ></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ item.label }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ formatTimeAgo(item.created_at) }}
                                </p>
                            </div>
                        </div>
                        <span
                            :class="{
                                'bg-yellow-100 text-yellow-700':
                                    item.status === 'pending',
                                'bg-green-100 text-green-700':
                                    item.status === 'accepted' ||
                                    item.status === 'approved',
                                'bg-red-100 text-red-700':
                                    item.status === 'rejected' ||
                                    item.status === 'cancelled',
                            }"
                            class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                        >
                            {{ item.status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
import QrcodeVue from "qrcode.vue";

export default {
    name: "Dashboard",
    components: { QrcodeVue },
    data() {
        return {
            user: {
                name: "",
                level: 1,
                stars: 0,
                points: 0,
                cash: 0,
                consumable_minutes: 0,
                is_premium: false,
                profile_image: null,
                role: "user",
                created_at: null,
            },
            membership: null,
            userRank: null,
            showRules: false,
            loungeSession: null,
            loungeStats: { total_time: "0h 0m", total_visits: 0 },
            recentActivity: [],
            elapsedTime: "00:00:00",
            tickInterval: null,
        };
    },

    computed: {
        isConsumableNegative() {
            return (
                this.loungeSession?.billing_mode === "consumable" &&
                this.elapsedTime.startsWith("-")
            );
        },
        roleLabel() {
            const map = {
                admin: "Administrator",
                shop_owner: "Shop Owner",
                staff: "Staff",
            };
            return map[this.user.role] || "Member";
        },
    },

    async mounted() {
        const token = localStorage.getItem("auth-token");
        if (token)
            axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

        await this.fetchUser();
        await Promise.all([
            this.fetchRank(),
            this.fetchLoungeSession(),
            this.fetchLoungeStats(),
            this.fetchRecentActivity(),
        ]);

        this.tickInterval = setInterval(() => this.updateTimer(), 1000);
    },

    beforeUnmount() {
        clearInterval(this.tickInterval);
    },

    methods: {
        async fetchUser() {
            try {
                const res = await axios.get("/api/user/profile");
                if (res.data.success) {
                    this.user = { ...this.user, ...res.data.user };
                    this.membership = res.data.membership || null;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async fetchRank() {
            try {
                const res = await axios.get("/api/leaderboard");
                if (res.data.success) {
                    const idx = res.data.users.findIndex(
                        (u) => u.id === this.user.id,
                    );
                    this.userRank = idx !== -1 ? `#${idx + 1}` : "Unranked";
                }
            } catch (e) {
                console.error(e);
            }
        },

        async fetchLoungeSession() {
            try {
                const res = await axios.get("/api/lounge/my-session");
                if (res.data.success) {
                    this.loungeSession = res.data.session || null;
                }
            } catch (e) {
                this.loungeSession = null;
            }
        },

        async fetchLoungeStats() {
            try {
                const res = await axios.get("/api/lounge/my-stats");
                if (res.data.success) this.loungeStats = res.data;
            } catch (e) {
                this.loungeStats = { total_time: "0h 0m", total_visits: 0 };
            }
        },

        async fetchRecentActivity() {
            try {
                const [tradesRes, betsRes] = await Promise.all([
                    axios.get("/api/trades"),
                    axios.get("/api/bets"),
                ]);

                const trades = (tradesRes.data.trades || [])
                    .slice(0, 3)
                    .map((t) => ({
                        id: t.id,
                        type: "trade",
                        label: `Trade — ${t.send_amount} pts → ${t.receive_amount} pts`,
                        status: t.status,
                        created_at: t.created_at,
                    }));

                const bets = (betsRes.data.my_bets || [])
                    .slice(0, 3)
                    .map((b) => ({
                        id: b.id,
                        type: "bet",
                        label: `Bet — ${b.stars_amount} stars`,
                        status: b.status,
                        created_at: b.created_at,
                    }));

                // Merge and sort by date, keep top 3
                this.recentActivity = [...trades, ...bets]
                    .sort(
                        (a, b) =>
                            new Date(b.created_at) - new Date(a.created_at),
                    )
                    .slice(0, 3);
            } catch (e) {
                console.error(e);
            }
        },

        updateTimer() {
            if (!this.loungeSession) return;
            const elapsedSeconds = Math.floor(
                (Date.now() -
                    new Date(this.loungeSession.checked_in_at).getTime()) /
                    1000,
            );

            let totalSeconds = elapsedSeconds;
            let showSign = false;

            if (this.loungeSession.billing_mode === "consumable") {
                const balanceSeconds = (this.user.consumable_minutes ?? 0) * 60;
                totalSeconds = balanceSeconds - elapsedSeconds;
                showSign = true;
            }

            const sign = showSign && totalSeconds < 0 ? "-" : "";
            const abs = Math.abs(totalSeconds);
            const h = Math.floor(abs / 3600)
                .toString()
                .padStart(2, "0");
            const m = Math.floor((abs % 3600) / 60)
                .toString()
                .padStart(2, "0");
            const s = (abs % 60).toString().padStart(2, "0");
            this.elapsedTime = `${sign}${h}:${m}:${s}`;
        },

        formatMinutesBalance(mins) {
            mins = mins ?? 0;
            const sign = mins < 0 ? "-" : "";
            const abs = Math.abs(mins);
            const h = Math.floor(abs / 60);
            const m = abs % 60;
            return `${sign}${h}h ${m}m`;
        },

        formatTime(dt) {
            if (!dt) return "—";
            return new Date(dt).toLocaleTimeString("en-PH", {
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        formatDate(dt) {
            if (!dt) return "—";
            return new Date(dt).toLocaleDateString("en-PH", {
                year: "numeric",
                month: "short",
                day: "numeric",
            });
        },

        formatTimeAgo(dateString) {
            const now = new Date();
            const date = new Date(dateString);
            const diff = Math.floor((now - date) / 60000);
            if (diff < 1) return "Just now";
            if (diff < 60) return `${diff}m ago`;
            const h = Math.floor(diff / 60);
            if (h < 24) return `${h}h ago`;
            return `${Math.floor(h / 24)}d ago`;
        },
    },
};
</script>
