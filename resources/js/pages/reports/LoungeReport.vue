<template>
    <div>
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="font-semibold text-gray-800">Lounge Report</h3>
            <DateRangePicker @change="onRangeChange" />
        </div>

        <div v-if="loading" class="text-center py-8 text-gray-400">
            <i class="pi pi-spin pi-spinner text-xl"></i>
        </div>

        <template v-else>
            <div class="grid grid-cols-3 gap-3 mb-6">
                <div class="bg-green-50 rounded-lg p-3">
                    <p class="text-xs text-green-400">Earned</p>
                    <p class="text-lg font-bold text-green-600">
                        ₱{{ totalEarned.toLocaleString() }}
                    </p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-3">
                    <p class="text-xs text-indigo-400">Sessions</p>
                    <p class="text-lg font-bold text-indigo-700">
                        {{ history.length }}
                    </p>
                </div>
                <div class="bg-blue-50 rounded-lg p-3">
                    <p class="text-xs text-blue-400">Days active</p>
                    <p class="text-lg font-bold text-blue-600">
                        {{ activeDays }}
                    </p>
                </div>
            </div>

            <div v-if="history.length" class="grid grid-cols-2 gap-3 mb-6">
                <div class="bg-emerald-50 rounded-lg p-3">
                    <p class="text-xs text-emerald-400">Best earning day</p>
                    <p class="text-lg font-bold text-emerald-600">
                        ₱{{
                            bestEarningDay
                                ? bestEarningDay.value.toLocaleString()
                                : 0
                        }}
                    </p>
                    <p class="text-xs text-emerald-400 mt-0.5">
                        {{ bestEarningDay ? bestEarningDay.label : "—" }}
                    </p>
                </div>
                <div class="bg-violet-50 rounded-lg p-3">
                    <p class="text-xs text-violet-400">Busiest day</p>
                    <p class="text-lg font-bold text-violet-600">
                        {{ bestSessionDay ? bestSessionDay.value : 0 }} sessions
                    </p>
                    <p class="text-xs text-violet-400 mt-0.5">
                        {{ bestSessionDay ? bestSessionDay.label : "—" }}
                    </p>
                </div>
            </div>

            <div
                v-if="history.length"
                class="bg-white rounded-lg p-4 border border-gray-100"
            >
                <p class="text-sm font-medium text-gray-600 mb-3">
                    Sessions per day
                </p>
                <Chart
                    type="bar"
                    :data="chartData"
                    :options="chartOptions"
                    class="h-64"
                />
            </div>
            <div v-else class="text-center py-8 text-gray-400 text-sm">
                No sessions for this range.
            </div>
        </template>
    </div>
</template>

<script>
import axios from "axios";
import DateRangePicker from "@/components/DateRangePicker.vue";

export default {
    name: "LoungeReport",
    components: { DateRangePicker },
    data() {
        return {
            loading: false,
            history: [],
        };
    },
    // earnings computed below
    computed: {
        activeDays() {
            return Object.keys(this.byDay()).length;
        },
        totalEarned() {
            return this.history.reduce(
                (sum, s) => sum + (Number(s.total_bill) || 0),
                0,
            );
        },
        bestEarningDay() {
            const byDay = {};
            this.history.forEach((s) => {
                const day = this.dayLabel(s.checked_in_at);
                byDay[day] = (byDay[day] || 0) + (Number(s.total_bill) || 0);
            });
            return this.topEntry(byDay);
        },
        bestSessionDay() {
            const grouped = this.byDay();
            return this.topEntry(grouped);
        },
        chartData() {
            const grouped = this.byDay();
            const labels = Object.keys(grouped);
            return {
                labels,
                datasets: [
                    {
                        label: "Sessions",
                        data: labels.map((l) => grouped[l]),
                        backgroundColor: "#6366f1",
                        borderRadius: 4,
                    },
                ],
            };
        },
        chartOptions() {
            return {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
            };
        },
    },
    methods: {
        dayLabel(dateStr) {
            return new Date(dateStr).toLocaleDateString("en-PH", {
                month: "short",
                day: "numeric",
            });
        },
        topEntry(map) {
            // Returns { label, value } for the biggest entry, or null if empty
            let best = null;
            for (const [label, value] of Object.entries(map)) {
                if (!best || value > best.value) best = { label, value };
            }
            return best;
        },
        byDay() {
            const grouped = {};
            this.history.forEach((s) => {
                grouped[this.dayLabel(s.checked_in_at)] =
                    (grouped[this.dayLabel(s.checked_in_at)] || 0) + 1;
            });
            return grouped;
        },
        headers() {
            const token = localStorage.getItem("auth-token");
            return { Authorization: `Bearer ${token}` };
        },
        async onRangeChange({ from, to }) {
            this.loading = true;
            try {
                const res = await axios.get("/api/lounge/session-history", {
                    headers: this.headers(),
                    params: { from, to },
                });
                if (res.data.success) this.history = res.data.sessions;
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
