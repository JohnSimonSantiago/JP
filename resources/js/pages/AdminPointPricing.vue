<template>
    <Layout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        Point Pricing Management
                    </h1>
                    <p class="text-gray-600 mt-2">
                        Manage point prices across all shops
                    </p>
                </div>

                <!-- Search and Filters -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                                <option value="active">Active Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center py-12">
                    <i class="pi pi-spin pi-spinner text-blue-500 text-3xl"></i>
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
                                    @click="refreshData"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                                >
                                    <i class="pi pi-refresh mr-2"></i>
                                    Refresh
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
                                        Status
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
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
                                            {{ item.shop?.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ item.shop?.owner?.name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-medium text-green-600"
                                        >
                                            ${{
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
                                                    ? formatPoints(item.price) +
                                                      " points"
                                                    : "Not set"
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="flex items-center space-x-2"
                                        >
                                            <input
                                                v-model.number="item.newPrice"
                                                type="number"
                                                min="0"
                                                placeholder="0"
                                                class="w-24 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                            />
                                            <span class="text-xs text-gray-500"
                                                >points</span
                                            >
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
                                                updatingPrices.includes(item.id)
                                            "
                                            class="text-blue-600 hover:text-blue-900 disabled:text-gray-400 disabled:cursor-not-allowed"
                                        >
                                            <i
                                                v-if="
                                                    updatingPrices.includes(
                                                        item.id
                                                    )
                                                "
                                                class="pi pi-spin pi-spinner"
                                            ></i>
                                            <i v-else class="pi pi-check"></i>
                                            Update
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing
                                {{ (currentPage - 1) * itemsPerPage + 1 }} to
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
            items: [],
            shops: [],

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

            // Individual updates
            updatingPrices: [],
        };
    },

    computed: {
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
