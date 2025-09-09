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
                    <!-- Shop Info & Stats -->
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
                    >
                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <i
                                        class="pi pi-box text-blue-600 text-xl"
                                    ></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-600">
                                        Total Items
                                    </p>
                                    <p class="text-2xl font-bold">
                                        {{ statistics?.total_items || 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <i
                                        class="pi pi-shopping-cart text-green-600 text-xl"
                                    ></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-600">
                                        Total Orders
                                    </p>
                                    <p class="text-2xl font-bold">
                                        {{ statistics?.total_orders || 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-yellow-100 rounded-lg">
                                    <i
                                        class="pi pi-clock text-yellow-600 text-xl"
                                    ></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-600">
                                        Pending Orders
                                    </p>
                                    <p class="text-2xl font-bold">
                                        {{ statistics?.pending_orders || 0 }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <i
                                        class="pi pi-dollar text-green-600 text-xl"
                                    ></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-600">Revenue</p>
                                    <p class="text-2xl font-bold">
                                        ₱{{
                                            formatCash(
                                                statistics?.total_revenue || 0
                                            )
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab Navigation -->
                    <div class="bg-white rounded-xl shadow-lg mb-8">
                        <div class="border-b border-gray-200">
                            <nav class="flex space-x-8 px-6">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.id"
                                    @click="activeTab = tab.id"
                                    :class="[
                                        'py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center gap-2',
                                        activeTab === tab.id
                                            ? 'border-purple-500 text-purple-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    ]"
                                >
                                    <i :class="tab.icon"></i>
                                    <span>{{ tab.name }}</span>
                                    <!-- Badge for counts -->
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
                            <!-- Items Tab -->
                            <div v-if="activeTab === 'items'">
                                <div
                                    class="flex justify-between items-center mb-6"
                                >
                                    <h3 class="text-lg font-semibold">
                                        Shop Items
                                    </h3>
                                    <button
                                        @click="showCreateItem = true"
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors"
                                    >
                                        <i class="pi pi-plus mr-2"></i>
                                        Add Item
                                    </button>
                                </div>

                                <!-- Items List -->
                                <div v-if="items.length > 0" class="space-y-4">
                                    <div
                                        v-for="item in items"
                                        :key="item.id"
                                        class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
                                    >
                                        <div
                                            class="flex items-center space-x-4"
                                        >
                                            <img
                                                v-if="item.image"
                                                :src="`/storage/${item.image}`"
                                                :alt="item.name"
                                                class="w-16 h-16 object-cover rounded-lg"
                                            />
                                            <div
                                                v-else
                                                class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center"
                                            >
                                                <i
                                                    class="pi pi-image text-gray-400"
                                                ></i>
                                            </div>
                                            <div>
                                                <h4 class="font-medium">
                                                    {{ item.name }}
                                                </h4>
                                                <p
                                                    class="text-sm text-gray-600"
                                                >
                                                    {{ item.description }}
                                                </p>
                                                <div
                                                    class="flex items-center space-x-4 mt-1"
                                                >
                                                    <!-- Show only cash price for shop owners -->
                                                    <div
                                                        class="flex items-center space-x-2"
                                                    >
                                                        <span
                                                            v-if="
                                                                item.cash_price >
                                                                0
                                                            "
                                                            class="text-sm font-medium text-green-600"
                                                        >
                                                            ₱{{
                                                                formatCash(
                                                                    item.cash_price
                                                                )
                                                            }}
                                                        </span>
                                                        <span
                                                            v-else
                                                            class="text-sm text-red-500"
                                                        >
                                                            No price set
                                                        </span>
                                                    </div>
                                                    <span
                                                        :class="[
                                                            'text-xs px-2 py-1 rounded-full',
                                                            item.is_active
                                                                ? 'bg-green-100 text-green-800'
                                                                : 'bg-red-100 text-red-800',
                                                        ]"
                                                    >
                                                        {{
                                                            item.is_active
                                                                ? "Active"
                                                                : "Inactive"
                                                        }}
                                                    </span>
                                                    <span
                                                        class="text-xs text-gray-500"
                                                    >
                                                        Stock:
                                                        <span
                                                            :class="
                                                                item.stock ===
                                                                null
                                                                    ? 'text-green-600'
                                                                    : ''
                                                            "
                                                        >
                                                            {{
                                                                item.stock ===
                                                                null
                                                                    ? "Unlimited"
                                                                    : item.stock
                                                            }}
                                                        </span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button
                                                @click="editItem(item)"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            >
                                                <i class="pi pi-pencil"></i>
                                            </button>
                                            <button
                                                @click="deleteItem(item)"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            >
                                                <i class="pi pi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty Items State -->
                                <div v-else class="text-center py-12">
                                    <i
                                        class="pi pi-box text-gray-300 text-5xl mb-4"
                                    ></i>
                                    <h3
                                        class="text-lg font-medium text-gray-600 mb-2"
                                    >
                                        No Items Yet
                                    </h3>
                                    <p class="text-gray-500 mb-4">
                                        Start by adding your first item to the
                                        shop
                                    </p>
                                    <button
                                        @click="showCreateItem = true"
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors"
                                    >
                                        <i class="pi pi-plus mr-2"></i>
                                        Add First Item
                                    </button>
                                </div>
                            </div>

                            <!-- Orders Tab -->
                            <div v-if="activeTab === 'orders'">
                                <h3 class="text-lg font-semibold mb-6">
                                    Recent Orders
                                </h3>

                                <!-- Pending Orders -->
                                <div
                                    v-if="pendingOrders.length > 0"
                                    class="mb-8"
                                >
                                    <h4
                                        class="text-md font-medium text-orange-600 mb-4"
                                    >
                                        Pending Orders
                                    </h4>
                                    <div class="space-y-3">
                                        <div
                                            v-for="order in pendingOrders"
                                            :key="order.id"
                                            class="p-4 bg-orange-50 border border-orange-200 rounded-lg"
                                        >
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <div class="flex-1">
                                                    <div
                                                        class="flex items-start justify-between mb-2"
                                                    >
                                                        <div class="flex-1">
                                                            <p
                                                                class="font-medium text-lg"
                                                            >
                                                                {{
                                                                    order
                                                                        .shop_item
                                                                        ?.name
                                                                }}
                                                            </p>
                                                            <p
                                                                class="text-sm text-gray-600"
                                                            >
                                                                Customer:
                                                                {{
                                                                    order.user
                                                                        ?.name
                                                                }}
                                                            </p>
                                                            <p
                                                                class="text-sm text-gray-500"
                                                            >
                                                                {{
                                                                    new Date(
                                                                        order.created_at
                                                                    ).toLocaleDateString()
                                                                }}
                                                            </p>
                                                        </div>

                                                        <!-- Order Details -->
                                                        <div
                                                            class="text-right ml-4"
                                                        >
                                                            <div
                                                                class="bg-white px-2 py-1 rounded border text-center min-w-[60px]"
                                                            >
                                                                <p
                                                                    class="text-xs text-gray-600"
                                                                >
                                                                    Qty
                                                                </p>
                                                                <p
                                                                    class="font-semibold text-sm"
                                                                >
                                                                    {{
                                                                        order.quantity
                                                                    }}
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Price Information -->
                                                    <div
                                                        class="flex items-center space-x-4 mt-3"
                                                    >
                                                        <div
                                                            class="flex items-center space-x-2 text-sm"
                                                        >
                                                            <span
                                                                class="text-gray-600"
                                                                >Unit
                                                                Price:</span
                                                            >
                                                            <span
                                                                class="font-medium text-green-600"
                                                            >
                                                                ₱{{
                                                                    formatCash(
                                                                        order.price_paid
                                                                    )
                                                                }}
                                                            </span>
                                                        </div>
                                                        <div
                                                            class="flex items-center space-x-2 text-sm"
                                                        >
                                                            <span
                                                                class="text-gray-600"
                                                                >Total:</span
                                                            >
                                                            <span
                                                                class="font-semibold text-green-600 text-base"
                                                            >
                                                                ₱{{
                                                                    formatCash(
                                                                        order.price_paid *
                                                                            order.quantity
                                                                    )
                                                                }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Action Buttons -->
                                                <div
                                                    class="flex flex-col space-y-2 ml-6"
                                                >
                                                    <button
                                                        @click="
                                                            approveOrder(order)
                                                        "
                                                        class="px-4 py-2 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition-colors flex items-center"
                                                    >
                                                        <i
                                                            class="pi pi-check mr-1"
                                                        ></i>
                                                        Approve
                                                    </button>
                                                    <button
                                                        @click="
                                                            rejectOrder(order)
                                                        "
                                                        class="px-4 py-2 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition-colors flex items-center"
                                                    >
                                                        <i
                                                            class="pi pi-times mr-1"
                                                        ></i>
                                                        Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recent Orders -->
                                <div v-if="recentOrders.length > 0">
                                    <h4 class="text-md font-medium mb-4">
                                        Recent Orders
                                    </h4>
                                    <div class="space-y-3">
                                        <div
                                            v-for="order in recentOrders"
                                            :key="order.id"
                                            class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                                        >
                                            <div class="flex-1">
                                                <div
                                                    class="flex items-center justify-between"
                                                >
                                                    <div>
                                                        <p class="font-medium">
                                                            {{
                                                                order.shop_item
                                                                    ?.name
                                                            }}
                                                        </p>
                                                        <p
                                                            class="text-sm text-gray-600"
                                                        >
                                                            Customer:
                                                            {{
                                                                order.user?.name
                                                            }}
                                                            • Qty:
                                                            {{ order.quantity }}
                                                        </p>
                                                        <p
                                                            class="text-sm text-gray-500"
                                                        >
                                                            {{
                                                                new Date(
                                                                    order.created_at
                                                                ).toLocaleDateString()
                                                            }}
                                                            • Total: ₱{{
                                                                formatCash(
                                                                    order.price_paid *
                                                                        order.quantity
                                                                )
                                                            }}
                                                        </p>
                                                    </div>
                                                    <span
                                                        :class="[
                                                            'px-3 py-1 rounded-full text-sm ml-4',
                                                            order.status ===
                                                            'completed'
                                                                ? 'bg-green-100 text-green-800'
                                                                : order.status ===
                                                                  'rejected'
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

                                <!-- Empty Orders State -->
                                <div
                                    v-if="
                                        recentOrders.length === 0 &&
                                        pendingOrders.length === 0
                                    "
                                    class="text-center py-12"
                                >
                                    <i
                                        class="pi pi-shopping-cart text-gray-300 text-5xl mb-4"
                                    ></i>
                                    <h3
                                        class="text-lg font-medium text-gray-600 mb-2"
                                    >
                                        No Orders Yet
                                    </h3>
                                    <p class="text-gray-500">
                                        Orders will appear here once customers
                                        start purchasing
                                    </p>
                                </div>
                            </div>

                            <!-- Loyalty Card Tab -->
                            <div v-if="activeTab === 'loyalty'">
                                <div class="space-y-6">
                                    <!-- Loyalty Card Header -->
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <div>
                                            <h3
                                                class="text-xl font-semibold text-gray-900"
                                            >
                                                Loyalty Card System
                                            </h3>
                                            <p class="text-gray-600">
                                                Manage customer loyalty and
                                                rewards
                                            </p>
                                        </div>
                                        <div class="flex gap-3">
                                            <button
                                                v-if="hasLoyaltyCard"
                                                @click="openLoyaltyCardForm"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                                            >
                                                <i class="pi pi-cog mr-2"></i>
                                                Settings
                                            </button>

                                            <button
                                                v-if="hasLoyaltyCard"
                                                @click="toggleLoyaltyCard"
                                                :class="
                                                    loyaltyCardActive
                                                        ? 'bg-red-500 hover:bg-red-600'
                                                        : 'bg-green-500 hover:bg-green-600'
                                                "
                                                class="text-white px-4 py-2 rounded-lg transition-colors"
                                            >
                                                <i
                                                    :class="
                                                        loyaltyCardActive
                                                            ? 'pi pi-pause'
                                                            : 'pi pi-play'
                                                    "
                                                    class="mr-2"
                                                ></i>
                                                {{
                                                    loyaltyCardActive
                                                        ? "Deactivate"
                                                        : "Activate"
                                                }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- No Loyalty Card State -->
                                    <div
                                        v-if="!hasLoyaltyCard"
                                        class="bg-white rounded-xl shadow-lg p-12 text-center"
                                    >
                                        <i
                                            class="pi pi-gift text-gray-300 text-5xl mb-4"
                                        ></i>
                                        <h3
                                            class="text-xl font-semibold text-gray-800 mb-2"
                                        >
                                            No Loyalty Card System
                                        </h3>
                                        <p class="text-gray-600 mb-6">
                                            Create a loyalty card to reward your
                                            customers and increase retention.
                                        </p>
                                        <button
                                            @click="openLoyaltyCardForm"
                                            class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg transition-colors"
                                        >
                                            <i class="pi pi-plus mr-2"></i>
                                            Create Loyalty Card
                                        </button>
                                    </div>

                                    <!-- Loyalty Card Dashboard -->
                                    <div v-else class="space-y-6">
                                        <!-- Loyalty Card Info -->
                                        <div
                                            class="bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-xl p-6"
                                        >
                                            <div
                                                class="flex justify-between items-start"
                                            >
                                                <div>
                                                    <h3
                                                        class="text-xl font-bold mb-2"
                                                    >
                                                        {{ loyaltyCard.name }}
                                                    </h3>
                                                    <p class="opacity-90 mb-4">
                                                        {{
                                                            loyaltyCard.description ||
                                                            "Reward your loyal customers"
                                                        }}
                                                    </p>
                                                    <div
                                                        class="flex items-center gap-4"
                                                    >
                                                        <span
                                                            class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm"
                                                        >
                                                            <i
                                                                class="pi pi-shopping-cart mr-1"
                                                            ></i>
                                                            {{
                                                                loyaltyCard.required_purchases
                                                            }}
                                                            purchases = 1 free
                                                            item
                                                        </span>
                                                        <span
                                                            :class="
                                                                loyaltyCardActive
                                                                    ? 'bg-green-500 bg-opacity-30'
                                                                    : 'bg-red-500 bg-opacity-30'
                                                            "
                                                            class="px-3 py-1 rounded-full text-sm"
                                                        >
                                                            {{
                                                                loyaltyCardActive
                                                                    ? "Active"
                                                                    : "Inactive"
                                                            }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <i
                                                    class="pi pi-gift text-4xl opacity-50"
                                                ></i>
                                            </div>
                                        </div>

                                        <!-- Customer Progress and Available Rewards -->
                                        <div
                                            class="bg-white rounded-xl shadow-lg"
                                        >
                                            <div
                                                class="p-6 border-b border-gray-200"
                                            >
                                                <h4
                                                    class="text-lg font-semibold text-gray-900"
                                                >
                                                    Customer Loyalty Progress
                                                </h4>
                                                <p class="text-gray-600">
                                                    Customers with completed
                                                    loyalty cards
                                                </p>
                                            </div>
                                            <div class="p-6">
                                                <div
                                                    v-if="
                                                        loyaltyProgress.length ===
                                                        0
                                                    "
                                                    class="text-center py-8"
                                                >
                                                    <i
                                                        class="pi pi-users text-gray-300 text-4xl mb-4"
                                                    ></i>
                                                    <p class="text-gray-500">
                                                        No customer loyalty
                                                        progress yet
                                                    </p>
                                                </div>
                                                <div v-else class="space-y-4">
                                                    <div
                                                        v-for="customer in loyaltyProgress.filter(
                                                            (c) =>
                                                                getCompletedCards(
                                                                    c
                                                                ) > 0
                                                        )"
                                                        :key="customer.user.id"
                                                        class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                                    >
                                                        <div
                                                            class="flex items-center justify-between"
                                                        >
                                                            <div
                                                                class="flex items-center gap-4"
                                                            >
                                                                <div
                                                                    class="w-12 h-12 bg-purple-500 text-white rounded-full flex items-center justify-center"
                                                                >
                                                                    <i
                                                                        class="pi pi-user"
                                                                    ></i>
                                                                </div>
                                                                <div>
                                                                    <h5
                                                                        class="font-medium text-gray-900"
                                                                    >
                                                                        {{
                                                                            customer
                                                                                .user
                                                                                .name
                                                                        }}
                                                                    </h5>
                                                                    <p
                                                                        class="text-sm text-gray-600"
                                                                    >
                                                                        Completed
                                                                        Cards:
                                                                        {{
                                                                            getCompletedCards(
                                                                                customer
                                                                            )
                                                                        }}
                                                                    </p>
                                                                    <p
                                                                        class="text-xs text-gray-500"
                                                                    >
                                                                        Current
                                                                        Progress:
                                                                        {{
                                                                            getCurrentCardPurchases(
                                                                                customer
                                                                            )
                                                                        }}/{{
                                                                            loyaltyCard.required_purchases
                                                                        }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="flex items-center gap-2"
                                                            >
                                                                <span
                                                                    class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium"
                                                                >
                                                                    {{
                                                                        getCompletedCards(
                                                                            customer
                                                                        )
                                                                    }}
                                                                    Available
                                                                </span>
                                                                <button
                                                                    @click="
                                                                        openCustomerLoyaltyModal(
                                                                            customer
                                                                        )
                                                                    "
                                                                    class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-sm transition-colors"
                                                                >
                                                                    <i
                                                                        class="pi pi-eye mr-1"
                                                                    ></i>
                                                                    View Details
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-white rounded-xl shadow-lg"
                                        >
                                            <div
                                                class="p-6 border-b border-gray-200"
                                            >
                                                <h4
                                                    class="text-lg font-semibold text-gray-900"
                                                >
                                                    Loyalty Rewards Management
                                                </h4>
                                                <p class="text-gray-600">
                                                    Manage customer reward
                                                    claims
                                                </p>
                                            </div>

                                            <div class="p-6">
                                                <!-- Pending Rewards -->
                                                <div
                                                    v-if="
                                                        pendingRewards.length >
                                                        0
                                                    "
                                                    class="mb-6"
                                                >
                                                    <h5
                                                        class="text-md font-semibold text-yellow-700 mb-3 flex items-center"
                                                    >
                                                        <i
                                                            class="pi pi-clock mr-2"
                                                        ></i>
                                                        Pending Approval ({{
                                                            pendingRewards.length
                                                        }})
                                                    </h5>
                                                    <div class="space-y-3">
                                                        <div
                                                            v-for="reward in pendingRewards"
                                                            :key="reward.id"
                                                            class="flex items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg"
                                                        >
                                                            <div
                                                                class="flex items-center gap-4"
                                                            >
                                                                <div
                                                                    class="w-10 h-10 bg-yellow-500 text-white rounded-full flex items-center justify-center"
                                                                >
                                                                    <i
                                                                        class="pi pi-gift"
                                                                    ></i>
                                                                </div>
                                                                <div>
                                                                    <h6
                                                                        class="font-medium text-gray-900"
                                                                    >
                                                                        {{
                                                                            reward
                                                                                .user
                                                                                .name
                                                                        }}
                                                                    </h6>
                                                                    <p
                                                                        class="text-sm text-gray-600"
                                                                    >
                                                                        Wants:
                                                                        {{
                                                                            reward
                                                                                .shop_item
                                                                                ?.name ||
                                                                            "Any item"
                                                                        }}
                                                                        • Card
                                                                        #{{
                                                                            reward.card_completion_number
                                                                        }}
                                                                    </p>
                                                                    <p
                                                                        class="text-xs text-gray-500"
                                                                    >
                                                                        {{
                                                                            formatDate(
                                                                                reward.created_at
                                                                            )
                                                                        }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="flex gap-2"
                                                            >
                                                                <button
                                                                    @click="
                                                                        approveReward(
                                                                            reward.id
                                                                        )
                                                                    "
                                                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors"
                                                                >
                                                                    <i
                                                                        class="pi pi-check mr-1"
                                                                    ></i>
                                                                    Approve
                                                                </button>
                                                                <button
                                                                    @click="
                                                                        rejectReward(
                                                                            reward.id,
                                                                            'Out of stock'
                                                                        )
                                                                    "
                                                                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition-colors"
                                                                >
                                                                    <i
                                                                        class="pi pi-times mr-1"
                                                                    ></i>
                                                                    Reject
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Approved Rewards (Ready to Claim) -->
                                                <div
                                                    v-if="
                                                        approvedRewards.length >
                                                        0
                                                    "
                                                    class="mb-6"
                                                >
                                                    <h5
                                                        class="text-md font-semibold text-green-700 mb-3 flex items-center"
                                                    >
                                                        <i
                                                            class="pi pi-check-circle mr-2"
                                                        ></i>
                                                        Ready to Claim ({{
                                                            approvedRewards.length
                                                        }})
                                                    </h5>
                                                    <div class="space-y-3">
                                                        <div
                                                            v-for="reward in approvedRewards"
                                                            :key="reward.id"
                                                            class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg"
                                                        >
                                                            <div
                                                                class="flex items-center gap-4"
                                                            >
                                                                <div
                                                                    class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center"
                                                                >
                                                                    <i
                                                                        class="pi pi-gift"
                                                                    ></i>
                                                                </div>
                                                                <div>
                                                                    <h6
                                                                        class="font-medium text-gray-900"
                                                                    >
                                                                        {{
                                                                            reward
                                                                                .user
                                                                                .name
                                                                        }}
                                                                    </h6>
                                                                    <p
                                                                        class="text-sm text-gray-600"
                                                                    >
                                                                        Approved:
                                                                        {{
                                                                            reward
                                                                                .shop_item
                                                                                ?.name ||
                                                                            "Any item"
                                                                        }}
                                                                        • Card
                                                                        #{{
                                                                            reward.card_completion_number
                                                                        }}
                                                                    </p>
                                                                    <p
                                                                        class="text-xs text-gray-500"
                                                                    >
                                                                        Approved:
                                                                        {{
                                                                            formatDate(
                                                                                reward.approved_at
                                                                            )
                                                                        }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="flex gap-2"
                                                            >
                                                                <button
                                                                    @click="
                                                                        markAsClaimed(
                                                                            reward.id
                                                                        )
                                                                    "
                                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-colors"
                                                                >
                                                                    <i
                                                                        class="pi pi-check mr-1"
                                                                    ></i>
                                                                    Mark as
                                                                    Claimed
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Claimed Rewards (History) -->
                                                <div
                                                    v-if="
                                                        claimedRewards.length >
                                                        0
                                                    "
                                                    class="mb-6"
                                                >
                                                    <h5
                                                        class="text-md font-semibold text-blue-700 mb-3 flex items-center"
                                                    >
                                                        <i
                                                            class="pi pi-history mr-2"
                                                        ></i>
                                                        Claimed History ({{
                                                            claimedRewards.length
                                                        }})
                                                    </h5>
                                                    <div class="space-y-3">
                                                        <div
                                                            v-for="reward in claimedRewards.slice(
                                                                0,
                                                                5
                                                            )"
                                                            :key="reward.id"
                                                            class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg opacity-75"
                                                        >
                                                            <div
                                                                class="flex items-center gap-4"
                                                            >
                                                                <div
                                                                    class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center"
                                                                >
                                                                    <i
                                                                        class="pi pi-check"
                                                                    ></i>
                                                                </div>
                                                                <div>
                                                                    <h6
                                                                        class="font-medium text-gray-900"
                                                                    >
                                                                        {{
                                                                            reward
                                                                                .user
                                                                                .name
                                                                        }}
                                                                    </h6>
                                                                    <p
                                                                        class="text-sm text-gray-600"
                                                                    >
                                                                        Claimed:
                                                                        {{
                                                                            reward
                                                                                .shop_item
                                                                                ?.name ||
                                                                            "Any item"
                                                                        }}
                                                                        • Card
                                                                        #{{
                                                                            reward.card_completion_number
                                                                        }}
                                                                    </p>
                                                                    <p
                                                                        class="text-xs text-gray-500"
                                                                    >
                                                                        Claimed:
                                                                        {{
                                                                            formatDate(
                                                                                reward.claimed_at
                                                                            )
                                                                        }}
                                                                    </p>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="flex items-center"
                                                            >
                                                                <span
                                                                    class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium"
                                                                >
                                                                    Completed
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        v-if="
                                                            claimedRewards.length >
                                                            5
                                                        "
                                                        class="text-center mt-3"
                                                    >
                                                        <span
                                                            class="text-sm text-gray-500"
                                                        >
                                                            And
                                                            {{
                                                                claimedRewards.length -
                                                                5
                                                            }}
                                                            more claimed
                                                            rewards...
                                                        </span>
                                                    </div>
                                                </div>

                                                <!-- Empty State -->
                                                <div
                                                    v-if="
                                                        loyaltyRewards.length ===
                                                        0
                                                    "
                                                    class="text-center py-8"
                                                >
                                                    <i
                                                        class="pi pi-gift text-gray-300 text-4xl mb-4"
                                                    ></i>
                                                    <h5
                                                        class="text-lg font-medium text-gray-600 mb-2"
                                                    >
                                                        No Loyalty Rewards Yet
                                                    </h5>
                                                    <p class="text-gray-500">
                                                        Rewards will appear here
                                                        when customers complete
                                                        their loyalty cards
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Settings Tab -->
                            <div v-if="activeTab === 'settings'">
                                <h3 class="text-lg font-semibold mb-6">
                                    Shop Settings
                                </h3>

                                <!-- Current Shop Images Preview -->
                                <div
                                    class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6"
                                >
                                    <!-- Current Logo -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Current Logo
                                        </label>
                                        <div
                                            class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center"
                                        >
                                            <img
                                                v-if="shop.logo_url"
                                                :src="shop.logo_url"
                                                alt="Shop Logo"
                                                class="w-20 h-20 object-cover rounded-lg mx-auto"
                                            />
                                            <div v-else class="text-gray-400">
                                                <i
                                                    class="pi pi-image text-3xl mb-2"
                                                ></i>
                                                <p class="text-sm">
                                                    No logo uploaded
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Current Banner -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-2"
                                        >
                                            Current Banner
                                        </label>
                                        <div
                                            class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center"
                                        >
                                            <img
                                                v-if="shop.banner_url"
                                                :src="shop.banner_url"
                                                alt="Shop Banner"
                                                class="w-full h-20 object-cover rounded-lg"
                                            />
                                            <div v-else class="text-gray-400">
                                                <i
                                                    class="pi pi-image text-3xl mb-2"
                                                ></i>
                                                <p class="text-sm">
                                                    No banner uploaded
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <form
                                    @submit.prevent="updateShop"
                                    class="space-y-6"
                                >
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Shop Name
                                        </label>
                                        <input
                                            v-model="shopForm.name"
                                            type="text"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        />
                                    </div>

                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Description
                                        </label>
                                        <textarea
                                            v-model="shopForm.description"
                                            rows="4"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        ></textarea>
                                    </div>

                                    <div
                                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                                    >
                                        <!-- Logo Upload -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1"
                                            >
                                                Shop Logo (Square recommended)
                                            </label>
                                            <input
                                                ref="logoInput"
                                                type="file"
                                                accept="image/*"
                                                @change="handleLogoUpload"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            />
                                            <p
                                                class="text-xs text-gray-500 mt-1"
                                            >
                                                Max 2MB, JPG/PNG
                                            </p>
                                        </div>

                                        <!-- Banner Upload -->
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700 mb-1"
                                            >
                                                Shop Banner (16:9 recommended)
                                            </label>
                                            <input
                                                ref="bannerInput"
                                                type="file"
                                                accept="image/*"
                                                @change="handleBannerUpload"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                            />
                                            <p
                                                class="text-xs text-gray-500 mt-1"
                                            >
                                                Max 4MB, JPG/PNG
                                            </p>
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        :disabled="updatingShop"
                                        class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors disabled:opacity-50"
                                    >
                                        <i
                                            v-if="updatingShop"
                                            class="pi pi-spin pi-spinner mr-2"
                                        ></i>
                                        Update Shop
                                    </button>
                                </form>
                            </div>
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

                <!-- Create/Edit Item Dialog -->
                <div
                    v-if="showCreateItem || showEditItem"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                >
                    <div
                        class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto"
                    >
                        <h3 class="text-xl font-bold text-gray-800 mb-4">
                            {{ showEditItem ? "Edit Item" : "Add New Item" }}
                        </h3>
                        <form @submit.prevent="saveItem">
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Item Name *
                                    </label>
                                    <input
                                        v-model="itemForm.name"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Enter item name"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Description
                                    </label>
                                    <textarea
                                        v-model="itemForm.description"
                                        rows="3"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Describe the item..."
                                    ></textarea>
                                </div>

                                <!-- Pricing Section -->
                                <div class="space-y-4 border-t pt-4">
                                    <h4
                                        class="text-md font-semibold text-gray-800"
                                    >
                                        Pricing
                                    </h4>

                                    <!-- Cash Price (Shop owners only see this) -->
                                    <div>
                                        <label
                                            class="block text-sm font-medium text-gray-700 mb-1"
                                        >
                                            Cash Price *
                                        </label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-3 top-2 text-gray-500"
                                                >₱</span
                                            >
                                            <input
                                                v-model.number="
                                                    itemForm.cash_price
                                                "
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                required
                                                class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                placeholder="0.00"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- Stock Management Section -->
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Stock Management
                                    </label>

                                    <!-- Unlimited Stock Toggle Button -->
                                    <div class="flex items-center gap-3 mb-3">
                                        <button
                                            type="button"
                                            @click="toggleUnlimitedStock"
                                            :class="
                                                itemForm.unlimited_stock
                                                    ? 'bg-green-500 hover:bg-green-600 text-white'
                                                    : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
                                            "
                                            class="px-4 py-2 rounded-lg transition-colors flex items-center gap-2 font-medium"
                                        >
                                            <i
                                                :class="
                                                    itemForm.unlimited_stock
                                                        ? 'pi pi-infinity'
                                                        : 'pi pi-box'
                                                "
                                            ></i>
                                            {{
                                                itemForm.unlimited_stock
                                                    ? "Unlimited"
                                                    : "Limited"
                                            }}
                                        </button>

                                        <span class="text-sm text-gray-500">
                                            {{
                                                itemForm.unlimited_stock
                                                    ? "This item will never run out of stock"
                                                    : "Set a specific quantity available"
                                            }}
                                        </span>
                                    </div>

                                    <!-- Stock Input -->
                                    <div
                                        v-if="!itemForm.unlimited_stock"
                                        class="flex items-center gap-3"
                                    >
                                        <div class="flex-1">
                                            <input
                                                v-model.number="itemForm.stock"
                                                type="number"
                                                min="0"
                                                required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                placeholder="Enter stock quantity"
                                            />
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 whitespace-nowrap"
                                        >
                                            items available
                                        </div>
                                    </div>

                                    <!-- Stock Status Indicator -->
                                    <div
                                        class="mt-2 p-3 rounded-lg"
                                        :class="
                                            itemForm.unlimited_stock
                                                ? 'bg-green-50 border border-green-200'
                                                : 'bg-blue-50 border border-blue-200'
                                        "
                                    >
                                        <div class="flex items-center gap-2">
                                            <i
                                                :class="
                                                    itemForm.unlimited_stock
                                                        ? 'pi pi-infinity text-green-600'
                                                        : 'pi pi-box text-blue-600'
                                                "
                                            ></i>
                                            <span
                                                class="text-sm font-medium"
                                                :class="
                                                    itemForm.unlimited_stock
                                                        ? 'text-green-700'
                                                        : 'text-blue-700'
                                                "
                                            >
                                                {{ getStockStatusText() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                    >
                                        Image
                                    </label>
                                    <input
                                        ref="imageInput"
                                        type="file"
                                        accept="image/*"
                                        @change="handleImageUpload"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    />
                                </div>

                                <div class="flex items-center">
                                    <input
                                        v-model="itemForm.is_active"
                                        type="checkbox"
                                        id="is_active"
                                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                    />
                                    <label
                                        for="is_active"
                                        class="ml-2 block text-sm text-gray-700"
                                    >
                                        Active (visible to customers)
                                    </label>
                                </div>
                            </div>

                            <div class="flex gap-3 mt-6">
                                <button
                                    type="button"
                                    @click="closeItemDialog"
                                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="savingItem"
                                    class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50"
                                >
                                    <i
                                        v-if="savingItem"
                                        class="pi pi-spin pi-spinner mr-2"
                                    ></i>
                                    {{ showEditItem ? "Update" : "Create" }}
                                    Item
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Loyalty Card Form Modal -->
                <div
                    v-if="showLoyaltyCardForm"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                >
                    <div
                        class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6"
                    >
                        <h3 class="text-xl font-bold text-gray-800 mb-4">
                            {{
                                hasLoyaltyCard
                                    ? "Edit Loyalty Card"
                                    : "Create Loyalty Card"
                            }}
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Card Name</label
                                >
                                <input
                                    v-model="loyaltyCardForm.name"
                                    type="text"
                                    placeholder="e.g., VIP Loyalty Card"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Description (Optional)</label
                                >
                                <textarea
                                    v-model="loyaltyCardForm.description"
                                    placeholder="Describe your loyalty program..."
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Required Purchases</label
                                >
                                <input
                                    v-model.number="
                                        loyaltyCardForm.required_purchases
                                    "
                                    type="number"
                                    :min="1"
                                    :max="100"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                                <small class="text-gray-500"
                                    >How many items customers need to buy for 1
                                    free item</small
                                >
                            </div>

                            <div class="flex items-center gap-2">
                                <input
                                    v-model="loyaltyCardForm.is_active"
                                    type="checkbox"
                                    id="loyalty-active"
                                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                />
                                <label
                                    for="loyalty-active"
                                    class="text-sm text-gray-700"
                                    >Active</label
                                >
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <button
                                type="button"
                                @click="showLoyaltyCardForm = false"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                @click="
                                    hasLoyaltyCard
                                        ? updateLoyaltyCard()
                                        : createLoyaltyCard()
                                "
                                class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg transition-colors"
                            >
                                {{ hasLoyaltyCard ? "Update" : "Create" }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Customer Loyalty Modal -->
                <div
                    v-if="showLoyaltyProgressModal"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                >
                    <div
                        class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6"
                    >
                        <h3 class="text-xl font-bold text-gray-800 mb-4">
                            {{
                                selectedCustomerLoyalty
                                    ? `${selectedCustomerLoyalty.user.name}'s Loyalty Progress`
                                    : "Customer Loyalty"
                            }}
                        </h3>
                        <div v-if="selectedCustomerLoyalty" class="space-y-6">
                            <!-- Progress Overview -->
                            <div
                                class="text-center bg-purple-50 p-6 rounded-lg"
                            >
                                <div
                                    class="text-3xl font-bold text-purple-600 mb-2"
                                >
                                    {{
                                        selectedCustomerLoyalty.current_purchases
                                    }}
                                </div>
                                <p class="text-gray-600">Total Purchases</p>

                                <div class="mt-4 text-center">
                                    <div
                                        class="text-xl font-semibold text-gray-900"
                                    >
                                        {{
                                            getCompletedCards(
                                                selectedCustomerLoyalty
                                            )
                                        }}
                                        Cards Completed
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Current Progress:
                                        {{
                                            getCurrentCardPurchases(
                                                selectedCustomerLoyalty
                                            )
                                        }}/{{ loyaltyCard.required_purchases }}
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div
                                    class="w-full bg-gray-200 rounded-full h-4 mt-4"
                                >
                                    <div
                                        class="bg-purple-500 h-4 rounded-full transition-all duration-300"
                                        :style="{
                                            width:
                                                getProgressPercentage(
                                                    selectedCustomerLoyalty
                                                ) + '%',
                                        }"
                                    ></div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="flex gap-3 justify-center">
                                <button
                                    @click="
                                        adjustLoyaltyCount(
                                            selectedCustomerLoyalty.user_id,
                                            'add'
                                        )
                                    "
                                    :disabled="adjustingLoyalty"
                                    class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
                                >
                                    <i class="pi pi-plus"></i>
                                    Add Purchase
                                </button>
                                <button
                                    @click="
                                        adjustLoyaltyCount(
                                            selectedCustomerLoyalty.user_id,
                                            'remove'
                                        )
                                    "
                                    :disabled="
                                        adjustingLoyalty ||
                                        selectedCustomerLoyalty.current_purchases ===
                                            0
                                    "
                                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2"
                                >
                                    <i class="pi pi-minus"></i>
                                    Remove Purchase
                                </button>
                            </div>

                            <!-- Last Activity -->
                            <div
                                v-if="selectedCustomerLoyalty.last_purchase_at"
                                class="text-center text-sm text-gray-500"
                            >
                                Last purchase:
                                {{
                                    formatDate(
                                        selectedCustomerLoyalty.last_purchase_at
                                    )
                                }}
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button
                                @click="showLoyaltyProgressModal = false"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Close
                            </button>
                        </div>
                    </div>
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
            shop: null,
            statistics: null,
            items: [],
            recentOrders: [],
            pendingOrders: [],

            // User data
            user: null,

            // UI State
            activeTab: "items",
            tabs: [
                { id: "items", name: "Items", icon: "pi pi-box" },
                { id: "orders", name: "Orders", icon: "pi pi-shopping-cart" },
                { id: "loyalty", name: "Loyalty Card", icon: "pi pi-gift" }, // ADD THIS LINE
                { id: "settings", name: "Settings", icon: "pi pi-cog" },
            ],

            // Create Shop
            showCreateShop: false,
            creatingShop: false,
            newShop: {
                name: "",
                description: "",
            },

            // Shop Settings
            shopForm: {
                name: "",
                description: "",
                logo: null,
                banner: null,
            },
            updatingShop: false,

            // Item Management
            showCreateItem: false,
            showEditItem: false,
            editingItem: null,
            savingItem: false,
            itemForm: {
                name: "",
                description: "",
                price: 0,
                cash_price: 0,
                stock: 0,
                unlimited_stock: false,
                is_active: true,
                image: null,
            },

            loyaltyCard: null,
            loyaltyProgress: [],
            loyaltyRewards: [],
            showLoyaltyCardForm: false,
            showLoyaltyProgressModal: false,
            selectedCustomerLoyalty: null,
            loyaltyCardForm: {
                name: "Loyalty Card",
                description: "",
                required_purchases: 10,
                is_active: true,
            },
            adjustingLoyalty: false,
            loadingLoyaltyData: false,
        };
    },

    computed: {
        isAdmin() {
            return this.user && this.user.role === "admin";
        },
        pendingRewards() {
            return this.loyaltyRewards.filter(
                (reward) => reward.status === "pending"
            );
        },
        approvedRewards() {
            return this.loyaltyRewards.filter(
                (reward) => reward.status === "approved"
            );
        },

        claimedRewards() {
            return this.loyaltyRewards.filter(
                (reward) => reward.status === "claimed"
            );
        },

        rejectedRewards() {
            return this.loyaltyRewards.filter(
                (reward) => reward.status === "rejected"
            );
        },

        loyaltyCardActive() {
            return this.loyaltyCard && this.loyaltyCard.is_active;
        },

        // Helper to check if shop has loyalty card
        hasLoyaltyCard() {
            return this.loyaltyCard !== null;
        },
        pendingOrdersCount() {
            return this.pendingOrders ? this.pendingOrders.length : 0;
        },

        // NEW: Count pending loyalty rewards
        pendingLoyaltyCount() {
            return this.pendingRewards.length;
        },

        // NEW: Total notifications count for sidebar
        totalShopNotifications() {
            return this.pendingOrdersCount + this.pendingLoyaltyCount;
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
            ];
        },
    },

    watch: {
        // Watch for changes to unlimited_stock
        "itemForm.unlimited_stock"(newVal) {
            if (newVal) {
                // When switching to unlimited, reset stock to 0 for display
                this.itemForm.stock = 0;
            } else if (!this.itemForm.stock) {
                // When switching to limited and stock is 0, set default
                this.itemForm.stock = 1;
            }
        },
    },

    methods: {
        async fetchShopData() {
            try {
                this.loading = true;

                // Try to get the admin's own shop first
                let response;
                try {
                    response = await axios.get("/api/shops/dashboard/my-shop");
                } catch (error) {
                    // If admin doesn't have a shop or API doesn't find it, try alternative approach
                    if (this.isAdmin && error.response?.status === 404) {
                        console.log(
                            "Admin shop not found via my-shop endpoint, trying alternative..."
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

                    // Update shop form
                    this.shopForm = {
                        name: this.shop.name,
                        description: this.shop.description || "",
                        logo: null,
                        banner: null,
                    };

                    // Fetch items
                    await this.fetchItems();
                }
            } catch (error) {
                console.error("Error fetching shop data:", error);
                if (error.response?.status === 404) {
                    this.shop = null; // No shop exists
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
                    `/api/shops/${this.shop.id}/items`
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

        async updateShop() {
            try {
                this.updatingShop = true;
                const formData = new FormData();

                formData.append("name", this.shopForm.name);
                formData.append("description", this.shopForm.description || "");

                if (this.shopForm.logo) {
                    formData.append("logo", this.shopForm.logo);
                }

                if (this.shopForm.banner) {
                    formData.append("banner", this.shopForm.banner);
                }

                const response = await axios.post(
                    `/api/shops/${this.shop.id}`,
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            "X-HTTP-Method-Override": "PUT",
                        },
                    }
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Shop updated successfully",
                    });

                    this.shop = response.data.shop;
                    // Reset file inputs
                    this.shopForm.logo = null;
                    this.shopForm.banner = null;
                    this.$refs.logoInput.value = "";
                    this.$refs.bannerInput.value = "";
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to update shop",
                });
            } finally {
                this.updatingShop = false;
            }
        },

        async saveItem() {
            try {
                this.savingItem = true;
                const formData = new FormData();

                formData.append("name", this.itemForm.name);
                formData.append("description", this.itemForm.description || "");

                // For shop owners, only send cash_price
                formData.append("cash_price", this.itemForm.cash_price || 0);

                formData.append(
                    "is_active",
                    this.itemForm.is_active ? "1" : "0"
                );
                formData.append(
                    "unlimited_stock",
                    this.itemForm.unlimited_stock ? "1" : "0"
                );

                // Only send stock if not unlimited
                if (!this.itemForm.unlimited_stock) {
                    formData.append("stock", this.itemForm.stock || 0);
                }

                if (this.itemForm.image) {
                    formData.append("image", this.itemForm.image);
                }

                let response;
                if (this.showEditItem) {
                    // Update existing item
                    formData.append("_method", "PUT");
                    response = await axios.post(
                        `/api/shops/${this.shop.id}/items/${this.editingItem.id}`,
                        formData,
                        {
                            headers: { "Content-Type": "multipart/form-data" },
                        }
                    );
                } else {
                    // Create new item
                    response = await axios.post(
                        `/api/shops/${this.shop.id}/items`,
                        formData,
                        {
                            headers: { "Content-Type": "multipart/form-data" },
                        }
                    );
                }

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    this.closeItemDialog();
                    await this.fetchItems();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message || "Failed to save item",
                });
            } finally {
                this.savingItem = false;
            }
        },

        editItem(item) {
            this.editingItem = item;
            this.itemForm = {
                name: item.name,
                description: item.description || "",
                price: item.price || 0,
                cash_price: item.cash_price || 0,
                stock: item.stock || 0,
                unlimited_stock: item.stock === null,
                is_active: item.is_active,
                image: null,
            };
            this.showEditItem = true;
        },

        async deleteItem(item) {
            if (!confirm(`Are you sure you want to delete "${item.name}"?`)) {
                return;
            }

            try {
                const response = await axios.delete(
                    `/api/shops/${this.shop.id}/items/${item.id}`
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Item deleted successfully",
                    });

                    await this.fetchItems();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to delete item",
                });
            }
        },

        closeItemDialog() {
            this.showCreateItem = false;
            this.showEditItem = false;
            this.editingItem = null;
            this.itemForm = {
                name: "",
                description: "",
                price: 0,
                cash_price: 0,
                stock: 0,
                unlimited_stock: false,
                is_active: true,
                image: null,
            };
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = "";
            }
        },

        toggleUnlimitedStock() {
            this.itemForm.unlimited_stock = !this.itemForm.unlimited_stock;

            if (!this.itemForm.unlimited_stock && !this.itemForm.stock) {
                this.itemForm.stock = 1;
            }
        },

        getStockStatusText() {
            if (this.itemForm.unlimited_stock) {
                return "Unlimited stock - customers can always purchase this item";
            } else {
                const stock = this.itemForm.stock || 0;
                if (stock === 0) {
                    return "Out of stock - customers cannot purchase this item";
                } else if (stock <= 5) {
                    return `Low stock - only ${stock} items remaining`;
                } else {
                    return `${stock} items available for purchase`;
                }
            }
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    this.$toast?.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Image file must be smaller than 2MB",
                    });
                    event.target.value = "";
                    return;
                }
                this.itemForm.image = file;
            }
        },

        handleLogoUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.shopForm.logo = file;
            }
        },

        handleBannerUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.shopForm.banner = file;
            }
        },

        async approveOrder(order) {
            try {
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/items/purchases/${order.id}/approve`
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Order approved successfully",
                    });

                    await this.fetchShopData();
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
                    }
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Order rejected successfully",
                    });

                    await this.fetchShopData();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to reject order",
                });
            }
        },

        formatCash(amount) {
            return parseFloat(amount || 0).toFixed(2);
        },
        formatDate(date) {
            return new Date(date).toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        formatPoints(points) {
            return (points || 0).toLocaleString();
        },
        async fetchLoyaltyCard() {
            if (!this.shop) return;

            try {
                this.loadingLoyaltyData = true;
                const response = await axios.get(
                    `/api/shops/${this.shop.id}/loyalty-card`
                );

                if (response.data.success) {
                    this.loyaltyCard = response.data.loyalty_card;
                    await this.fetchLoyaltyProgress();
                }
            } catch (error) {
                console.error("Error fetching loyalty card:", error);
            } finally {
                this.loadingLoyaltyData = false;
            }
        },

        async fetchLoyaltyProgress() {
            if (!this.loyaltyCard) return;

            try {
                const response = await axios.get(
                    `/api/shops/${this.shop.id}/loyalty-progress`
                );

                if (response.data.success) {
                    this.loyaltyProgress = response.data.progress;
                    this.loyaltyRewards = response.data.rewards;
                }
            } catch (error) {
                console.error("Error fetching loyalty progress:", error);
            }
        },

        async createLoyaltyCard() {
            try {
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/loyalty-card`,
                    this.loyaltyCardForm
                );

                if (response.data.success) {
                    this.loyaltyCard = response.data.loyalty_card;
                    this.showLoyaltyCardForm = false;
                    this.$toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Loyalty card created successfully!",
                    });
                    await this.fetchLoyaltyProgress();
                }
            } catch (error) {
                this.$toast.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to create loyalty card",
                });
            }
        },

        async updateLoyaltyCard() {
            try {
                const response = await axios.put(
                    `/api/shops/${this.shop.id}/loyalty-card`,
                    this.loyaltyCardForm
                );

                if (response.data.success) {
                    this.loyaltyCard = response.data.loyalty_card;
                    this.showLoyaltyCardForm = false;
                    this.$toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Loyalty card updated successfully!",
                    });
                }
            } catch (error) {
                this.$toast.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to update loyalty card",
                });
            }
        },

        async toggleLoyaltyCard() {
            try {
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/loyalty-card/toggle`
                );

                if (response.data.success) {
                    this.loyaltyCard.is_active = !this.loyaltyCard.is_active;
                    this.$toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: `Loyalty card ${
                            this.loyaltyCard.is_active
                                ? "activated"
                                : "deactivated"
                        }`,
                    });
                }
            } catch (error) {
                this.$toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to toggle loyalty card",
                });
            }
        },

        async adjustLoyaltyCount(customerId, action) {
            try {
                this.adjustingLoyalty = true;
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/loyalty-progress/${customerId}/adjust`,
                    {
                        action: action, // 'add' or 'remove'
                    }
                );

                if (response.data.success) {
                    await this.fetchLoyaltyProgress();
                    this.$toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: `Loyalty count ${
                            action === "add" ? "added" : "removed"
                        } successfully`,
                    });
                }
            } catch (error) {
                this.$toast.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to adjust loyalty count",
                });
            } finally {
                this.adjustingLoyalty = false;
            }
        },

        async approveReward(rewardId) {
            try {
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/loyalty-rewards/${rewardId}/approve`
                );

                if (response.data.success) {
                    await this.fetchLoyaltyProgress();
                    this.$toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Reward approved successfully",
                    });
                }
            } catch (error) {
                this.$toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to approve reward",
                });
            }
        },

        async rejectReward(rewardId, reason) {
            try {
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/loyalty-rewards/${rewardId}/reject`,
                    {
                        reason: reason,
                    }
                );

                if (response.data.success) {
                    await this.fetchLoyaltyProgress();
                    this.$toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Reward rejected",
                    });
                }
            } catch (error) {
                this.$toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to reject reward",
                });
            }
        },

        openLoyaltyCardForm() {
            if (this.loyaltyCard) {
                // Edit mode
                this.loyaltyCardForm = {
                    name: this.loyaltyCard.name,
                    description: this.loyaltyCard.description,
                    required_purchases: this.loyaltyCard.required_purchases,
                    is_active: this.loyaltyCard.is_active,
                };
            } else {
                // Create mode
                this.loyaltyCardForm = {
                    name: "Loyalty Card",
                    description: "",
                    required_purchases: 10,
                    is_active: true,
                };
            }
            this.showLoyaltyCardForm = true;
        },

        openCustomerLoyaltyModal(customer) {
            this.selectedCustomerLoyalty = customer;
            this.showLoyaltyProgressModal = true;
        },

        getProgressPercentage(customer) {
            if (!this.loyaltyCard) return 0;
            const currentInCard =
                customer.current_purchases %
                this.loyaltyCard.required_purchases;
            return (currentInCard / this.loyaltyCard.required_purchases) * 100;
        },

        getCurrentCardPurchases(customer) {
            if (!this.loyaltyCard) return 0;
            return (
                customer.current_purchases % this.loyaltyCard.required_purchases
            );
        },

        getCompletedCards(customer) {
            if (!this.loyaltyCard) return 0;
            return Math.floor(
                customer.current_purchases / this.loyaltyCard.required_purchases
            );
        },

        // ===== MODIFY EXISTING METHODS =====

        // Modify the existing approvePurchase method to include loyalty update
        async approvePurchase(purchase) {
            try {
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/items/purchases/${purchase.id}/approve`
                );

                if (response.data.success) {
                    // Refresh data including loyalty progress
                    await Promise.all([
                        this.fetchShopData(),
                        this.fetchLoyaltyProgress(),
                    ]);

                    this.$toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Purchase approved successfully",
                    });
                }
            } catch (error) {
                this.$toast.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to approve purchase",
                });
            }
        },
        // Add this method to your existing methods in MyShop.vue

        async markAsClaimed(rewardId) {
            try {
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/loyalty-rewards/${rewardId}/mark-claimed`
                );

                if (response.data.success) {
                    await this.fetchLoyaltyProgress();
                    this.$toast.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Reward marked as claimed successfully",
                    });
                }
            } catch (error) {
                this.$toast.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to mark reward as claimed",
                });
            }
        },
    },

    async mounted() {
        await this.fetchUserData();
        await this.fetchShopData();
        await this.fetchLoyaltyCard();
    },
};
</script>
