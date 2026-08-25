<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold">Earnings</h3>
            <button
                @click="fetchPayouts"
                class="text-sm text-purple-600 hover:text-purple-800 flex items-center gap-1"
            >
                <i class="pi pi-refresh" :class="{ 'pi-spin': loading }"></i>
                Refresh
            </button>
        </div>

        <div v-if="loading" class="flex justify-center py-12">
            <i class="pi pi-spin pi-spinner text-purple-500 text-3xl"></i>
        </div>

        <template v-else>
            <!-- Balance card -->
            <div
                class="bg-gradient-to-br from-purple-50 to-indigo-50 border border-purple-100 rounded-xl p-6 mb-6 text-center"
            >
                <p class="text-xs text-purple-500 uppercase tracking-wide">
                    You are owed
                </p>
                <p class="text-4xl font-bold text-purple-700 my-2">
                    ₱{{ formatCash(data.balance || 0) }}
                </p>
                <p class="text-sm text-gray-500">
                    from {{ data.claimable_orders || 0 }} completed order{{
                        (data.claimable_orders || 0) === 1 ? "" : "s"
                    }}
                </p>
                <p class="text-xs text-gray-400 mt-3">
                    Payouts are processed by Level Lounge. This balance is what
                    you'll receive on your next payout.
                </p>
            </div>

            <!-- History -->
            <h4 class="text-md font-semibold text-gray-800 mb-3">
                Payout History
            </h4>
            <div v-if="data.history && data.history.length" class="space-y-3">
                <div
                    v-for="p in data.history"
                    :key="p.id"
                    class="bg-white border rounded-lg p-4 flex items-start justify-between shadow-sm"
                >
                    <div>
                        <div class="text-lg font-semibold text-gray-800">
                            ₱{{ formatCash(p.amount) }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ formatDate(p.created_at) }}
                            · {{ p.order_count }} order{{
                                p.order_count === 1 ? "" : "s"
                            }}
                        </div>
                        <div
                            v-if="p.note"
                            class="text-sm text-gray-500 italic mt-1"
                        >
                            {{ p.note }}
                        </div>
                    </div>
                    <span
                        class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700"
                    >
                        Paid
                    </span>
                </div>
            </div>
            <div v-else class="text-center py-10 text-gray-400">
                <i class="pi pi-wallet text-4xl mb-3 block text-gray-300"></i>
                <p class="text-sm">No payouts yet</p>
            </div>
        </template>
    </div>
</template>

<script>
export default {
    name: "EarningsTab",
    props: {
        shop: { type: Object, required: true },
    },
    data() {
        return {
            loading: false,
            data: {
                balance: 0,
                claimable_orders: 0,
                history: [],
            },
        };
    },
    methods: {
        formatCash(amount) {
            return parseFloat(amount || 0).toFixed(2);
        },
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString("default", {
                year: "numeric",
                month: "short",
                day: "numeric",
            });
        },
        async fetchPayouts() {
            try {
                this.loading = true;
                const response = await axios.get(
                    `/api/shops/${this.shop.id}/items/payouts`,
                );
                if (response.data.success) {
                    this.data = {
                        balance: response.data.balance,
                        claimable_orders: response.data.claimable_orders,
                        history: response.data.history,
                    };
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load earnings",
                });
            } finally {
                this.loading = false;
            }
        },
    },
    mounted() {
        this.fetchPayouts();
    },
};
</script>
