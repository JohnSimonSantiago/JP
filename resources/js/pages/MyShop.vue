<template>
    <Layout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        My Shop Dashboard
                    </h1>
                    <p class="text-gray-600 mt-2">Manage your shop and items</p>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center py-12">
                    <i
                        class="pi pi-spin pi-spinner text-purple-500 text-3xl"
                    ></i>
                </div>

                <!-- No Shop State -->
                <div
                    v-else-if="!shop"
                    class="bg-white rounded-xl shadow-lg p-12 text-center"
                >
                    <i class="pi pi-shop text-gray-300 text-5xl mb-4"></i>
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">
                        No Shop Found
                    </h2>
                    <p class="text-gray-600 mb-6">
                        You don't have a shop yet. Create one to start selling!
                    </p>
                    <button
                        @click="showCreateShop = true"
                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg transition-colors"
                    >
                        <i class="pi pi-plus mr-2"></i>
                        Create My Shop
                    </button>
                </div>

                <!-- Shop Dashboard -->
                <div v-else class="space-y-8">
                    <!-- Tab Navigation -->
                    <div class="bg-white rounded-xl shadow-lg mb-8">
                        <div class="border-b border-gray-200">
                            <nav
                                class="grid grid-cols-2 sm:flex sm:space-x-8 px-6"
                            >
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.id"
                                    @click="activeTab = tab.id"
                                    :class="[
                                        'py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2 justify-center sm:justify-start',
                                        activeTab === tab.id
                                            ? 'border-purple-500 text-purple-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    ]"
                                >
                                    <i :class="tab.icon"></i>
                                    <span>{{ tab.name }}</span>
                                    <span
                                        v-if="tab.count && tab.count > 0"
                                        :class="[
                                            'ml-1 px-2 py-0.5 text-xs rounded-full font-semibold',
                                            activeTab === tab.id
                                                ? 'bg-purple-100 text-purple-700'
                                                : 'bg-red-500 text-white',
                                        ]"
                                    >
                                        {{ tab.count > 99 ? "99+" : tab.count }}
                                    </span>
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content -->
                        <div class="p-6">
                            <ItemsTab
                                v-if="activeTab === 'items'"
                                :shop="shop"
                                :items="items"
                                @items-changed="fetchItems"
                            />

                            <OrdersTab
                                v-if="activeTab === 'orders'"
                                :shop="shop"
                                :pending-orders="pendingOrders"
                                :recent-orders="recentOrders"
                                @orders-changed="fetchShopData"
                            />

                            <LoyaltyTab
                                v-if="activeTab === 'loyalty'"
                                :shop="shop"
                                @pending-count-changed="
                                    pendingLoyaltyCount = $event
                                "
                            />

                            <SettingsTab
                                v-if="activeTab === 'settings'"
                                :shop="shop"
                                @shop-updated="shop = $event"
                            />

                            <ReportsTab
                                v-if="activeTab === 'reports'"
                                :statistics="statistics"
                                :shop="shop"
                            />

                            <EarningsTab
                                v-if="activeTab === 'earnings'"
                                :shop="shop"
                            />
                        </div>
                    </div>
                </div>

                <!-- Create Shop Dialog -->
                <div
                    v-if="showCreateShop"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                >
                    <div
                        class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6"
                    >
                        <h3 class="text-xl font-bold text-gray-800 mb-4">
                            Create Your Shop
                        </h3>
                        <form @submit.prevent="createShop">
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Shop Name *
                                    </label>
                                    <input
                                        v-model="newShop.name"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Enter shop name"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Description
                                    </label>
                                    <textarea
                                        v-model="newShop.description"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Describe your shop..."
                                    ></textarea>
                                </div>
                            </div>
                            <div class="flex gap-3 mt-6">
                                <button
                                    type="button"
                                    @click="showCreateShop = false"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="creatingShop"
                                    class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50"
                                >
                                    <i
                                        v-if="creatingShop"
                                        class="pi pi-spin pi-spinner mr-2"
                                    ></i>
                                    Create Shop
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
import ItemsTab from "./MyShop/ItemsTab.vue";
import OrdersTab from "./MyShop/OrdersTab.vue";
import LoyaltyTab from "./MyShop/LoyaltyTab.vue";
import SettingsTab from "./MyShop/SettingsTab.vue";
import ReportsTab from "./MyShop/ReportsTab.vue";
import EarningsTab from "./MyShop/EarningsTab.vue";

