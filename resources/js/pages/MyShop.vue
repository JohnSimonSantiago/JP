<template>
    <Layout>
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Header -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <div
                        class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                    >
                        <div>
                            <h1
                                class="text-4xl font-bold text-gray-800 mb-2 flex items-center gap-3"
                            >
                                <i class="pi pi-shop text-purple-500"></i>
                                My Shop Dashboard
                            </h1>
                            <p class="text-gray-600">
                                Manage your shop and track your performance
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <button
                                @click="$router.push(`/shop/${shop?.id}`)"
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors flex items-center gap-2"
                            >
                                <i class="pi pi-external-link"></i>
                                View Shop
                            </button>
                            <button
                                @click="showEditShop = true"
                                class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-colors flex items-center gap-2"
                            >
                                <i class="pi pi-cog"></i>
                                Settings
                            </button>
                        </div>
                    </div>
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
                    <h3 class="text-lg font-medium text-gray-600 mb-2">
                        You don't have a shop yet
                    </h3>
                    <p class="text-gray-500 mb-6">
                        Create your shop to start selling items and earning
                        points
                    </p>
                    <button
                        @click="showCreateShop = true"
                        class="px-6 py-3 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-colors"
                    >
                        Create My Shop
                    </button>
                </div>

                <!-- Dashboard Content -->
                <div v-else class="space-y-6">
                    <!-- Statistics Cards -->
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
                    >
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">
                                        Total Items
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-purple-600"
                                    >
                                        {{ statistics?.total_items || 0 }}
                                    </p>
                                </div>
                                <i
                                    class="pi pi-box text-purple-500 text-2xl"
                                ></i>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">
                                        Total Sales
                                    </p>
                                    <p
                                        class="text-2xl font-bold text-green-600"
                                    >
                                        {{ statistics?.total_sales || 0 }}
                                    </p>
                                </div>
                                <i
                                    class="pi pi-shopping-cart text-green-500 text-2xl"
                                ></i>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Revenue</p>
                                    <p
                                        class="text-2xl font-bold text-yellow-600"
                                    >
                                        {{
                                            formatPoints(
                                                statistics?.total_revenue || 0
                                            )
                                        }}
                                    </p>
                                </div>
                                <i
                                    class="pi pi-dollar text-yellow-500 text-2xl"
                                ></i>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">
                                        Followers
                                    </p>
                                    <p class="text-2xl font-bold text-blue-600">
                                        {{ statistics?.followers || 0 }}
                                    </p>
                                </div>
                                <i
                                    class="pi pi-users text-blue-500 text-2xl"
                                ></i>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Orders -->
                    <div
                        v-if="pendingOrders.length > 0"
                        class="bg-white rounded-xl shadow-lg p-6"
                    >
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold text-gray-800">
                                Pending Orders ({{ pendingOrders.length }})
                            </h2>
                            <button
                                @click="fetchDashboard"
                                class="text-blue-500 hover:text-blue-700"
                            >
                                <i class="pi pi-refresh mr-1"></i>Refresh
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="order in pendingOrders"
                                :key="order.id"
                                class="border border-yellow-200 bg-yellow-50 rounded-lg p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-semibold text-gray-800">
                                            {{ order.user.name }}
                                        </h4>
                                        <p class="text-sm text-gray-600">
                                            wants to buy {{ order.quantity }}x
                                            {{ order.shop_item.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ formatDate(order.created_at) }}
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click="approveOrder(order.id)"
                                            class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            @click="rejectOrder(order)"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">
                            Recent Orders
                        </h2>

                        <div
                            v-if="recentOrders.length === 0"
                            class="text-center py-8"
                        >
                            <i
                                class="pi pi-shopping-cart text-gray-300 text-3xl mb-2"
                            ></i>
                            <p class="text-gray-600">No orders yet</p>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="order in recentOrders"
                                :key="order.id"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                            >
                                <div>
                                    <p class="font-medium">
                                        {{ order.user.name }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ order.quantity }}x
                                        {{ order.shop_item.name }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span
                                        :class="getStatusColor(order.status)"
                                        class="px-2 py-1 rounded-full text-xs font-medium"
                                    >
                                        {{ order.status }}
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ formatDate(order.created_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Shop Dialog -->
        <div
            v-if="showCreateShop"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
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
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
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
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
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
                            class="flex-1 bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50"
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
    </Layout>
</template>

<script>
export default {
    data() {
        return {
            loading: true,
            shop: null,
            statistics: null,
            recentOrders: [],
            pendingOrders: [],

            // Create shop
            showCreateShop: false,
            creatingShop: false,
            newShop: {
                name: "",
                description: "",
            },

            // Edit shop
            showEditShop: false,
        };
    },

    methods: {
        async fetchDashboard() {
            try {
                this.loading = true;
                const response = await axios.get(
                    "/api/shops/dashboard/my-shop"
                );

                if (response.data.success) {
                    this.shop = response.data.shop;
                    this.statistics = response.data.statistics;
                    this.recentOrders = response.data.recent_orders || [];
                    this.pendingOrders = response.data.pending_orders || [];
                }
            } catch (error) {
                console.error("Error fetching dashboard:", error);
                if (error.response?.status === 404) {
                    this.shop = null; // User doesn't have a shop
                } else {
                    this.$toast?.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Failed to load dashboard",
                    });
                }
            } finally {
                this.loading = false;
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
                    await this.fetchDashboard();
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

        async approveOrder(orderId) {
            try {
                const response = await axios.post(
                    `/api/shop-items/purchases/${orderId}/approve`
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Order approved successfully",
                    });

                    await this.fetchDashboard();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to approve order",
                });
            }
        },

        async rejectOrder(order) {
            const reason = prompt("Reason for rejection (optional):");
            if (reason === null) return; // User cancelled

            try {
                const response = await axios.post(
                    `/api/shop-items/purchases/${order.id}/reject`,
                    {
                        reason: reason,
                    }
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Order rejected and refunded",
                    });

                    await this.fetchDashboard();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to reject order",
                });
            }
        },

        getStatusColor(status) {
            const colors = {
                pending: "bg-yellow-100 text-yellow-800",
                completed: "bg-green-100 text-green-800",
                rejected: "bg-red-100 text-red-800",
            };
            return colors[status] || "bg-gray-100 text-gray-800";
        },

        formatPoints(points) {
            return (points || 0).toLocaleString();
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },
    },

    async mounted() {
        await this.fetchDashboard();
    },
};
</script>
