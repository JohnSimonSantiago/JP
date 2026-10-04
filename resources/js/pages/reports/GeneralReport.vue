<template>
    <div>
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="font-semibold text-gray-800">General Report</h3>
            <DateRangePicker @change="onRangeChange" />
        </div>

        <div v-if="loading" class="text-center py-8 text-gray-400">
            <i class="pi pi-spin pi-spinner text-xl"></i>
        </div>

        <div v-else-if="error" class="text-center py-8 text-red-500 text-sm">
            {{ error }}
        </div>

        <template v-else>
            <!-- Daily averages -->
            <p class="text-sm font-medium text-gray-600 mb-2">
                Average per day
                <span class="text-xs text-gray-400 font-normal">
                    (over {{ days.length }} day{{
                        days.length === 1 ? "" : "s"
                    }})
                </span>
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-indigo-50 rounded-lg p-3">
                    <p class="text-xs text-indigo-400">Buy time revenue</p>
                    <p class="text-lg font-bold text-indigo-700">
                        ₱{{ money(avgBuyRevenue) }}
                    </p>
                </div>
                <div class="bg-purple-50 rounded-lg p-3">
                    <p class="text-xs text-purple-400">Hours sold</p>
                    <p class="text-lg font-bold text-purple-700">
                        {{ formatMinutes(avgMinutesSold) }}
                    </p>
                </div>
                <div class="bg-green-50 rounded-lg p-3">
                    <p class="text-xs text-green-400">Lounge revenue</p>
                    <p class="text-lg font-bold text-green-600">
                        ₱{{ money(avgLoungeRevenue) }}
                    </p>
                </div>
                <div class="bg-sky-50 rounded-lg p-3">
                    <p class="text-xs text-sky-400">Lounge visits</p>
                    <p class="text-lg font-bold text-sky-700">
                        {{ avgVisits.toFixed(1) }}
                    </p>
                </div>
            </div>

            <!-- Totals -->
            <p class="text-sm font-medium text-gray-600 mb-2">Totals</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400">Combined revenue</p>
                    <p class="text-lg font-bold text-gray-800">
                        ₱{{ money(totalBuyRevenue + totalLoungeRevenue) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        ₱{{ money(avgBuyRevenue + avgLoungeRevenue) }} / day
                    </p>
                    <div class="mt-2 pt-2 border-t border-gray-200 space-y-0.5">
                        <div class="flex justify-between text-xs">
                            <span class="text-green-600">Lounge</span>
                            <span class="text-gray-600">
                                ₱{{ money(totalLoungeRevenue) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-indigo-600">Buy time</span>
                            <span class="text-gray-600">
                                ₱{{ money(totalBuyRevenue) }}
                            </span>
                        </div>
                        <div
                            v-if="totalBuyBalance > 0"
                            class="flex justify-between text-xs pl-2"
                        >
                            <span class="text-gray-400"
                                >of which app balance</span
                            >
                            <span class="text-gray-400">
                                ₱{{ money(totalBuyBalance) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400">Lounge visits</p>
                    <p class="text-lg font-bold text-gray-800">
                        {{ sessions.length }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ uniqueCustomers }} unique customers
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400">Avg session length</p>
                    <p class="text-lg font-bold text-gray-800">
                        {{ formatMinutes(avgSessionMinutes) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ formatMinutes(totalPlayedMinutes) }} played total
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400">Avg bill per paid visit</p>
                    <p class="text-lg font-bold text-gray-800">
                        ₱{{ money(avgBillPerPaidVisit) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ paidSessions.length }} paid visits
                    </p>
                </div>
            </div>

            <!-- Insights -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                <div class="rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-400">Busiest weekday</p>
                    <p class="text-base font-bold text-gray-800">
                        {{ busiestWeekday ? busiestWeekday.label : "—" }}
                    </p>
                    <p
                        v-if="busiestWeekday"
                        class="text-xs text-gray-400 mt-0.5"
                    >
                        avg ₱{{ money(busiestWeekday.value) }} on that day
                    </p>
                </div>
                <div class="rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-400">Peak check-in hour</p>
                    <p class="text-base font-bold text-gray-800">
                        {{ peakHour ? peakHour.label : "—" }}
                    </p>
                    <p v-if="peakHour" class="text-xs text-gray-400 mt-0.5">
                        {{ peakHour.value }} check-ins
                    </p>
                </div>
                <div class="rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-400">Active days</p>
                    <p class="text-base font-bold text-gray-800">
                        {{ activeDays }} of {{ days.length }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        days with any sale or visit
                    </p>
                </div>
            </div>

            <!-- Customer mix + top customers -->
            <div
                v-if="sessions.length"
                class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6"
            >
                <div class="rounded-lg p-4 border border-gray-100">
                    <p class="text-sm font-medium text-gray-600 mb-3">
                        Visit mix
                    </p>
                    <div
                        v-for="m in visitMix"
                        :key="m.label"
                        class="mb-2 last:mb-0"
                    >
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600">{{ m.label }}</span>
                            <span class="text-gray-400">
                                {{ m.count }} ({{ m.pct }}%)
                            </span>
                        </div>
                        <div class="h-2 bg-gray-100 rounded">
                            <div
                                class="h-2 rounded"
                                :class="m.color"
                                :style="{ width: m.pct + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg p-4 border border-gray-100">
                    <p class="text-sm font-medium text-gray-600 mb-3">
                        Top customers by time played
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div
                            v-for="group in topCustomerGroups"
                            :key="group.label"
                        >
                            <p
                                class="text-xs font-medium mb-1"
                                :class="group.color"
                            >
                                {{ group.label }}
                            </p>
                            <div
                                v-if="!group.list.length"
                                class="text-xs text-gray-400 py-1"
                            >
                                None in this range
                            </div>
                            <div
                                v-for="(c, i) in group.list"
                                :key="c.key"
                                class="flex items-center justify-between text-sm py-1"
                            >
                                <span class="text-gray-700 truncate">
                                    <span class="text-gray-400 mr-2">{{
                                        i + 1
                                    }}</span>
                                    {{ c.name }}
                                </span>
                                <span
                                    class="text-gray-500 whitespace-nowrap ml-2 text-xs"
                                >
                                    {{ formatMinutes(c.minutes) }}
                                    <span class="text-gray-300">·</span>
                                    {{ c.visits }}×
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily revenue chart -->
            <div
                v-if="hasData"
                class="bg-white rounded-lg p-4 border border-gray-100 mb-4"
            >
                <p class="text-sm font-medium text-gray-600 mb-3">
                    Revenue per day
                </p>
                <Chart
                    type="bar"
                    :data="revenueChartData"
                    :options="stackedOptions"
                    class="h-64"
                />
            </div>

            <!-- Check-ins by hour -->
            <div
                v-if="sessions.length"
                class="bg-white rounded-lg p-4 border border-gray-100"
            >
                <p class="text-sm font-medium text-gray-600 mb-3">
                    Check-ins by hour
                </p>
                <Chart
                    type="bar"
                    :data="hourChartData"
                    :options="plainOptions"
                    class="h-56"
                />
            </div>

            <div v-if="!hasData" class="text-center py-8 text-gray-400 text-sm">
                No sales or visits for this range.
            </div>
        </template>
    </div>
</template>

<script>
import axios from "axios";
import DateRangePicker from "@/components/DateRangePicker.vue";

const WEEKDAYS = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

export default {
    name: "GeneralReport",
    components: { DateRangePicker },
    data() {
        return {
            loading: false,
            error: null,
            from: null,
            to: null,
            purchases: [],
            sessions: [],
        };
    },
    computed: {
        hasData() {
            return this.purchases.length > 0 || this.sessions.length > 0;
        },

        // Every calendar day in the range (capped at today), so empty days count toward averages
        days() {
            const today = this.localDate(new Date());
            let start = this.from;
            let end = this.to && this.to < today ? this.to : today;

            if (!start) {
                // "All time": start from the earliest record we have
                const dates = [
                    ...this.purchases.map((p) => this.dayKey(p.created_at)),
                    ...this.sessions.map((s) => this.dayKey(s.checked_in_at)),
                ];
                if (!dates.length) return [];
                start = dates.sort()[0];
            }
            if (start > end) return [];

            const list = [];
            const cur = new Date(start + "T00:00:00");
            const last = new Date(end + "T00:00:00");
            while (cur <= last) {
                list.push(this.localDate(cur));
                cur.setDate(cur.getDate() + 1);
            }
            return list;
        },
        dayCount() {
            return this.days.length || 1;
        },

        // ── Buy time ──
        totalBuyRevenue() {
            return this.purchases.reduce(
                (sum, p) => sum + Number(p.amount || 0),
                0,
            );
        },
        totalBuyBalance() {
            return this.purchases
                .filter((p) => p.payment_method === "balance")
                .reduce((sum, p) => sum + Number(p.amount || 0), 0);
        },
        totalMinutesSold() {
            return this.purchases.reduce(
                (sum, p) => sum + Number(p.minutes_added || 0),
                0,
            );
        },
        avgBuyRevenue() {
            return this.totalBuyRevenue / this.dayCount;
        },
        avgMinutesSold() {
            return Math.round(this.totalMinutesSold / this.dayCount);
        },

        // ── Lounge ──
        paidSessions() {
            return this.sessions.filter((s) => Number(s.total_bill) > 0);
        },
        totalLoungeRevenue() {
            return this.sessions.reduce(
                (sum, s) => sum + Number(s.total_bill || 0),
                0,
            );
        },
        avgLoungeRevenue() {
            return this.totalLoungeRevenue / this.dayCount;
        },
        avgVisits() {
            return this.sessions.length / this.dayCount;
        },
        totalPlayedMinutes() {
            return this.sessions.reduce(
                (sum, s) => sum + this.sessionMinutes(s),
                0,
            );
        },
        avgSessionMinutes() {
            return this.sessions.length
                ? Math.round(this.totalPlayedMinutes / this.sessions.length)
                : 0;
        },
        avgBillPerPaidVisit() {
            return this.paidSessions.length
                ? this.totalLoungeRevenue / this.paidSessions.length
                : 0;
        },
        uniqueCustomers() {
            const set = new Set(
                this.sessions.map((s) =>
                    s.user_id
                        ? "u" + s.user_id
                        : "n" + (s.customer_name || "").trim().toLowerCase(),
                ),
            );
            return set.size;
        },

        // ── Per-day buckets (shared by chart + insights) ──
        byDay() {
            const map = {};
            this.days.forEach(
                (d) => (map[d] = { buy: 0, lounge: 0, visits: 0 }),
            );
            this.purchases.forEach((p) => {
                const d = this.dayKey(p.created_at);
                if (map[d]) map[d].buy += Number(p.amount || 0);
            });
            this.sessions.forEach((s) => {
                const d = this.dayKey(s.checked_in_at);
                if (map[d]) {
                    map[d].lounge += Number(s.total_bill || 0);
                    map[d].visits += 1;
                }
            });
            return map;
        },
        activeDays() {
            return Object.values(this.byDay).filter(
                (d) => d.buy > 0 || d.visits > 0,
            ).length;
        },
        busiestWeekday() {
            // Average revenue per weekday (e.g. total of all Saturdays ÷ number of Saturdays)
            const totals = Array(7).fill(0);
            const counts = Array(7).fill(0);
            this.days.forEach((d) => {
                const wd = new Date(d + "T00:00:00").getDay();
                totals[wd] += this.byDay[d].buy + this.byDay[d].lounge;
                counts[wd] += 1;
            });
            let best = null;
            totals.forEach((t, i) => {
                if (!counts[i]) return;
                const avg = t / counts[i];
                if (avg > 0 && (!best || avg > best.value)) {
                    best = { label: WEEKDAYS[i], value: avg };
                }
            });
            return best;
        },
        visitsByHour() {
            const hours = Array(24).fill(0);
            this.sessions.forEach((s) => {
                hours[new Date(s.checked_in_at).getHours()] += 1;
            });
            return hours;
        },
        peakHour() {
            const max = Math.max(...this.visitsByHour);
            if (max <= 0) return null;
            const h = this.visitsByHour.indexOf(max);
            return {
                label: `${this.hourLabel(h)} – ${this.hourLabel(h + 1)}`,
                value: max,
            };
        },

        visitMix() {
            const total = this.sessions.length || 1;
            const count = (fn) => this.sessions.filter(fn).length;
            const rows = [
                {
                    label: "Walk-in",
                    count: count((s) => s.customer_type === "walk_in"),
                    color: "bg-gray-400",
                },
                {
                    label: "Member · hourly",
                    count: count(
                        (s) =>
                            s.customer_type === "member" &&
                            !s.is_free &&
                            s.billing_mode !== "consumable",
                    ),
                    color: "bg-green-500",
                },
                {
                    label: "Member · bought time",
                    count: count((s) => s.billing_mode === "consumable"),
                    color: "bg-indigo-500",
                },
                {
                    label: "Free (Level 2/3)",
                    count: count((s) => s.is_free),
                    color: "bg-yellow-400",
                },
            ];
            return rows.map((r) => ({
                ...r,
                pct: Math.round((r.count / total) * 100),
            }));
        },
        topCustomerGroups() {
            const rank = (list, keyFn) => {
                const map = {};
                list.forEach((s) => {
                    const key = keyFn(s);
                    const name = s.user?.name || s.customer_name || "—";
                    if (!map[key])
                        map[key] = { key, name, minutes: 0, visits: 0 };
                    map[key].minutes += this.sessionMinutes(s);
                    map[key].visits += 1;
                });
                return Object.values(map)
                    .sort((a, b) => b.minutes - a.minutes)
                    .slice(0, 5);
            };

            const members = this.sessions.filter(
                (s) => s.customer_type === "member" && s.user_id,
            );
            const walkIns = this.sessions.filter(
                (s) => s.customer_type !== "member" || !s.user_id,
            );

            return [
                {
                    label: "Members",
                    color: "text-indigo-600",
                    list: rank(members, (s) => "u" + s.user_id),
                },
                {
                    label: "Walk-ins",
                    color: "text-gray-500",
                    list: rank(
                        walkIns,
                        (s) =>
                            "n" + (s.customer_name || "").trim().toLowerCase(),
                    ),
                },
            ];
        },

        // ── Charts ──
        revenueChartData() {
            return {
                labels: this.days.map((d) => this.shortLabel(d)),
                datasets: [
                    {
                        label: "Lounge",
                        data: this.days.map((d) => this.byDay[d].lounge),
                        backgroundColor: "#22c55e",
                        borderRadius: 4,
                    },
                    {
                        label: "Buy time",
                        data: this.days.map((d) => this.byDay[d].buy),
                        backgroundColor: "#6366f1",
                        borderRadius: 4,
                    },
                ],
            };
        },
        hourChartData() {
            return {
                labels: this.visitsByHour.map((_, h) => this.hourLabel(h)),
                datasets: [
                    {
                        label: "Check-ins",
                        data: this.visitsByHour,
                        backgroundColor: "#0ea5e9",
                        borderRadius: 4,
                    },
                ],
            };
        },
        stackedOptions() {
            return {
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: "bottom" } },
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true },
                },
            };
        },
        plainOptions() {
            return {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
            };
        },
    },
    methods: {
        headers() {
            const token = localStorage.getItem("auth-token");
            return token ? { Authorization: `Bearer ${token}` } : {};
        },
        localDate(d) {
            const off = d.getTimezoneOffset() * 60000;
            return new Date(d.getTime() - off).toISOString().split("T")[0];
        },
        dayKey(dateStr) {
            return this.localDate(new Date(dateStr));
        },
        shortLabel(key) {
            return new Date(key + "T00:00:00").toLocaleDateString("en-PH", {
                month: "short",
                day: "numeric",
            });
        },
        hourLabel(h) {
            h = h % 24;
            const suffix = h < 12 ? "am" : "pm";
            const hr = h % 12 === 0 ? 12 : h % 12;
            return `${hr}${suffix}`;
        },
        sessionMinutes(s) {
            if (!s.checked_in_at || !s.checked_out_at) return 0;
            return Math.max(
                0,
                Math.floor(
                    (new Date(s.checked_out_at) - new Date(s.checked_in_at)) /
                        60000,
                ),
            );
        },
        money(n) {
            return Math.round(Number(n) || 0).toLocaleString();
        },
        formatMinutes(mins) {
            mins = Math.round(mins || 0);
            const h = Math.floor(mins / 60);
            const m = mins % 60;
            return `${h}h ${m}m`;
        },
        async onRangeChange({ from, to }) {
            this.from = from;
            this.to = to;
            this.loading = true;
            this.error = null;
            try {
                const params = { from, to };
                const [buyRes, loungeRes] = await Promise.all([
                    axios.get("/api/lounge/consumable/history", {
                        params,
                        headers: this.headers(),
                    }),
                    axios.get("/api/lounge/session-history", {
                        params,
                        headers: this.headers(),
                    }),
                ]);
                this.purchases = buyRes.data.success
                    ? buyRes.data.purchases
                    : [];
                this.sessions = loungeRes.data.success
                    ? loungeRes.data.sessions
                    : [];
            } catch (e) {
                console.error(e);
                this.error = "Couldn't load the report. Try the range again.";
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
