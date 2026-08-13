<template>
    <Layout>
        <div class="max-w-5xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-800 mb-1">Statistics</h1>
            <p class="text-sm text-gray-400 mb-6">Reports and insights</p>

            <!-- Report tabs -->
            <div
                class="flex gap-1 border-b border-gray-200 mb-6 overflow-x-auto"
            >
                <button
                    v-for="t in tabs"
                    :key="t.key"
                    @click="active = t.key"
                    class="px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 -mb-px transition-colors"
                    :class="
                        active === t.key
                            ? 'border-indigo-600 text-indigo-700'
                            : 'border-transparent text-gray-500 hover:text-gray-700'
                    "
                >
                    {{ t.label }}
                </button>
            </div>

            <!-- Active report -->
            <div class="bg-white rounded-xl shadow-sm p-4 md:p-6">
                <component :is="activeComponent" :key="active" />
            </div>
        </div>
    </Layout>
</template>

<script>
import BuyTimeReport from "@/pages/reports/BuyTimeReport.vue";
import LoungeReport from "@/pages/reports/LoungeReport.vue";
import ConsumableTimeBoughtReport from "@/pages/reports/ConsumableTimeBoughtReport.vue";

export default {
    name: "Statistics",
    components: { BuyTimeReport, LoungeReport, ConsumableTimeBoughtReport },
    data() {
        return {
            active: "buyTime",
            tabs: [
                {
                    key: "buyTime",
                    label: "Buy Time",
                    component: "BuyTimeReport",
                },
                { key: "lounge", label: "Lounge", component: "LoungeReport" },
                {
                    key: "consumableTimeBought",
                    label: "Time Bought",
                    component: "ConsumableTimeBoughtReport",
                },
                // Add future reports here:
                // { key: "monthly", label: "Monthly", component: "MonthlyReport" },
            ],
        };
    },
    computed: {
        activeComponent() {
            return this.tabs.find((t) => t.key === this.active)?.component;
        },
    },
};
</script>
