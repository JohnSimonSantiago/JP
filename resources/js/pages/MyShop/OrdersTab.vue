<template>
    <div>
        <h3 class="text-lg font-semibold mb-6">Recent Orders</h3>

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
                                            Customer: {{ order.user?.name }}
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
                                            >Unit Price:</span
                                        >
                                        <span
                                            class="font-medium text-green-600"
                                        >
                                            ₱{{ formatCash(order.price_paid) }}
                                        </span>
                                    </div>
                                    <div
                                        class="flex items-center space-x-2 text-sm"
                                    >
                                        <span class="text-gray-600"
                                            >Total:</span
                                        >
                                        <span
                                            class="font-semibold text-green-600 text-base"
                                        >
                                            ₱{{
                                                formatCash(
                                                    order.price_paid *
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

        <!-- Recent Orders -->
        <div v-if="recentOrders.length > 0">
            <h4 class="text-md font-medium mb-4">Recent Orders</h4>
            <div class="space-y-3">
                <div
                    v-for="order in recentOrders"
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
                                        Customer: {{ order.user?.name }} • Qty:
                                        {{ order.quantity }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{
                                            new Date(
                                                order.created_at,
                                            ).toLocaleDateString()
                                        }}
                                        • Total: ₱{{
                                            formatCash(
                                                order.price_paid *
                                                    order.quantity,
                                            )
                                        }}
                                    </p>
                                </div>
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-sm ml-4',
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
        </div>

        <!-- Empty Orders State -->
        <div
            v-if="recentOrders.length === 0 && pendingOrders.length === 0"
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
    // Tells the parent to re-fetch all shop data after approve/reject
    emits: ["orders-changed"],
    methods: {
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
                    {
                        reason: reason,
                    },
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

        handleImageError(event) {
            event.target.style.display = "none";
        },

        formatCash(amount) {
            return parseFloat(amount || 0).toFixed(2);
        },
    },
};
</script>
