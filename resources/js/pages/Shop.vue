<template>
    <Layout>
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="max-w-6xl mx-auto space-y-6">
                <!-- Header -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <div
                        class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                    >
                        <div>
                            <h1
                                class="text-4xl font-bold text-gray-800 mb-2 flex items-center gap-3"
                            >
                                <i
                                    class="pi pi-shopping-cart text-purple-500"
                                ></i>
                                Point Shop
                            </h1>
                            <p class="text-gray-600">
                                Spend your points on amazing items
                            </p>
                        </div>
                        <div class="bg-purple-100 px-4 py-2 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-wallet text-purple-600"></i>
                                <span class="text-lg font-bold text-purple-700">
                                    {{
                                        formatPoints(currentUser.points || 0)
                                    }}
                                    Points
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center py-12">
                    <i
                        class="pi pi-spin pi-spinner text-purple-500 text-3xl"
                    ></i>
                </div>

                <!-- Shop Items Grid -->
                <div
                    v-else-if="items.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                >
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow"
                    >
                        <!-- Item Image -->
                        <div
                            class="aspect-square bg-gray-100 flex items-center justify-center"
                        >
                            <img
                                v-if="item.image"
                                :src="`/storage/${item.image}`"
                                :alt="item.name"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="text-gray-400">
                                <i
                                    :class="getCategoryIcon(item.category)"
                                    class="text-5xl"
                                ></i>
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-2">
                                <h3
                                    class="text-lg font-bold text-gray-800 line-clamp-2"
                                >
                                    {{ item.name }}
                                </h3>
                            </div>

                            <p
                                v-if="item.description"
                                class="text-sm text-gray-600 mb-4 line-clamp-3"
                            >
                                {{ item.description }}
                            </p>

                            <!-- Stock Info -->
                            <div v-if="item.stock !== null" class="mb-3">
                                <div
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="text-gray-500">Stock:</span>
                                    <span
                                        :class="
                                            item.stock > 10
                                                ? 'text-green-600'
                                                : item.stock > 0
                                                ? 'text-yellow-600'
                                                : 'text-red-600'
                                        "
                                    >
                                        {{
                                            item.stock > 0
                                                ? `${item.stock} left`
                                                : "Out of stock"
                                        }}
                                    </span>
                                </div>
                            </div>

                            <!-- Price and Buy Button -->
                            <div class="flex items-center justify-between">
                                <div class="text-xl font-bold text-purple-600">
                                    {{ formatPoints(item.price) }}
                                    <span class="text-sm text-gray-500"
                                        >points</span
                                    >
                                </div>
                                <button
                                    @click="buyItem(item)"
                                    :disabled="
                                        !canBuyItem(item) ||
                                        purchasingItem === item.id
                                    "
                                    :class="
                                        canBuyItem(item)
                                            ? 'bg-purple-500 hover:bg-purple-600 text-white'
                                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                    "
                                    class="px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                                >
                                    <i
                                        v-if="purchasingItem === item.id"
                                        class="pi pi-spin pi-spinner"
                                    ></i>
                                    <i v-else class="pi pi-shopping-cart"></i>
                                    {{ getButtonText(item) }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="bg-white rounded-xl shadow-lg p-12 text-center"
                >
                    <i
                        class="pi pi-shopping-bag text-gray-300 text-5xl mb-4"
                    ></i>
                    <h3 class="text-lg font-medium text-gray-600 mb-2">
                        No Items Available
                    </h3>
                    <p class="text-gray-500">The shop is currently empty</p>
                </div>

                <!-- Purchase History -->
                <div
                    v-if="showPurchaseHistory"
                    class="bg-white rounded-xl shadow-lg p-6"
                >
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800">
                            Purchase History
                        </h2>
                        <button
                            @click="showPurchaseHistory = false"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <i class="pi pi-times"></i>
                        </button>
                    </div>
                    <!-- Purchase history content would go here -->
                </div>

                <!-- Admin Panel -->
                <div v-if="isAdmin" class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">
                        Admin Panel
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <button
                            @click="showPendingPurchases"
                            class="bg-yellow-500 text-white px-4 py-3 rounded-lg hover:bg-yellow-600 transition-colors"
                        >
                            <i class="pi pi-clock mr-2"></i>
                            <div>
                                <div class="font-medium">Pending Orders</div>
                                <div class="text-sm opacity-90">
                                    {{ pendingPurchases.length }} waiting
                                </div>
                            </div>
                        </button>
                        <button
                            class="bg-blue-500 text-white px-4 py-3 rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            <i class="pi pi-plus mr-2"></i>
                            <div>
                                <div class="font-medium">Add Item</div>
                                <div class="text-sm opacity-90">Create new</div>
                            </div>
                        </button>
                    </div>

                    <!-- Pending Purchases List -->
                    <div v-if="showPendingList" class="border-t pt-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">
                                Pending Purchase Approvals
                            </h3>
                            <button
                                @click="fetchPendingPurchases"
                                class="text-blue-500 hover:text-blue-700"
                            >
                                <i class="pi pi-refresh mr-1"></i>Refresh
                            </button>
                        </div>

                        <div v-if="loadingPending" class="text-center py-4">
                            <i class="pi pi-spin pi-spinner text-blue-500"></i>
                            <p class="text-gray-600 mt-2">
                                Loading pending orders...
                            </p>
                        </div>

                        <div
                            v-else-if="pendingPurchases.length === 0"
                            class="text-center py-8"
                        >
                            <i
                                class="pi pi-check-circle text-green-400 text-3xl mb-2"
                            ></i>
                            <p class="text-gray-600">
                                No pending orders to review
                            </p>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="purchase in pendingPurchases"
                                :key="purchase.id"
                                class="border border-yellow-200 bg-yellow-50 rounded-lg p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <div
                                                class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-200"
                                            >
                                                <img
                                                    v-if="
                                                        purchase.user
                                                            .profile_image
                                                    "
                                                    :src="`/storage/profiles/${purchase.user.profile_image}`"
                                                    :alt="purchase.user.name"
                                                    class="w-full h-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                                                >
                                                    <i
                                                        class="pi pi-user text-white text-sm"
                                                    ></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <h4
                                                class="font-semibold text-gray-800"
                                            >
                                                {{ purchase.user.name }}
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                wants to buy
                                                {{ purchase.quantity }}x
                                                {{ purchase.shop_item.name }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{
                                                    formatDate(
                                                        purchase.created_at
                                                    )
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="text-right mr-4">
                                            <div
                                                class="text-lg font-bold text-yellow-600"
                                            >
                                                {{
                                                    formatPoints(
                                                        purchase.price_paid *
                                                            purchase.quantity
                                                    )
                                                }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                points paid
                                            </div>
                                        </div>

                                        <div class="flex gap-2">
                                            <button
                                                @click="
                                                    approvePurchase(purchase.id)
                                                "
                                                :disabled="
                                                    processingPurchase ===
                                                    purchase.id
                                                "
                                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg transition-colors text-sm"
                                            >
                                                <i
                                                    v-if="
                                                        processingPurchase ===
                                                        purchase.id
                                                    "
                                                    class="pi pi-spin pi-spinner mr-1"
                                                ></i>
                                                <i
                                                    v-else
                                                    class="pi pi-check mr-1"
                                                ></i>
                                                Approve
                                            </button>
                                            <button
                                                @click="
                                                    showRejectDialog(purchase)
                                                "
                                                :disabled="
                                                    processingPurchase ===
                                                    purchase.id
                                                "
                                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-lg transition-colors text-sm"
                                            >
                                                <i class="pi pi-times mr-1"></i>
                                                Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Confirmation Dialog -->
        <div
            v-if="showPurchaseDialog"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                <div class="text-center">
                    <div
                        class="w-16 h-16 mx-auto mb-4 rounded-full bg-purple-100 flex items-center justify-center"
                    >
                        <i
                            class="pi pi-shopping-cart text-purple-500 text-2xl"
                        ></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">
                        Confirm Purchase
                    </h3>
                    <p class="text-gray-600 mb-4">
                        Are you sure you want to buy
                        <strong>{{ selectedItem?.name }}</strong
                        >?
                    </p>

                    <!-- Item preview -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-800">
                                    {{ selectedItem?.name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-purple-600">
                                    {{ formatPoints(selectedItem?.price) }}
                                </p>
                                <p class="text-sm text-gray-500">points</p>
                            </div>
                        </div>
                    </div>

                    <!-- Balance info -->
                    <div class="text-sm text-gray-600 mb-6">
                        <div class="flex justify-between">
                            <span>Current balance:</span>
                            <span
                                >{{
                                    formatPoints(currentUser.points)
                                }}
                                points</span
                            >
                        </div>
                        <div class="flex justify-between">
                            <span>After purchase:</span>
                            <span
                                >{{
                                    formatPoints(
                                        currentUser.points -
                                            (selectedItem?.price || 0)
                                    )
                                }}
                                points</span
                            >
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button
                            @click="cancelPurchase"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="confirmPurchase"
                            :disabled="processingPurchase"
                            class="flex-1 bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50"
                        >
                            <i
                                v-if="processingPurchase"
                                class="pi pi-spin pi-spinner mr-2"
                            ></i>
                            Confirm Purchase
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Purchase Dialog -->
        <div
            v-if="showRejectDialogModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                <div class="text-center mb-4">
                    <div
                        class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center"
                    >
                        <i class="pi pi-times text-red-500 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">
                        Reject Purchase
                    </h3>
                    <p class="text-gray-600">
                        Reject purchase from
                        <strong>{{ rejectingPurchase?.user.name }}</strong
                        >?
                    </p>
                </div>

                <!-- Purchase details -->
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Item:</span>
                            <span class="font-medium">{{
                                rejectingPurchase?.shop_item.name
                            }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600">Quantity:</span>
                            <span class="font-medium">{{
                                rejectingPurchase?.quantity
                            }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total paid:</span>
                            <span class="font-medium text-red-600"
                                >{{
                                    formatPoints(
                                        rejectingPurchase?.price_paid *
                                            rejectingPurchase?.quantity
                                    )
                                }}
                                points</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Rejection reason -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for rejection (optional)
                    </label>
                    <textarea
                        v-model="rejectReason"
                        rows="3"
                        placeholder="Enter reason for rejection..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                    ></textarea>
                </div>

                <div class="flex gap-3">
                    <button
                        @click="cancelReject"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="rejectPurchase"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors"
                    >
                        Reject & Refund
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
export default {
    data() {
        return {
            loading: true,
            items: [],
            currentUser: {},
            showPurchaseHistory: false,

            // Purchase dialog
            showPurchaseDialog: false,
            selectedItem: null,
            processingPurchase: false,
            purchasingItem: null,

            // Admin features
            pendingPurchases: [],
            showPendingList: false,
            loadingPending: false,

            // Reject dialog
            showRejectDialogModal: false,
            rejectingPurchase: null,
            rejectReason: "",
        };
    },
    computed: {
        isAdmin() {
            return this.currentUser.role === "admin";
        },
    },
    methods: {
        async fetchItems() {
            try {
                this.loading = true;
                const response = await axios.get("/api/shop");

                if (response.data.success) {
                    this.items = response.data.items || [];
                    this.currentUser = response.data.user || {};
                }
            } catch (error) {
                console.error("Error fetching shop items:", error);
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load shop items",
                });
            } finally {
                this.loading = false;
            }
        },

        async fetchPendingPurchases() {
            if (!this.isAdmin) return;

            try {
                this.loadingPending = true;
                const response = await axios.get(
                    "/api/admin/shop/purchases/pending"
                );

                if (response.data.success) {
                    this.pendingPurchases = response.data.purchases || [];
                }
            } catch (error) {
                console.error("Error fetching pending purchases:", error);
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load pending purchases",
                });
            } finally {
                this.loadingPending = false;
            }
        },

        showPendingPurchases() {
            this.showPendingList = !this.showPendingList;
            if (this.showPendingList) {
                this.fetchPendingPurchases();
            }
        },

        buyItem(item) {
            this.selectedItem = item;
            this.showPurchaseDialog = true;
        },

        async confirmPurchase() {
            if (!this.selectedItem) return;

            try {
                this.processingPurchase = true;
                const response = await axios.post(
                    `/api/shop/${this.selectedItem.id}/purchase`
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    // Update user balance
                    this.currentUser.points = response.data.new_balance;

                    // No need to refresh items since stock isn't decremented until approval
                    this.cancelPurchase();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: error.response?.data?.message || "Purchase failed",
                });
            } finally {
                this.processingPurchase = false;
            }
        },

        cancelPurchase() {
            this.showPurchaseDialog = false;
            this.selectedItem = null;
            this.processingPurchase = false;
        },

        async approvePurchase(purchaseId) {
            try {
                this.processingPurchase = purchaseId;
                const response = await axios.post(
                    `/api/admin/shop/purchases/${purchaseId}/approve`
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    // Refresh pending purchases and items
                    await this.fetchPendingPurchases();
                    await this.fetchItems();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to approve purchase",
                });
            } finally {
                this.processingPurchase = null;
            }
        },

        showRejectDialog(purchase) {
            this.rejectingPurchase = purchase;
            this.showRejectDialogModal = true;
        },

        async rejectPurchase() {
            if (!this.rejectingPurchase) return;

            try {
                const response = await axios.post(
                    `/api/admin/shop/purchases/${this.rejectingPurchase.id}/reject`,
                    {
                        reason: this.rejectReason,
                    }
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    // Refresh pending purchases
                    await this.fetchPendingPurchases();
                    this.cancelReject();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to reject purchase",
                });
            }
        },

        cancelReject() {
            this.showRejectDialogModal = false;
            this.rejectingPurchase = null;
            this.rejectReason = "";
        },

        canBuyItem(item) {
            return (
                item.is_active &&
                (item.stock === null || item.stock > 0) &&
                this.currentUser.points >= item.price
            );
        },

        getButtonText(item) {
            if (!item.is_active) return "Unavailable";
            if (item.stock !== null && item.stock <= 0) return "Out of Stock";
            if (this.currentUser.points < item.price)
                return "Not Enough Points";
            return "Buy Now";
        },

        getCategoryIcon(category) {
            const icons = {
                cosmetic: "pi pi-palette",
                boost: "pi pi-bolt",
                premium: "pi pi-star",
                special: "pi pi-gift",
            };
            return icons[category] || "pi pi-box";
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
        // Get current user info
        try {
            const userResponse = await axios.get("/api/user");
            if (userResponse.data.success) {
                this.currentUser = userResponse.data.user;
            }
        } catch (error) {
            console.error("Error fetching current user:", error);
        }

        await this.fetchItems();

        // Auto-refresh pending purchases for admins
        if (this.isAdmin) {
            await this.fetchPendingPurchases();
        }
    },
};
</script>
