<template>
    <Layout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Point Shop & Pricing Management
                    </h1>
                    <p class="text-gray-600 mt-2">
                        Manage point prices, point shop availability, and
                        point-based orders across all shops
                    </p>
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
                                    'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                    activeTab === tab.id
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                ]"
                            >
                                <i :class="tab.icon + ' mr-2'"></i>
                                {{ tab.name }}
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Items Management Tab -->
                <div v-if="activeTab === 'items'">
                    <!-- Search and Filters -->
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Search Items
                                </label>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search by item name..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Filter by Shop
                                </label>
                                <select
                                    v-model="selectedShop"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">All Shops</option>
                                    <option
                                        v-for="shop in shops"
                                        :key="shop.id"
                                        :value="shop.id"
                                    >
                                        {{ shop.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Filter by Status
                                </label>
                                <select
                                    v-model="statusFilter"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">All Items</option>
                                    <option value="no_points">
                                        No Point Price
                                    </option>
                                    <option value="has_points">
                                        Has Point Price
                                    </option>
                                    <option value="in_point_shop">
                                        In Point Shop
                                    </option>
                                    <option value="not_in_point_shop">
                                        Not in Point Shop
                                    </option>
                                    <option value="active">Active Only</option>
                                    <option value="inactive">
                                        Inactive Only
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Actions
                                </label>
                                <button
                                    @click="refreshData"
                                    class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                                >
                                    <i class="pi pi-refresh mr-2"></i>
                                    Refresh
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="flex justify-center py-12">
                        <i
                            class="pi pi-spin pi-spinner text-blue-500 text-3xl"
                        ></i>
                    </div>

                    <!-- Items Table -->
                    <div
                        v-else
                        class="bg-white rounded-xl shadow-lg overflow-hidden"
                    >
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Shop Items ({{ filteredItems.length }})
                                </h3>
                                <div class="flex space-x-3">
                                    <button
                                        @click="showBulkPricing = true"
                                        :disabled="selectedItems.length === 0"
                                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <i class="pi pi-tags mr-2"></i>
                                        Bulk Price ({{ selectedItems.length }})
                                    </button>
                                    <button
                                        @click="showBulkPointShop = true"
                                        :disabled="selectedItems.length === 0"
                                        class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <i class="pi pi-star mr-2"></i>
                                        Bulk Point Shop ({{
                                            selectedItems.length
                                        }})
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            <input
                                                type="checkbox"
                                                @change="toggleSelectAll"
                                                :checked="allItemsSelected"
                                                class="rounded border-gray-300"
                                            />
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Item
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Shop
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Cash Price
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Current Points
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            New Points Price
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Point Shop Status
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="item in paginatedItems"
                                        :key="item.id"
                                        class="hover:bg-gray-50"
                                    >
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input
                                                type="checkbox"
                                                v-model="selectedItems"
                                                :value="item.id"
                                                class="rounded border-gray-300"
                                            />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <img
                                                    v-if="item.image"
                                                    :src="`/storage/${item.image}`"
                                                    :alt="item.name"
                                                    class="w-10 h-10 object-cover rounded-lg mr-3"
                                                />
                                                <div
                                                    v-else
                                                    class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center mr-3"
                                                >
                                                    <i
                                                        class="pi pi-image text-gray-400 text-sm"
                                                    ></i>
                                                </div>
                                                <div>
                                                    <div
                                                        class="text-sm font-medium text-gray-900"
                                                    >
                                                        {{ item.name }}
                                                    </div>
                                                    <div
                                                        class="text-sm text-gray-500 max-w-xs truncate"
                                                    >
                                                        {{ item.description }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{
                                                    item.shop?.name ||
                                                    "Point Shop"
                                                }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{
                                                    item.shop?.owner?.name ||
                                                    "Admin"
                                                }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                class="text-sm font-medium text-green-600"
                                            >
                                                ₱{{
                                                    parseFloat(
                                                        item.cash_price || 0
                                                    ).toFixed(2)
                                                }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                :class="[
                                                    'text-sm font-medium',
                                                    item.price > 0
                                                        ? 'text-blue-600'
                                                        : 'text-gray-400',
                                                ]"
                                            >
                                                {{
                                                    item.price > 0
                                                        ? formatPoints(
                                                              item.price
                                                          ) + " points"
                                                        : "Not set"
                                                }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                class="flex items-center space-x-2"
                                            >
                                                <input
                                                    v-model.number="
                                                        item.newPrice
                                                    "
                                                    type="number"
                                                    min="0"
                                                    placeholder="0"
                                                    class="w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                                />
                                                <span
                                                    class="text-xs text-gray-500"
                                                    >points</span
                                                >
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                class="flex flex-col space-y-1"
                                            >
                                                <span
                                                    :class="[
                                                        'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                        item.is_active_in_point_shop
                                                            ? 'bg-purple-100 text-purple-800'
                                                            : 'bg-gray-100 text-gray-800',
                                                    ]"
                                                >
                                                    {{
                                                        item.is_active_in_point_shop
                                                            ? "In Point Shop"
                                                            : "Not in Point Shop"
                                                    }}
                                                </span>
                                                <button
                                                    @click="
                                                        togglePointShopStatus(
                                                            item
                                                        )
                                                    "
                                                    :disabled="
                                                        updatingPointShop.includes(
                                                            item.id
                                                        )
                                                    "
                                                    :class="[
                                                        'text-xs px-2 py-1 rounded transition-colors',
                                                        item.is_active_in_point_shop
                                                            ? 'text-red-600 hover:bg-red-50'
                                                            : 'text-purple-600 hover:bg-purple-50',
                                                        updatingPointShop.includes(
                                                            item.id
                                                        )
                                                            ? 'opacity-50 cursor-not-allowed'
                                                            : '',
                                                    ]"
                                                >
                                                    <i
                                                        v-if="
                                                            updatingPointShop.includes(
                                                                item.id
                                                            )
                                                        "
                                                        class="pi pi-spin pi-spinner mr-1"
                                                    ></i>
                                                    <i
                                                        v-else
                                                        :class="
                                                            item.is_active_in_point_shop
                                                                ? 'pi pi-eye-slash'
                                                                : 'pi pi-plus'
                                                        "
                                                        class="mr-1"
                                                    ></i>
                                                    {{
                                                        item.is_active_in_point_shop
                                                            ? "Remove"
                                                            : "Add"
                                                    }}
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="[
                                                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
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
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                        >
                                            <button
                                                @click="updateSinglePrice(item)"
                                                :disabled="
                                                    !item.newPrice ||
                                                    item.newPrice <= 0 ||
                                                    updatingPrices.includes(
                                                        item.id
                                                    )
                                                "
                                                class="text-blue-600 hover:text-blue-900 disabled:text-gray-400 disabled:cursor-not-allowed mr-3"
                                            >
                                                <i
                                                    v-if="
                                                        updatingPrices.includes(
                                                            item.id
                                                        )
                                                    "
                                                    class="pi pi-spin pi-spinner"
                                                ></i>
                                                <i
                                                    v-else
                                                    class="pi pi-check"
                                                ></i>
                                                Update Price
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div
                            class="px-6 py-4 bg-gray-50 border-t border-gray-200"
                        >
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Showing
                                    {{
                                        (currentPage - 1) * itemsPerPage + 1
                                    }}
                                    to
                                    {{
                                        Math.min(
                                            currentPage * itemsPerPage,
                                            filteredItems.length
                                        )
                                    }}
                                    of {{ filteredItems.length }} items
                                </div>
                                <div class="flex space-x-2">
                                    <button
                                        @click="currentPage--"
                                        :disabled="currentPage === 1"
                                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Previous
                                    </button>
                                    <button
                                        @click="currentPage++"
                                        :disabled="currentPage >= totalPages"
                                        class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Tab -->
                <div v-if="activeTab === 'orders'">
                    <!-- Orders Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
                                        {{ pendingOrders.length }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <i
                                        class="pi pi-check text-green-600 text-xl"
                                    ></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-600">
                                        Completed Orders
                                    </p>
                                    <p class="text-2xl font-bold">
                                        {{ completedOrders.length }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <div class="flex items-center">
                                <div class="p-2 bg-red-100 rounded-lg">
                                    <i
                                        class="pi pi-times text-red-600 text-xl"
                                    ></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm text-gray-600">
                                        Rejected Orders
                                    </p>
                                    <p class="text-2xl font-bold">
                                        {{ rejectedOrders.length }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Orders -->
                    <div v-if="loadingOrders" class="flex justify-center py-12">
                        <i
                            class="pi pi-spin pi-spinner text-blue-500 text-3xl"
                        ></i>
                    </div>

                    <!-- Orders Content -->
                    <div v-else class="bg-white rounded-xl shadow-lg">
                        <div class="border-b border-gray-200">
                            <nav class="flex space-x-8 px-6">
                                <button
                                    v-for="orderTab in orderTabs"
                                    :key="orderTab.id"
                                    @click="activeOrderTab = orderTab.id"
                                    :class="[
                                        'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                        activeOrderTab === orderTab.id
                                            ? 'border-blue-500 text-blue-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    ]"
                                >
                                    <i :class="orderTab.icon + ' mr-2'"></i>
                                    {{ orderTab.name }}
                                    <span
                                        v-if="orderTab.count > 0"
                                        class="ml-2 px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded-full"
                                    >
                                        {{ orderTab.count }}
                                    </span>
                                </button>
                            </nav>
                        </div>

                        <div class="p-6">
                            <!-- Pending Orders -->
                            <div v-if="activeOrderTab === 'pending'">
                                <h3 class="text-lg font-semibold mb-6">
                                    Pending Point Orders
                                </h3>

                                <div
                                    v-if="pendingOrders.length > 0"
                                    class="space-y-4"
                                >
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
                                                        </p>
                                                        <p
                                                            class="text-sm text-gray-500"
                                                        >
                                                            Shop:
                                                            {{
                                                                order.shop
                                                                    ?.name ||
                                                                "Point Shop"
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

                                                <div
                                                    class="flex items-center space-x-4 mt-3"
                                                >
                                                    <div
                                                        class="flex items-center space-x-2 text-sm"
                                                    >
                                                        <span
                                                            class="text-gray-600"
                                                            >Unit Price:</span
                                                        >
                                                        <span
                                                            class="font-medium text-blue-600"
                                                        >
                                                            {{
                                                                formatPoints(
                                                                    order.price_paid
                                                                )
                                                            }}
                                                            points
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
                                                            class="font-semibold text-blue-600 text-base"
                                                        >
                                                            {{
                                                                formatPoints(
                                                                    order.price_paid *
                                                                        order.quantity
                                                                )
                                                            }}
                                                            points
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="flex flex-col space-y-2 ml-6"
                                            >
                                                <button
                                                    @click="approveOrder(order)"
                                                    :disabled="
                                                        processingOrders.includes(
                                                            order.id
                                                        )
                                                    "
                                                    class="px-4 py-2 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition-colors flex items-center disabled:opacity-50"
                                                >
                                                    <i
                                                        v-if="
                                                            processingOrders.includes(
                                                                order.id
                                                            )
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
                                                    @click="rejectOrder(order)"
                                                    :disabled="
                                                        processingOrders.includes(
                                                            order.id
                                                        )
                                                    "
                                                    class="px-4 py-2 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition-colors flex items-center disabled:opacity-50"
                                                >
                                                    <i
                                                        v-if="
                                                            processingOrders.includes(
                                                                order.id
                                                            )
                                                        "
                                                        class="pi pi-spin pi-spinner mr-1"
                                                    ></i>
                                                    <i
                                                        v-else
                                                        class="pi pi-times mr-1"
                                                    ></i>
                                                    Reject
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="text-center py-12">
                                    <i
                                        class="pi pi-clock text-gray-300 text-5xl mb-4"
                                    ></i>
                                    <h3
                                        class="text-lg font-medium text-gray-600 mb-2"
                                    >
                                        No Pending Orders
                                    </h3>
                                    <p class="text-gray-500">
                                        All point orders have been processed
                                    </p>
                                </div>
                            </div>

                            <!-- Completed Orders -->
                            <div v-if="activeOrderTab === 'completed'">
                                <h3 class="text-lg font-semibold mb-6">
                                    Completed Point Orders
                                </h3>

                                <div
                                    v-if="completedOrders.length > 0"
                                    class="space-y-3"
                                >
                                    <div
                                        v-for="order in completedOrders"
                                        :key="order.id"
                                        class="flex items-center justify-between p-4 border border-gray-200 rounded-lg bg-green-50"
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
                                                        {{ order.user?.name }} •
                                                        Qty:
                                                        {{ order.quantity }}
                                                    </p>
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        Shop:
                                                        {{
                                                            order.shop?.name ||
                                                            "Point Shop"
                                                        }}
                                                        •
                                                        {{
                                                            new Date(
                                                                order.created_at
                                                            ).toLocaleDateString()
                                                        }}
                                                        • Total:
                                                        {{
                                                            formatPoints(
                                                                order.price_paid *
                                                                    order.quantity
                                                            )
                                                        }}
                                                        points
                                                    </p>
                                                </div>
                                                <span
                                                    class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-800 ml-4"
                                                >
                                                    Completed
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="text-center py-12">
                                    <i
                                        class="pi pi-check text-gray-300 text-5xl mb-4"
                                    ></i>
                                    <h3
                                        class="text-lg font-medium text-gray-600 mb-2"
                                    >
                                        No Completed Orders
                                    </h3>
                                    <p class="text-gray-500">
                                        Completed point orders will appear here
                                    </p>
                                </div>
                            </div>

                            <!-- Rejected Orders -->
                            <div v-if="activeOrderTab === 'rejected'">
                                <h3 class="text-lg font-semibold mb-6">
                                    Rejected Point Orders
                                </h3>

                                <div
                                    v-if="rejectedOrders.length > 0"
                                    class="space-y-3"
                                >
                                    <div
                                        v-for="order in rejectedOrders"
                                        :key="order.id"
                                        class="flex items-center justify-between p-4 border border-gray-200 rounded-lg bg-red-50"
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
                                                        {{ order.user?.name }} •
                                                        Qty:
                                                        {{ order.quantity }}
                                                    </p>
                                                    <p
                                                        class="text-sm text-gray-500"
                                                    >
                                                        Shop:
                                                        {{
                                                            order.shop?.name ||
                                                            "Point Shop"
                                                        }}
                                                        •
                                                        {{
                                                            new Date(
                                                                order.created_at
                                                            ).toLocaleDateString()
                                                        }}
                                                        • Total:
                                                        {{
                                                            formatPoints(
                                                                order.price_paid *
                                                                    order.quantity
                                                            )
                                                        }}
                                                        points
                                                    </p>
                                                    <p
                                                        v-if="
                                                            order.rejection_reason
                                                        "
                                                        class="text-sm text-red-600 mt-1"
                                                    >
                                                        Reason:
                                                        {{
                                                            order.rejection_reason
                                                        }}
                                                    </p>
                                                </div>
                                                <span
                                                    class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-800 ml-4"
                                                >
                                                    Rejected
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else class="text-center py-12">
                                    <i
                                        class="pi pi-times text-gray-300 text-5xl mb-4"
                                    ></i>
                                    <h3
                                        class="text-lg font-medium text-gray-600 mb-2"
                                    >
                                        No Rejected Orders
                                    </h3>
                                    <p class="text-gray-500">
                                        Rejected point orders will appear here
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Pricing Modal -->
                <div
                    v-if="showBulkPricing"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                >
                    <div
                        class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6"
                    >
                        <h3 class="text-xl font-bold text-gray-800 mb-4">
                            Bulk Set Point Prices
                        </h3>
                        <p class="text-gray-600 mb-4">
                            Set point price for
                            {{ selectedItems.length }} selected items
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Point Price *
                                </label>
                                <div class="relative">
                                    <input
                                        v-model.number="bulkPrice"
                                        type="number"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="0"
                                    />
                                    <span
                                        class="absolute right-3 top-2 text-gray-500"
                                        >points</span
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3 mt-6">
                            <button
                                type="button"
                                @click="showBulkPricing = false"
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                @click="updateBulkPrices"
                                :disabled="
                                    !bulkPrice || bulkPrice <= 0 || bulkUpdating
                                "
                                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50"
                            >
                                <i
                                    v-if="bulkUpdating"
                                    class="pi pi-spin pi-spinner mr-2"
                                ></i>
                                Update Prices
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bulk Point Shop Modal -->
                <div
                    v-if="showBulkPointShop"
                    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                >
                    <div
                        class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6"
                    >
                        <h3 class="text-xl font-bold text-gray-800 mb-4">
                            Bulk Point Shop Management
                        </h3>
                        <p class="text-gray-600 mb-4">
                            Update point shop status for
                            {{ selectedItems.length }} selected items
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Point Shop Status *
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input
                                            v-model="bulkPointShopStatus"
                                            type="radio"
                                            value="true"
                                            class="mr-2"
                                        />
                                        <span>Add to Point Shop</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input
                                            v-model="bulkPointShopStatus"
                                            type="radio"
                                            value="false"
                                            class="mr-2"
                                        />
                                        <span>Remove from Point Shop</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-3 mt-6">
                            <button
                                type="button"
                                @click="showBulkPointShop = false"
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                @click="updateBulkPointShop"
                                :disabled="
                                    !bulkPointShopStatus ||
                                    bulkPointShopUpdating
                                "
                                class="flex-1 bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50"
                            >
                                <i
                                    v-if="bulkPointShopUpdating"
                                    class="pi pi-spin pi-spinner mr-2"
                                ></i>
                                Update Point Shop Status
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
    name: "AdminPointPricing",
    data() {
        return {
            loading: true,
            loadingOrders: false,
            items: [],
            shops: [],

            // Orders data
            pendingOrders: [],
            completedOrders: [],
            rejectedOrders: [],
            processingOrders: [],

            // UI State
            activeTab: "items",
            tabs: [
                { id: "items", name: "Item Management", icon: "pi pi-box" },
                {
                    id: "orders",
                    name: "Point Orders",
                    icon: "pi pi-shopping-cart",
                },
            ],

            activeOrderTab: "pending",

            // Search and filters
            searchQuery: "",
            selectedShop: "",
            statusFilter: "",

            // Selection
            selectedItems: [],

            // Pagination
            currentPage: 1,
            itemsPerPage: 20,

            // Bulk operations
            showBulkPricing: false,
            bulkPrice: null,
            bulkUpdating: false,

            // Bulk Point Shop operations
            showBulkPointShop: false,
            bulkPointShopStatus: "",
            bulkPointShopUpdating: false,

            // Individual updates
            updatingPrices: [],
            updatingPointShop: [],
        };
    },

    computed: {
        orderTabs() {
            return [
                {
                    id: "pending",
                    name: "Pending",
                    icon: "pi pi-clock",
                    count: this.pendingOrders.length,
                },
                {
                    id: "completed",
                    name: "Completed",
                    icon: "pi pi-check",
                    count: this.completedOrders.length,
                },
                {
                    id: "rejected",
                    name: "Rejected",
                    icon: "pi pi-times",
                    count: this.rejectedOrders.length,
                },
            ];
        },

        filteredItems() {
            let filtered = this.items;

            // Search by item name
            if (this.searchQuery) {
                filtered = filtered.filter((item) =>
                    item.name
                        .toLowerCase()
                        .includes(this.searchQuery.toLowerCase())
                );
            }

            // Filter by shop
            if (this.selectedShop) {
                filtered = filtered.filter(
                    (item) => item.shop_id == this.selectedShop
                );
            }

            // Filter by status
            if (this.statusFilter) {
                switch (this.statusFilter) {
                    case "no_points":
                        filtered = filtered.filter(
                            (item) => !item.price || item.price <= 0
                        );
                        break;
                    case "has_points":
                        filtered = filtered.filter((item) => item.price > 0);
                        break;
                    case "in_point_shop":
                        filtered = filtered.filter(
                            (item) => item.is_active_in_point_shop
                        );
                        break;
                    case "not_in_point_shop":
                        filtered = filtered.filter(
                            (item) => !item.is_active_in_point_shop
                        );
                        break;
                    case "active":
                        filtered = filtered.filter((item) => item.is_active);
                        break;
                    case "inactive":
                        filtered = filtered.filter((item) => !item.is_active);
                        break;
                }
            }

            return filtered;
        },

        paginatedItems() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredItems.slice(start, end);
        },

        totalPages() {
            return Math.ceil(this.filteredItems.length / this.itemsPerPage);
        },

        allItemsSelected() {
            return (
                this.paginatedItems.length > 0 &&
                this.paginatedItems.every((item) =>
                    this.selectedItems.includes(item.id)
                )
            );
        },
    },

    watch: {
        filteredItems() {
            this.currentPage = 1; // Reset to first page when filters change
        },

        activeTab(newTab) {
            if (newTab === "orders" && this.pendingOrders.length === 0) {
                this.fetchOrders();
            }
        },
    },

    methods: {
        async fetchData() {
            try {
                this.loading = true;

                // Fetch all items with shop information
                const itemsResponse = await axios.get("/api/admin/shop-items");
                if (itemsResponse.data.success) {
                    this.items = itemsResponse.data.items.map((item) => ({
                        ...item,
                        newPrice: item.price || null,
                    }));
                }

                // Fetch all shops for filter dropdown
                const shopsResponse = await axios.get(
                    "/api/admin/shops/dropdown"
                );
                if (shopsResponse.data.success) {
                    this.shops = shopsResponse.data.shops;
                }
            } catch (error) {
                console.error("Error fetching data:", error);
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load items and shops",
                });
            } finally {
                this.loading = false;
            }
        },

        async fetchOrders() {
            try {
                this.loadingOrders = true;

                const response = await axios.get("/api/admin/point-orders");
                if (response.data.success) {
                    this.pendingOrders = response.data.pending_orders || [];
                    this.completedOrders = response.data.completed_orders || [];
                    this.rejectedOrders = response.data.rejected_orders || [];
                }
            } catch (error) {
                console.error("Error fetching orders:", error);
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load orders",
                });
            } finally {
                this.loadingOrders = false;
            }
        },

        async approveOrder(order) {
            this.processingOrders.push(order.id);

            try {
                const response = await axios.post(
                    `/api/admin/point-orders/${order.id}/approve`
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Order approved successfully",
                    });

                    // Move order from pending to completed
                    const orderIndex = this.pendingOrders.findIndex(
                        (o) => o.id === order.id
                    );
                    if (orderIndex > -1) {
                        const approvedOrder = this.pendingOrders.splice(
                            orderIndex,
                            1
                        )[0];
                        approvedOrder.status = "completed";
                        this.completedOrders.unshift(approvedOrder);
                    }
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to approve order",
                });
            } finally {
                this.processingOrders = this.processingOrders.filter(
                    (id) => id !== order.id
                );
            }
        },

        async rejectOrder(order) {
            const reason = prompt("Reason for rejection (optional):");

            this.processingOrders.push(order.id);

            try {
                const response = await axios.post(
                    `/api/admin/point-orders/${order.id}/reject`,
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

                    // Move order from pending to rejected
                    const orderIndex = this.pendingOrders.findIndex(
                        (o) => o.id === order.id
                    );
                    if (orderIndex > -1) {
                        const rejectedOrder = this.pendingOrders.splice(
                            orderIndex,
                            1
                        )[0];
                        rejectedOrder.status = "rejected";
                        rejectedOrder.rejection_reason = reason;
                        this.rejectedOrders.unshift(rejectedOrder);
                    }
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to reject order",
                });
            } finally {
                this.processingOrders = this.processingOrders.filter(
                    (id) => id !== order.id
                );
            }
        },

        async updateSinglePrice(item) {
            if (!item.newPrice || item.newPrice <= 0) return;

            this.updatingPrices.push(item.id);

            try {
                const response = await axios.put(
                    `/api/admin/shop-items/${item.id}/price`,
                    {
                        price: item.newPrice,
                    }
                );

                if (response.data.success) {
                    item.price = item.newPrice;
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: `Updated point price for "${item.name}"`,
                    });
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to update price",
                });
            } finally {
                this.updatingPrices = this.updatingPrices.filter(
                    (id) => id !== item.id
                );
            }
        },

        async updateBulkPrices() {
            if (
                !this.bulkPrice ||
                this.bulkPrice <= 0 ||
                this.selectedItems.length === 0
            )
                return;

            this.bulkUpdating = true;

            try {
                const response = await axios.put(
                    "/api/admin/shop-items/bulk-price",
                    {
                        item_ids: this.selectedItems,
                        price: this.bulkPrice,
                    }
                );

                if (response.data.success) {
                    // Update local data
                    this.items.forEach((item) => {
                        if (this.selectedItems.includes(item.id)) {
                            item.price = this.bulkPrice;
                            item.newPrice = this.bulkPrice;
                        }
                    });

                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: `Updated point prices for ${this.selectedItems.length} items`,
                    });

                    this.showBulkPricing = false;
                    this.selectedItems = [];
                    this.bulkPrice = null;
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to update bulk prices",
                });
            } finally {
                this.bulkUpdating = false;
            }
        },

        async togglePointShopStatus(item) {
            this.updatingPointShop.push(item.id);

            try {
                const response = await axios.put(
                    `/api/admin/shop-items/${item.id}/point-shop-status`,
                    {
                        is_active_in_point_shop: !item.is_active_in_point_shop,
                    }
                );

                if (response.data.success) {
                    item.is_active_in_point_shop =
                        response.data.is_active_in_point_shop;
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to update point shop status",
                });
            } finally {
                this.updatingPointShop = this.updatingPointShop.filter(
                    (id) => id !== item.id
                );
            }
        },

        async updateBulkPointShop() {
            if (!this.bulkPointShopStatus || this.selectedItems.length === 0)
                return;

            this.bulkPointShopUpdating = true;
            const isActive = this.bulkPointShopStatus === "true";

            try {
                const response = await axios.put(
                    "/api/admin/shop-items/bulk-point-shop",
                    {
                        item_ids: this.selectedItems,
                        is_active_in_point_shop: isActive,
                    }
                );

                if (response.data.success) {
                    // Update local data
                    this.items.forEach((item) => {
                        if (this.selectedItems.includes(item.id)) {
                            item.is_active_in_point_shop = isActive;
                        }
                    });

                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    this.showBulkPointShop = false;
                    this.selectedItems = [];
                    this.bulkPointShopStatus = "";
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to update point shop status",
                });
            } finally {
                this.bulkPointShopUpdating = false;
            }
        },

        toggleSelectAll() {
            if (this.allItemsSelected) {
                // Deselect all current page items
                this.paginatedItems.forEach((item) => {
                    const index = this.selectedItems.indexOf(item.id);
                    if (index > -1) {
                        this.selectedItems.splice(index, 1);
                    }
                });
            } else {
                // Select all current page items
                this.paginatedItems.forEach((item) => {
                    if (!this.selectedItems.includes(item.id)) {
                        this.selectedItems.push(item.id);
                    }
                });
            }
        },

        refreshData() {
            this.fetchData();
            if (this.activeTab === "orders") {
                this.fetchOrders();
            }
        },

        formatPoints(points) {
            return (points || 0).toLocaleString();
        },
    },

    async mounted() {
        // Check if user is admin
        const user = JSON.parse(localStorage.getItem("user") || "null");
        if (!user || user.role !== "admin") {
            this.$router.push("/dashboard");
            return;
        }

        await this.fetchData();
    },
};
</script>
