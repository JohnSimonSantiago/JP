<template>
    <div>
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="font-semibold text-gray-800">Buy Time Report</h3>
            <DateRangePicker @change="onRangeChange" />
        </div>

        <div v-if="loading" class="text-center py-8 text-gray-400">
            <i class="pi pi-spin pi-spinner text-xl"></i>
        </div>

        <template v-else>
            <!-- Totals -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-green-50 rounded-lg p-3">
                    <p class="text-xs text-green-400">Cash</p>
                    <p class="text-lg font-bold text-green-600">
                        ₱{{ totalCash }}
                    </p>
                </div>
                <div class="bg-blue-50 rounded-lg p-3">
                    <p class="text-xs text-blue-400">App Balance</p>
                    <p class="text-lg font-bold text-blue-600">
                        ₱{{ totalBalance }}
                    </p>
                </div>
                <div class="bg-indigo-50 rounded-lg p-3">
                    <p class="text-xs text-indigo-400">Total</p>
                    <p class="text-lg font-bold text-indigo-700">
                        ₱{{ totalAmount }}
                    </p>
                </div>
                <div class="bg-purple-50 rounded-lg p-3">
                    <p class="text-xs text-purple-400">Hours Sold</p>
                    <p class="text-lg font-bold text-purple-700">
                        {{ formatMinutes(totalMinutesSold) }}
                    </p>
                </div>
            </div>

            <!-- Daily revenue chart -->
            <div
                v-if="history.length"
                class="bg-white rounded-lg p-4 border border-gray-100"
            >
                <p class="text-sm font-medium text-gray-600 mb-3">
                    Revenue per day
                </p>
                <Chart
                    type="bar"
                    :data="chartData"
                    :options="chartOptions"
                    class="h-64"
                />
            </div>
            <div v-else class="text-center py-8 text-gray-400 text-sm">
                No purchases for this range.
            </div>
        </template>
    </div>
</template>

<script>
import axios from "axios";
import DateRangePicker from "@/components/DateRangePicker.vue";

export default {
    name: "BuyTimeReport",
    components: { DateRangePicker },
    data() {
        return {
            loading: false,
            history: [],
            totalCash: 0,
            totalBalance: 0,
            totalAmount: 0,
            totalMinutesSold: 0,
        };
    },
    computed: {
        chartData() {
            const byDay = {};
            this.history.forEach((p) => {
                const day = new Date(p.created_at).toLocaleDateString("en-PH", {
                    month: "short",
                    day: "numeric",
                });
                byDay[day] = (byDay[day] || 0) + Number(p.amount || 0);
            });
            const labels = Object.keys(byDay);
            return {
                labels,
                datasets: [
                    {
                        label: "Revenue (₱)",
                        data: labels.map((l) => byDay[l]),
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
                scales: { y: { beginAtZero: true } },
            };
        },
    },
    methods: {
        formatMinutes(mins) {
            mins = mins || 0;
            const h = Math.floor(mins / 60);
            const m = mins % 60;
            return `${h}h ${m}m`;
        },
        async onRangeChange({ from, to }) {
            this.loading = true;
            try {
                const res = await axios.get("/api/lounge/consumable/history", {
                    params: { from, to },
                });
                if (res.data.success) {
                    this.history = res.data.purchases;
                    this.totalCash = Number(res.data.total_cash) || 0;
                    this.totalBalance = Number(res.data.total_balance) || 0;
                    this.totalAmount = Number(res.data.total_amount) || 0;
                    this.totalMinutesSold = Number(res.data.total_minutes) || 0;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