export default {
    components: {
        ItemsTab,
        OrdersTab,
        LoyaltyTab,
        SettingsTab,
        ReportsTab,
        EarningsTab,
    },

    data() {
        return {
            loading: true,
            shop: null,
            statistics: null,
            items: [],
            recentOrders: [],
            pendingOrders: [],

            user: null,

            activeTab: "items",

            showCreateShop: false,
            creatingShop: false,
            newShop: {
                name: "",
                description: "",
            },

            pendingLoyaltyCount: 0,
        };
    },

    computed: {
        isAdmin() {
            return this.user && this.user.role === "admin";
        },
        pendingOrdersCount() {
            return this.pendingOrders ? this.pendingOrders.length : 0;
        },
        tabs() {
            return [
                { id: "items", name: "Items", icon: "pi pi-box" },
                {
                    id: "orders",
                    name: "Orders",
                    icon: "pi pi-shopping-cart",
                    count: this.pendingOrdersCount,
                },
                {
                    id: "loyalty",
                    name: "Loyalty Card",
                    icon: "pi pi-gift",
                    count: this.pendingLoyaltyCount,
                },
                { id: "settings", name: "Settings", icon: "pi pi-cog" },
                { id: "reports", name: "Reports", icon: "pi pi-chart-bar" },
                { id: "earnings", name: "Earnings", icon: "pi pi-wallet" },
            ];
        },
    },

    async mounted() {
        await this.fetchUserData();
        await this.fetchShopData();
    },

    methods: {
        async fetchShopData() {
            try {
                this.loading = true;

                let response;
                try {
                    response = await axios.get("/api/shops/dashboard/my-shop");
                } catch (error) {
                    if (this.isAdmin && error.response?.status === 404) {
                        console.log(
                            "Admin shop not found via my-shop endpoint, trying alternative...",
                        );
                        this.shop = null;
                        this.loading = false;
                        return;
                    }
                    throw error;
                }

                if (response.data.success) {
                    this.shop = response.data.shop;
                    this.statistics = response.data.statistics;
                    this.recentOrders = response.data.recent_orders;
                    this.pendingOrders = response.data.pending_orders;

                    await this.fetchItems();
                }
            } catch (error) {
                console.error("Error fetching shop data:", error);
                if (error.response?.status === 404) {
                    this.shop = null;
                } else {
                    this.$toast?.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Failed to load shop data",
                    });
                }
            } finally {
                this.loading = false;
            }
        },

        async fetchItems() {
            if (!this.shop) return;

            try {
                const response = await axios.get(
                    `/api/shops/${this.shop.id}/items`,
                );
                if (response.data.success) {
                    this.items = response.data.items;
                }
            } catch (error) {
                console.error("Error fetching items:", error);
            }
        },

        async fetchUserData() {
            try {
                const user = JSON.parse(localStorage.getItem("user") || "null");
                this.user = user;
            } catch (error) {
                console.error("Error getting user data:", error);
            }
        },

        async createShop() {
            try {
                this.creatingShop = true;
                const response = await axios.post("/api/shops", this.newShop);

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    this.showCreateShop = false;
                    this.newShop = { name: "", description: "" };
                    await this.fetchShopData();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to create shop",
                });
            } finally {
                this.creatingShop = false;
            }
        },

        formatCash(amount) {
            return parseFloat(amount || 0).toFixed(2);
        },
    },
};
</script>
