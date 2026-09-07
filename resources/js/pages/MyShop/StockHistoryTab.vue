<template>
    <div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Stock History</h3>
            <button
                @click="fetchHistory()"
                :disabled="loading"
                class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50"
                title="Refresh"
            >
                <i
                    :class="loading ? 'pi pi-spin pi-spinner' : 'pi pi-refresh'"
                ></i>
            </button>
        </div>

        <div class="mb-6">
            <DateRangePicker @change="onRangeChange" />
        </div>

        <!-- List -->
        <div v-if="adjustments.length > 0" class="space-y-3">
            <div
                v-for="adj in adjustments"
                :key="adj.id"
                class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg"
            >
                <img
                    v-if="adj.shop_item && adj.shop_item.image"
                    :src="`/storage/${adj.shop_item.image}`"
                    :alt="adj.shop_item.name"
                    class="w-10 h-10 object-cover rounded-lg flex-shrink-0"
                />
                <div
                    v-else
                    class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0"
                >
                    <i class="pi pi-image text-gray-400 text-sm"></i>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="font-medium truncate">
                        {{ adj.shop_item?.name || "Deleted item" }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ formatDate(adj.created_at) }}
                        <span v-if="adj.user"> · by {{ adj.user.name }}</span>
                    </p>
                </div>

                <div class="text-right flex-shrink-0">
                    <span
                        :class="[
                            'text-sm font-bold px-2 py-1 rounded-full',
                            adj.quantity > 0
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700',
                        ]"
                    >
                        {{ adj.quantity > 0 ? "+" : "" }}{{ adj.quantity }}
                    </span>
                    <p class="text-xs text-gray-500 mt-1">
                        → {{ adj.stock_after }} left
                    </p>
                </div>
            </div>

            <!-- Load more -->
            <button
                v-if="hasMore"
                @click="fetchHistory(true)"
                :disabled="loading"
                class="w-full py-3 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
            >
                <i v-if="loading" class="pi pi-spin pi-spinner mr-2"></i>
                Load more
            </button>
        </div>

        <!-- Empty -->
        <div v-else-if="!loading" class="text-center py-12">
            <i class="pi pi-history text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-600 mb-2">
                No stock changes yet
            </h3>
            <p class="text-gray-500">
                Restock or dispose adjustments will show up here.
            </p>
        </div>

        <!-- Initial loading -->
        <div v-else class="text-center py-12">
            <i class="pi pi-spin pi-spinner text-gray-400 text-3xl"></i>
        </div>
    </div>
</template>

<script>
import DateRangePicker from "../../components/DateRangePicker.vue";

export default {
    name: "StockHistoryTab",
    components: { DateRangePicker },
    props: {
        shop: { type: Object, required: true },
    },
    data() {
        return {
            adjustments: [],
            loading: false,
            currentPage: 0,
            lastPage: 1,
            range: { from: null, to: null },
        };
    },
    computed: {
        hasMore() {
            return this.currentPage < this.lastPage;
        },
    },
    methods: {
        onRangeChange(range) {
            this.range = range;
            this.fetchHistory(); // reset to page 1 with new window
        },

        async fetchHistory(append = false) {
            try {
                this.loading = true;
                const nextPage = append ? this.currentPage + 1 : 1;

                const response = await axios.get(
                    `/api/shops/${this.shop.id}/items/stock-history`,
                    {
                        params: {
                            page: nextPage,
                            per_page: 20,
                            from: this.range.from,
                            to: this.range.to,
                        },
                    },
                );

                if (response.data.success) {
                    const paginated = response.data.adjustments;
                    if (append) {
                        this.adjustments.push(...paginated.data);
                    } else {
                        this.adjustments = paginated.data;
                    }
                    this.currentPage = paginated.current_page;
                    this.lastPage = paginated.last_page;
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load stock history",
                });
            } finally {
                this.loading = false;
            }
        },

        formatDate(dateStr) {
            const d = new Date(dateStr);
            return d.toLocaleDateString("en-PH", {
                month: "short",
                day: "numeric",
                year: "numeric",
                hour: "numeric",
                minute: "2-digit",
            });
        },
    },
};
</script>
