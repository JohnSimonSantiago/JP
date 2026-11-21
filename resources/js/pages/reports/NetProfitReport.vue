<template>
    <div>
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="font-semibold text-gray-800">Net Profit</h3>
            <DateRangePicker initial-mode="month" @change="onRangeChange" />
        </div>

        <!-- Paid memberships with no saved amount yet (old ones, before amounts were saved) -->
        <div
            v-if="data && data.memberships_missing_amount > 0"
            class="mb-4 text-sm bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3"
        >
            <i class="pi pi-exclamation-triangle mr-1"></i>
            {{ data.memberships_missing_amount }} paid
            {{ data.memberships_missing_amount === 1 ? "membership has" : "memberships have" }}
            no amount saved yet, so membership revenue is too low. Run
            <code class="bg-amber-100 px-1 rounded">php artisan memberships:backfill-amounts</code>
            on the server.
        </div>

        <div v-if="loading && !data" class="text-center py-8 text-gray-400">
            <i class="pi pi-spin pi-spinner text-xl"></i>
        </div>
        <div v-else-if="error" class="text-center py-8 text-red-500 text-sm">{{ error }}</div>

        <template v-else-if="data">
            <div :class="loading ? 'opacity-60' : ''">
                <!-- Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-xs text-green-600">Revenue</p>
                        <p class="text-xl font-bold text-green-700 tabular-nums">{{ money(data.revenue.total) }}</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3">
                        <p class="text-xs text-red-500">Costs</p>
                        <p class="text-xl font-bold text-red-600 tabular-nums">{{ money(data.costs.total) }}</p>
                    </div>
                    <div class="rounded-lg p-3" :class="data.net_profit >= 0 ? 'bg-indigo-50' : 'bg-red-100'">
                        <p class="text-xs" :class="data.net_profit >= 0 ? 'text-indigo-500' : 'text-red-600'">Net profit</p>
                        <p
                            class="text-xl font-bold tabular-nums"
                            :class="data.net_profit >= 0 ? 'text-indigo-700' : 'text-red-700'"
                        >
                            {{ money(data.net_profit) }}
                        </p>
                    </div>
                    <div class="bg-amber-50 rounded-lg p-3">
                        <p class="text-xs text-amber-600">Owed to shops (now)</p>
                        <p class="text-xl font-bold text-amber-700 tabular-nums">{{ money(data.owed_to_shops) }}</p>
                        <p class="text-[11px] text-amber-600 mt-0.5">Not in costs until paid out</p>
                    </div>
                </div>

                <!-- Line by line -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-2">Money in</p>
                        <div class="border border-gray-100 rounded-lg divide-y divide-gray-100 text-sm">
                            <div class="flex justify-between px-3 py-2">
                                <span class="text-gray-600">Lounge bills</span>
                                <span class="tabular-nums">{{ money(data.revenue.lounge) }}</span>
                            </div>
                            <div class="flex justify-between px-3 py-2">
                                <span class="text-gray-600">Buy time <span class="text-xs text-gray-400">(cash / GCash)</span></span>
                                <span class="tabular-nums">{{ money(data.revenue.buy_time) }}</span>
                            </div>
                            <div class="flex justify-between px-3 py-2">
                                <span class="text-gray-600">Memberships <span class="text-xs text-gray-400">(paid; gifts are ₱0)</span></span>
                                <span class="tabular-nums">{{ money(data.revenue.memberships) }}</span>
                            </div>
                            <div class="flex justify-between px-3 py-2 font-semibold bg-gray-50">
                                <span>Revenue</span>
                                <span class="tabular-nums text-green-700">{{ money(data.revenue.total) }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-2">Money out</p>
                        <div class="border border-gray-100 rounded-lg divide-y divide-gray-100 text-sm">
                            <div class="px-3 py-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Expenses</span>
                                    <span class="tabular-nums">{{ money(data.costs.expenses) }}</span>
                                </div>
                                <div
                                    v-for="c in data.costs.expense_categories"
                                    :key="c.name"
                                    class="flex items-center justify-between text-xs text-gray-500 pl-3 mt-1"
                                >
                                    <span class="flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: c.color || '#9ca3af' }"></span>
                                        {{ c.name }}
                                    </span>
                                    <span class="tabular-nums">{{ money(c.total) }}</span>
                                </div>
                            </div>
                            <div class="flex justify-between px-3 py-2">
                                <span class="text-gray-600">Shop payouts <span class="text-xs text-gray-400">(on the day paid out)</span></span>
                                <span class="tabular-nums">{{ money(data.costs.shop_payouts) }}</span>
                            </div>
                            <div class="flex justify-between px-3 py-2 font-semibold bg-gray-50">
                                <span>Costs</span>
                                <span class="tabular-nums text-red-600">{{ money(data.costs.total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <p class="text-sm font-medium text-gray-600 mb-2">
                    {{ data.group_by === "day" ? "Per day" : "Per month" }}
                </p>
                <p v-if="data.series.length === 0" class="text-sm text-gray-400 text-center py-8">
                    Nothing recorded for this range.
                </p>
                <Chart v-else type="bar" :data="chartData" :options="chartOptions" class="h-72" />

                <p class="text-xs text-gray-400 mt-4">
                    Revenue counts real money only: lounge bills, buy time paid in cash or GCash, and
                    paid memberships (on their approval day). Buy time paid with Coins isn't new money,
                    so it's left out. Costs are expenses plus shop payouts on the day they're paid out.
                </p>
            </div>
        </template>
    </div>
</template>

<script>
import axios from "axios";
import DateRangePicker from "@/components/DateRangePicker.vue";

export default {
    name: "NetProfitReport",
    components: { DateRangePicker },

    data() {
        return {
            from: null,
            to: null,
            data: null,
            loading: false,
            error: null,
        };
    },

    computed: {
        chartData() {
            const s = this.data?.series || [];
            return {
                labels: s.map((r) => this.bucketLabel(r.bucket)),
                datasets: [
                    { type: "line", label: "Net profit", data: s.map((r) => r.net), borderColor: "#4f46e5", backgroundColor: "#4f46e5", tension: 0.3, pointRadius: 2 },
                    { type: "bar", label: "Revenue", data: s.map((r) => r.revenue), backgroundColor: "#22c55e" },
                    { type: "bar", label: "Costs", data: s.map((r) => r.costs), backgroundColor: "#f87171" },
                ],
            };
        },
        chartOptions() {
            return {
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${this.money(ctx.raw)}` },
                    },
                },
                scales: {
                    y: { ticks: { callback: (v) => "₱" + Number(v).toLocaleString("en-PH") } },
                },
            };
        },
    },

    methods: {
        onRangeChange({ from, to }) {
            this.from = from;
            this.to = to;
            this.fetch();
        },

        async fetch() {
            const requestNo = (this._requestNo = (this._requestNo || 0) + 1);
            this.loading = true;
            this.error = null;
            try {
                const res = await axios.get("/api/admin/reports/net-profit", {
                    headers: { Authorization: `Bearer ${localStorage.getItem("auth-token")}` },
                    params: { from: this.from || null, to: this.to || null },
                });
                if (requestNo !== this._requestNo) return; // a newer range won
                this.data = res.data;
            } catch (e) {
                if (requestNo !== this._requestNo) return;
                this.error = e.response?.data?.message || "Couldn't load the report.";
            } finally {
                if (requestNo === this._requestNo) this.loading = false;
            }
        },

        money(value) {
            const n = Number(value) || 0;
            const sign = n < 0 ? "-" : "";
            return sign + "₱" + Math.abs(n).toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        // "2026-11-21" -> "Nov 21"; "2026-11" -> "Nov 2026"
        bucketLabel(bucket) {
            const [y, m, d] = bucket.split("-").map(Number);
            const date = new Date(Date.UTC(y, m - 1, d || 1));
            return d
                ? date.toLocaleDateString("en-PH", { month: "short", day: "numeric", timeZone: "UTC" })
                : date.toLocaleDateString("en-PH", { month: "short", year: "numeric", timeZone: "UTC" });
        },
    },
};
</script>
