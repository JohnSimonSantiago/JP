<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold">Recent Orders</h3>
            <button
                @click="openNewOrder"
                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm rounded-lg transition-colors flex items-center gap-2"
            >
                <i class="pi pi-plus"></i>
                New Order
            </button>
        </div>

        <!-- Pending Orders -->
        <div v-if="pendingOrders.length > 0" class="mb-8">
            <h4 class="text-md font-medium text-orange-600 mb-4">
                Pending Orders
            </h4>
            <div class="space-y-3">
                <div
                    v-for="order in pendingOrders"
                    :key="order.id"
                    class="p-4 bg-orange-50 border border-orange-200 rounded-lg"
                >
                    <div
                        class="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                    >
                        <div class="flex items-start space-x-4 flex-1">
                            <!-- User Profile Image -->
                            <div
                                class="w-12 h-12 rounded-full overflow-hidden border-2 border-orange-200 flex-shrink-0"
                            >
                                <img
                                    v-if="order.user?.profile_image"
                                    :src="`/storage/profiles/${order.user.profile_image}`"
                                    :alt="order.user?.name"
                                    class="w-full h-full object-cover"
                                    @error="handleImageError($event)"
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

                            <!-- Item Image -->
                            <div
                                class="w-16 h-16 rounded-lg overflow-hidden border border-gray-200 flex-shrink-0"
                            >
                                <img
                                    v-if="order.shop_item?.image"
                                    :src="`/storage/${order.shop_item.image}`"
                                    :alt="order.shop_item?.name"
                                    class="w-full h-full object-cover"
                                    @error="handleImageError($event)"
                                />
                                <div
                                    v-else
                                    class="w-full h-full bg-gray-200 flex items-center justify-center"
                                >
                                    <i class="pi pi-image text-gray-400"></i>
                                </div>
                            </div>

                            <!-- Order Details -->
                            <div class="flex-1">
                                <div
                                    class="flex items-start justify-between mb-2"
                                >
                                    <div class="flex-1">
                                        <p class="font-medium text-lg">
                                            {{ order.shop_item?.name }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Customer:
                                            {{
                                                order.customer_type ===
                                                "walk_in"
                                                    ? order.walk_in_name ||
                                                      "Walk-in"
                                                    : order.user?.name
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{
                                                new Date(
                                                    order.created_at,
                                                ).toLocaleDateString()
                                            }}
                                        </p>
                                    </div>

                                    <!-- Quantity Box -->
                                    <div
                                        class="flex items-center justify-center ml-4"
                                    >
                                        <div
                                            class="bg-white px-3 py-2 rounded border text-center min-w-[60px] flex flex-col items-center justify-center"
                                        >
                                            <p
                                                class="text-xs text-gray-600 mb-1"
                                            >
                                                Qty
                                            </p>
                                            <p class="font-semibold text-sm">
                                                {{ order.quantity }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Price Information -->
                                <div class="flex items-center space-x-4 mt-3">
                                    <div
                                        class="flex items-center space-x-2 text-sm"
                                    >
                                        <span class="text-gray-600"
                                            >Customer Paid:</span
                                        >
                                        <span
                                            class="font-medium text-green-600"
                                        >
                                            ₱{{
                                                formatCash(
                                                    order.price_paid *
                                                        order.quantity,
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div
                                        v-if="order.shop_claim_amount > 0"
                                        class="flex items-center space-x-2 text-sm"
                                    >
                                        <span class="text-gray-600"
                                            >You earn:</span
                                        >
                                        <span
                                            class="font-semibold text-purple-700 text-base"
                                        >
                                            ₱{{
                                                formatCash(
                                                    order.shop_claim_amount *
                                                        order.quantity,
                                                )
                                            }}
                                            in payouts
                                        </span>
                                    </div>
                                </div>

                                <!-- Discount Breakdown (app orders only) -->
                                <div
                                    v-if="order.shop_claim_amount"
                                    class="mt-2 text-xs text-gray-500 space-y-0.5"
                                >
                                    <div class="flex items-center gap-2">
                                        <span>List price:</span>
                                        <span class="line-through">
                                            ₱{{
                                                formatCash(
                                                    order.list_price *
                                                        order.quantity,
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span>Store discount:</span>
                                        <span class="text-red-500">
                                            −₱{{
                                                formatCash(
                                                    order.store_discount_amount *
                                                        order.quantity,
                                                )
                                            }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span>Level Lounge discount:</span>
                                        <span class="text-red-500">
                                            −₱{{
                                                formatCash(
                                                    order.admin_discount_amount *
                                                        order.quantity,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div
                            class="flex gap-2 sm:ml-6 w-full sm:w-auto [&>button]:flex-1 sm:[&>button]:flex-none"
                        >
                            <button
                                @click="approveOrder(order)"
                                class="px-3 py-2 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition-colors flex items-center"
                            >
                                <i class="pi pi-check mr-1"></i>
                                Approve
                            </button>
                            <button
                                @click="rejectOrder(order)"
                                class="px-3 py-2 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition-colors flex items-center"
                            >
                                <i class="pi pi-times mr-1"></i>
                                Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order History: every order (completed, rejected, pending), 20 at a time -->
        <div v-if="historyCounts.all > 0">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <h4 class="text-md font-medium">Order History</h4>
                <div class="flex flex-wrap gap-1 bg-gray-100 p-1 rounded-lg text-sm">
                    <button
                        v-for="f in historyFilters"
                        :key="f.key"
                        @click="setHistoryStatus(f.key)"
                        class="px-3 py-1 rounded-md font-medium transition-all"
                        :class="historyStatus === f.key ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500'"
                    >
                        {{ f.label }} ({{ historyCounts[f.key] }})
                    </button>
                </div>
            </div>

            <p v-if="historyError" class="text-sm text-red-500 mb-3">{{ historyError }}</p>
            <p
                v-else-if="!loadingHistory && historyOrders.length === 0"
                class="text-sm text-gray-400 text-center py-6"
            >
                No {{ historyStatus === "completed" ? "completed" : historyStatus }} orders.
            </p>

            <div class="space-y-3">
                <div
                    v-for="order in historyOrders"
                    :key="order.id"
                    class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                >
                    <div class="flex items-center space-x-4 flex-1">
                        <!-- Customer Image -->
                        <div
                            class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-200 flex-shrink-0"
                        >
                            <img
                                v-if="order.user?.profile_image"
                                :src="`/storage/profiles/${order.user.profile_image}`"
                                :alt="order.user?.name"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                            >
                                <i class="pi pi-user text-white text-lg"></i>
                            </div>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium">
                                        {{ order.shop_item?.name }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Customer:
                                        {{
                                            order.customer_type === "walk_in"
                                                ? order.walk_in_name ||
                                                  "Walk-in"
                                                : order.user?.name
                                        }}
                                        • Qty: {{ order.quantity }}
                                        •
                                        {{
                                            order.customer_type === "walk_in" && order.payment_method === "cash"
                                                ? "Counter, cash"
                                                : order.customer_type === "walk_in"
                                                  ? "Counter, Coins"
                                                  : "App, Coins"
                                        }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{
                                            new Date(order.created_at).toLocaleString("en-PH", {
                                                timeZone: "Asia/Manila",
                                                month: "short",
                                                day: "numeric",
                                                year: "numeric",
                                                hour: "numeric",
                                                minute: "2-digit",
                                            })
                                        }}
                                        • Total: ₱{{
                                            formatCash(
                                                order.price_paid *
                                                    order.quantity,
                                            )
                                        }}
                                        <span
                                            v-if="order.shop_claim_amount > 0 && order.status !== 'rejected'"
                                            class="text-purple-700 font-medium"
                                        >
                                            • You earn ₱{{
                                                formatCash(
                                                    order.shop_claim_amount *
                                                        order.quantity,
                                                )
                                            }}
                                            in payouts
                                        </span>
                                    </p>
                                    <p
                                        v-if="order.status === 'rejected' && order.rejection_reason"
                                        class="text-xs text-red-500 mt-0.5"
                                    >
                                        Reason: {{ order.rejection_reason }}
                                    </p>
                                </div>
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-sm ml-4 capitalize',
                                        order.status === 'completed'
                                            ? 'bg-green-100 text-green-800'
                                            : order.status === 'rejected'
                                              ? 'bg-red-100 text-red-800'
                                              : 'bg-yellow-100 text-yellow-800',
                                    ]"
                                >
                                    {{ order.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="loadingHistory" class="text-center py-4 text-gray-400">
                <i class="pi pi-spin pi-spinner"></i>
            </div>
            <button
                v-else-if="historyPage < historyLastPage"
                @click="fetchHistory(false)"
                class="mt-4 w-full border border-gray-200 text-gray-600 hover:bg-gray-50 py-2 rounded-lg text-sm font-medium"
            >
                Load more
            </button>
        </div>

        <!-- Empty Orders State -->
        <div
            v-if="!loadingHistory && historyCounts.all === 0 && pendingOrders.length === 0"
            class="text-center py-12"
        >
            <i class="pi pi-shopping-cart text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-600 mb-2">
                No Orders Yet
            </h3>
            <p class="text-gray-500">
                Orders will appear here once customers start purchasing
            </p>
        </div>

        <!-- New Order Modal -->
        <div
            v-if="showNewOrder"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div
                class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] flex flex-col"
            >
                <!-- Modal header -->
                <div
                    class="flex justify-between items-center p-6 border-b border-gray-200"
                >
                    <h3 class="text-xl font-bold text-gray-800">New Order</h3>
                    <button
                        @click="showNewOrder = false"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        <i class="pi pi-times"></i>
                    </button>
                </div>

                <!-- Modal body (scrollable) -->
                <div class="p-6 overflow-y-auto">
                    <!-- Items -->
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                        >Items</label
                    >
                    <div class="space-y-2 mb-4">
                        <div
                            v-for="item in shopItems"
                            :key="item.id"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex-1">
                                <p class="font-medium text-gray-800">
                                    {{ item.name }}
                                </p>
                                <p class="text-sm text-purple-600">
                                    ₱{{ formatCash(item.cash_price) }}
                                </p>
                            </div>
                            <div
                                v-if="item.stock !== null && item.stock <= 0"
                                class="text-sm text-gray-400"
                            >
                                Out of stock
                            </div>
                            <div v-else class="flex items-center gap-3">
                                <button
                                    @click="removeItem(item.id)"
                                    :disabled="!getQty(item.id)"
                                    class="text-red-500 disabled:text-gray-300"
                                >
                                    <i class="pi pi-minus-circle text-xl"></i>
                                </button>
                                <span class="w-6 text-center font-semibold">{{
                                    getQty(item.id)
                                }}</span>
                                <button
                                    @click="addItem(item)"
                                    :disabled="
                                        item.stock !== null &&
                                        getQty(item.id) >= item.stock
                                    "
                                    class="text-purple-600 disabled:text-gray-300"
                                >
                                    <i class="pi pi-plus-circle text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Total -->
                    <div
                        v-if="orderItems.length > 0"
                        class="flex justify-between items-center py-3 border-t border-gray-200 mb-4"
                    >
                        <span class="font-medium text-gray-600">Total</span>
                        <span class="font-bold text-lg text-gray-900"
                            >₱{{ formatCash(orderTotal) }}</span
                        >
                    </div>

                    <!-- Customer type -->
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                        >Customer</label
                    >
                    <div class="flex gap-2 mb-4">
                        <button
                            @click="customerType = 'walk_in'"
                            :class="[
                                'flex-1 py-2 rounded-lg text-sm font-medium transition-colors',
                                customerType === 'walk_in'
                                    ? 'bg-purple-600 text-white'
                                    : 'bg-gray-100 text-gray-600',
                            ]"
                        >
                            Walk-in
                        </button>
                        <button
                            @click="customerType = 'user'"
                            :class="[
                                'flex-1 py-2 rounded-lg text-sm font-medium transition-colors',
                                customerType === 'user'
                                    ? 'bg-purple-600 text-white'
                                    : 'bg-gray-100 text-gray-600',
                            ]"
                        >
                            Member
                        </button>
                    </div>

                    <!-- Walk-in name -->
                    <div v-if="customerType === 'walk_in'" class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Customer Name (optional)</label
                        >
                        <input
                            v-model="walkInName"
                            type="text"
                            placeholder="e.g. Juan, Table 3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500"
                        />
                    </div>

                    <!-- Member search -->
                    <div v-else class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Search Member</label
                        >
                        <input
                            :value="
                                selectedUser ? selectedUser.name : userSearch
                            "
                            @input="searchUsers($event.target.value)"
                            type="text"
                            placeholder="Type name..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-purple-500"
                        />
                        <div v-if="userResults.length" class="mt-1 space-y-1">
                            <button
                                v-for="u in userResults"
                                :key="u.id"
                                @click="pickUser(u)"
                                class="w-full text-left px-3 py-2 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm flex items-center gap-2"
                            >
                                <i class="pi pi-user text-purple-500"></i>
                                {{ u.name }}
                            </button>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400 mb-4">
                        <i class="pi pi-info-circle mr-1"></i>
                        Counter orders are paid in cash and are recorded as
                        completed. App discounts do not apply.
                    </p>
                </div>

                <!-- Modal footer -->
                <div class="p-6 border-t border-gray-200">
                    <button
                        @click="submitOrder"
                        :disabled="submitting || orderItems.length === 0"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white py-3 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <i
                            v-if="submitting"
                            class="pi pi-spin pi-spinner mr-2"
                        ></i>
                        Confirm Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "OrdersTab",
    props: {
        shop: { type: Object, required: true },
        pendingOrders: { type: Array, default: () => [] },
        recentOrders: { type: Array, default: () => [] },
    },
    emits: ["orders-changed"],
    data() {
        return {
            // Order History (all orders, filtered by status, 20 per page)
            historyOrders: [],
            historyStatus: "all",
            historyPage: 1,
            historyLastPage: 1,
            historyCounts: { all: 0, completed: 0, rejected: 0, pending: 0 },
            historyFilters: [
                { key: "all", label: "All" },
                { key: "completed", label: "Completed" },
                { key: "rejected", label: "Rejected" },
                { key: "pending", label: "Pending" },
            ],
            loadingHistory: false,
            historyError: null,

            showNewOrder: false,
            shopItems: [],
            orderItems: [],
            customerType: "walk_in",
            walkInName: "",
            userSearch: "",
            userResults: [],
            selectedUser: null,
            submitting: false,
        };
    },
    computed: {
        orderTotal() {
            return this.orderItems.reduce(
                (sum, i) => sum + i.cash_price * i.quantity,
                0,
            );
        },
    },
    watch: {
        // The page reloads its order lists after approve / reject / a new counter order
        recentOrders() {
            this.fetchHistory(true);
        },
    },

    mounted() {
        this.fetchHistory(true);
    },

    methods: {
        setHistoryStatus(status) {
            if (this.historyStatus === status) return;
            this.historyStatus = status;
            this.fetchHistory(true);
        },

        // reset = start again at page 1; otherwise add the next page
        async fetchHistory(reset = true) {
            const requestNo = (this._historyRequestNo = (this._historyRequestNo || 0) + 1);
            const page = reset ? 1 : this.historyPage + 1;
            this.loadingHistory = true;
            this.historyError = null;
            try {
                const res = await axios.get(`/api/shops/${this.shop.id}/items/purchases`, {
                    params: { status: this.historyStatus, page },
                });
                if (requestNo !== this._historyRequestNo) return; // a newer request won
                const incoming = res.data.orders || [];
                if (reset) {
                    this.historyOrders = incoming;
                } else {
                    const known = new Set(this.historyOrders.map((o) => o.id));
                    this.historyOrders = this.historyOrders.concat(incoming.filter((o) => !known.has(o.id)));
                }
                this.historyPage = res.data.pagination.current_page;
                this.historyLastPage = res.data.pagination.last_page;
                this.historyCounts = res.data.counts;
            } catch (e) {
                if (requestNo !== this._historyRequestNo) return;
                this.historyError = e.response?.data?.message || "Couldn't load the order history.";
            } finally {
                if (requestNo === this._historyRequestNo) this.loadingHistory = false;
            }
        },

        async approveOrder(order) {
            try {
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/items/purchases/${order.id}/approve`,
                );
                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Order approved successfully",
                    });
                    this.$emit("orders-changed");
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to approve order",
                });
            }
        },

        async rejectOrder(order) {
            const reason = prompt("Reason for rejection (optional):");
            try {
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/items/purchases/${order.id}/reject`,
                    { reason: reason },
                );
                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Order rejected successfully",
                    });
                    this.$emit("orders-changed");
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to reject order",
                });
            }
        },

        // ─── New Order ───────────────────────────────────────────
        async openNewOrder() {
            this.orderItems = [];
            this.customerType = "walk_in";
            this.walkInName = "";
            this.userSearch = "";
            this.userResults = [];
            this.selectedUser = null;
            this.showNewOrder = true;
            await this.loadShopItems();
        },

        async loadShopItems() {
            try {
                const res = await axios.get(`/api/shops/${this.shop.id}/items`);
                if (res.data.success) this.shopItems = res.data.items || [];
            } catch (e) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load items",
                });
            }
        },

        getQty(itemId) {
            return (
                this.orderItems.find((i) => i.shop_item_id === itemId)
                    ?.quantity ?? 0
            );
        },

        addItem(item) {
            const existing = this.orderItems.find(
                (i) => i.shop_item_id === item.id,
            );
            const current = existing ? existing.quantity : 0;

            if (item.stock !== null && current >= item.stock) {
                this.$toast?.add({
                    severity: "warn",
                    summary: "Stock limit",
                    detail: `Only ${item.stock} left in stock`,
                });
                return;
            }

            if (existing) {
                existing.quantity++;
            } else {
                this.orderItems.push({
                    shop_item_id: item.id,
                    name: item.name,
                    cash_price: item.cash_price,
                    quantity: 1,
                });
            }
        },

        removeItem(itemId) {
            const existing = this.orderItems.find(
                (i) => i.shop_item_id === itemId,
            );
            if (!existing) return;
            if (existing.quantity > 1) {
                existing.quantity--;
            } else {
                this.orderItems = this.orderItems.filter(
                    (i) => i.shop_item_id !== itemId,
                );
            }
        },

        async searchUsers(query) {
            this.userSearch = query;
            this.selectedUser = null;
            if (query.length < 2) {
                this.userResults = [];
                return;
            }
            try {
                // Search any logged-in user can use (the admin user list is admin-only,
                // so non-admin shop owners couldn't find members)
                const res = await axios.get("/api/users/search", {
                    params: { q: query },
                });
                this.userResults = res.data.users || [];
            } catch (e) {
                this.userResults = [];
            }
        },

        pickUser(u) {
            this.selectedUser = u;
            this.userResults = [];
            this.userSearch = "";
        },

        async submitOrder() {
            if (this.orderItems.length === 0) {
                this.$toast?.add({
                    severity: "warn",
                    summary: "Wait",
                    detail: "Add at least one item",
                });
                return;
            }
            if (this.customerType === "user" && !this.selectedUser) {
                this.$toast?.add({
                    severity: "warn",
                    summary: "Wait",
                    detail: "Select a member",
                });
                return;
            }

            this.submitting = true;
            try {
                await axios.post(
                    `/api/shops/${this.shop.id}/items/walk-in-order`,
                    {
                        items: this.orderItems.map((i) => ({
                            shop_item_id: i.shop_item_id,
                            quantity: i.quantity,
                        })),
                        customer_type: this.customerType,
                        walk_in_name:
                            this.customerType === "walk_in"
                                ? this.walkInName || "Walk-in"
                                : undefined,
                        user_id:
                            this.customerType === "user"
                                ? this.selectedUser?.id
                                : undefined,
                        payment_method: "cash",
                    },
                );
                this.showNewOrder = false;
                this.$toast?.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Order recorded!",
                });
                this.$emit("orders-changed");
            } catch (e) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Failed",
                    detail: e.response?.data?.message || "Something went wrong",
                });
            } finally {
                this.submitting = false;
            }
        },

        handleImageError(event) {
            event.target.style.display = "none";
        },

        formatCash(amount) {
            return parseFloat(amount || 0).toFixed(2);
        },
    },
};
</script>
