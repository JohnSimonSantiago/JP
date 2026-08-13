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
            <div class="grid grid-cols-2 gap-3 mb-6">
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
    computed: {
        activeDays() {
            return Object.keys(this.byDay()).length;
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
        byDay() {
            const grouped = {};
            this.history.forEach((s) => {
                const day = new Date(s.checked_in_at).toLocaleDateString(
                    "en-PH",
                    {
                        month: "short",
                        day: "numeric",
                    },
                );
                grouped[day] = (grouped[day] || 0) + 1;
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
