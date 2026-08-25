<template>
    <div>
        <!-- Header + Date Range -->
        <div
            class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6"
        >
            <h3 class="text-lg font-semibold">Reports</h3>
            <DateRangePicker @change="onRangeChange" />
        </div>

        <!-- Summary Stat Cards (driven by the selected range) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Items (all-time count, not range-bound) -->
            <div class="bg-white p-6 rounded-xl shadow border">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="pi pi-box text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Total Items</p>
                        <p class="text-2xl font-bold">
                            {{ statistics?.total_items || 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Orders in range -->
            <div class="bg-white p-6 rounded-xl shadow border">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i
                            class="pi pi-shopping-cart text-green-600 text-xl"
                        ></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Orders</p>
                        <p class="text-2xl font-bold">
                            {{ stats.summary.orders || 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Items sold in range -->
            <div class="bg-white p-6 rounded-xl shadow border">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="pi pi-tags text-yellow-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Items Sold</p>
                        <p class="text-2xl font-bold">
                            {{ stats.summary.items_sold || 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Revenue in range -->
            <div class="bg-white p-6 rounded-xl shadow border">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="pi pi-dollar text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-600">Revenue</p>
                        <p class="text-2xl font-bold">
                            ₱{{ formatCash(stats.summary.revenue || 0) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loadingStats" class="flex justify-center py-12">
            <i class="pi pi-spin pi-spinner text-purple-500 text-3xl"></i>
        </div>

        <!-- Charts -->
        <div v-else class="space-y-8">
            <!-- Revenue over time -->
            <div class="bg-white p-6 rounded-xl shadow border">
                <h4 class="text-md font-semibold text-gray-800 mb-4">
                    Revenue Over Time
                </h4>
                <div v-if="stats.by_day.length > 0">
                    <Chart
                        type="line"
                        :data="revenueChartData"
                        :options="chartOptions"
                        class="h-64"
                    />
                </div>
                <p v-else class="text-gray-400 text-sm py-8 text-center">
                    No sales in this period
                </p>
            </div>

            <!-- Orders over time -->
            <div class="bg-white p-6 rounded-xl shadow border">
                <h4 class="text-md font-semibold text-gray-800 mb-4">
                    Orders Over Time
                </h4>
                <div v-if="stats.by_day.length > 0">
                    <Chart
                        type="bar"
                        :data="ordersChartData"
                        :options="chartOptions"
                        class="h-64"
                    />
                </div>
                <p v-else class="text-gray-400 text-sm py-8 text-center">
                    No orders in this period
                </p>
            </div>

            <!-- Top items -->
            <div class="bg-white p-6 rounded-xl shadow border">
                <h4 class="text-md font-semibold text-gray-800 mb-4">
                    Top Items
                </h4>
                <div v-if="stats.top_items.length > 0">
                    <Chart
                        type="bar"
                        :data="topItemsChartData"
                        :options="horizontalChartOptions"
                        class="h-64"
                    />
                </div>
                <p v-else class="text-gray-400 text-sm py-8 text-center">
                    No items sold in this period
                </p>
            </div>
        </div>

        <!-- Monthly Sales Report (CSV) -->
        <div class="bg-white p-6 rounded-xl shadow border mt-8 max-w-xl">
            <h4 class="text-md font-semibold text-gray-800 mb-4">
                Download Monthly Report (CSV)
            </h4>
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Month
                    </label>
                    <select
                        v-model.number="reportMonth"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    >
                        <option v-for="m in 12" :key="m" :value="m">
                            {{ monthName(m) }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Year
                    </label>
                    <input
                        v-model.number="reportYear"
                        type="number"
                        class="w-28 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    />
                </div>
                <button
                    @click="downloadReport"
                    class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                >
                    <i class="pi pi-download"></i>
                    Download CSV
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import DateRangePicker from "../../components/DateRangePicker.vue";

export default {
    name: "ReportsTab",
    components: { DateRangePicker },
    props: {
        statistics: { type: Object, default: () => ({}) },
        shop: { type: Object, required: true },
    },
    data() {
        const now = new Date();
        return {
            reportMonth: now.getMonth() + 1,
            reportYear: now.getFullYear(),
            loadingStats: false,
            range: { from: null, to: null },
            stats: {
                summary: { revenue: 0, orders: 0, items_sold: 0 },
                by_day: [],
                top_items: [],
            },
            chartOptions: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true },
                },
            },
            horizontalChartOptions: {
                indexAxis: "y",
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true },
                },
            },
        };
    },
    computed: {
        revenueChartData() {
            return {
                labels: this.stats.by_day.map((d) => d.date),
                datasets: [
                    {
                        label: "Revenue",
                        data: this.stats.by_day.map((d) => d.revenue),
                        borderColor: "#22c55e",
                        backgroundColor: "rgba(34,197,94,0.15)",
                        fill: true,
                        tension: 0.3,
                    },
                ],
            };
        },
        ordersChartData() {
            return {
                labels: this.stats.by_day.map((d) => d.date),
                datasets: [
                    {
                        label: "Orders",
                        data: this.stats.by_day.map((d) => d.orders),
                        backgroundColor: "#8b5cf6",
                    },
                ],
            };
        },
        topItemsChartData() {
            return {
                labels: this.stats.top_items.map((i) => i.name),
                datasets: [
                    {
                        label: "Qty Sold",
                        data: this.stats.top_items.map((i) => i.qty),
                        backgroundColor: "#3b82f6",
                    },
                ],
            };
        },
    },
    methods: {
        formatCash(amount) {
            return parseFloat(amount || 0).toFixed(2);
        },
        monthName(m) {
            return new Date(2000, m - 1, 1).toLocaleString("default", {
                month: "long",
            });
        },
        onRangeChange(range) {
            console.log("Range emitted:", range);
            this.range = range;
            this.fetchStats();
        },
        async fetchStats() {
            try {
                this.loadingStats = true;
                const params = {};
                if (this.range.from) params.from = this.range.from;
                if (this.range.to) params.to = this.range.to;

                const response = await axios.get(
                    `/api/shops/${this.shop.id}/items/sales-stats`,
                    { params },
                );

                if (response.data.success) {
                    this.stats = {
                        summary: response.data.summary,
                        by_day: response.data.by_day,
                        top_items: response.data.top_items,
                    };
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load report stats",
                });
            } finally {
                this.loadingStats = false;
            }
        },
        async downloadReport() {
            try {
                const url = `/api/shops/${this.shop.id}/items/sales-report?month=${this.reportMonth}&year=${this.reportYear}`;
                const response = await axios.get(url, { responseType: "blob" });

                const blob = new Blob([response.data], { type: "text/csv" });
                const link = document.createElement("a");
                link.href = window.URL.createObjectURL(blob);
                link.download = `sales-${this.shop.name}-${this.reportYear}-${this.reportMonth}.csv`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(link.href);
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to download report",
                });
            }
        },
    },
};
</script>
