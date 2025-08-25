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
                    <div class="bg-white rounded-xl shadow-lg">
                        <div class="border-b border-gray-200">
                            <nav class="flex space-x-8 px-6">
                                <button
                                    v-for="tab in tabs"
                                    :key="tab.id"
                                    @click="activeTab = tab.id"
                                    :class="[
                                        'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                        activeTab === tab.id
                                            ? 'border-green-500 text-green-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    ]"
                                >
                                    <i :class="tab.icon + ' mr-2'"></i>
                                    {{ tab.name }}
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

                                                        <!-- Order Details - Made smaller -->
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
        };
    },

    computed: {
        isAdmin() {
            return this.user && this.user.role === "admin";
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

        formatPoints(points) {
            return (points || 0).toLocaleString();
        },
    },

    async mounted() {
        await this.fetchUserData();
        await this.fetchShopData();
    },
};
</script>
