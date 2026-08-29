<template>
    <Layout>
        <div class="min-h-screen bg-gray-50 p-4">
            <div class="max-w-7xl mx-auto space-y-4">
                <!-- Filters and Search -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search -->
                        <div class="relative">
                            <i
                                class="pi pi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                            ></i>
                            <input
                                v-model="filters.search"
                                @input="debounceSearch"
                                type="text"
                                placeholder="Search shops..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Sort Pills -->
                        <div class="flex items-center gap-2 md:col-span-2">
                            <button
                                v-for="opt in sortOptions"
                                :key="opt.value"
                                @click="
                                    filters.sort = opt.value;
                                    applyFilters();
                                "
                                :class="
                                    filters.sort === opt.value
                                        ? 'bg-purple-500 text-white border-purple-500'
                                        : 'bg-white text-gray-600 border-gray-300'
                                "
                                class="flex-1 px-3 py-2 rounded-full border text-sm font-semibold transition-colors"
                            >
                                {{ opt.label }}
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

                <!-- Shops Grid/List -->
                <div v-else-if="shops.data && shops.data.length > 0">
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                    >
                        <div
                            v-for="shop in shops.data"
                            :key="shop.id"
                            @click="goToShop(shop)"
                            class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all cursor-pointer transform hover:-translate-y-1"
                        >
                            <!-- Shop Banner/Logo -->
                            <div
                                class="relative aspect-video bg-gradient-to-br from-purple-400 to-blue-500"
                            >
                                <img
                                    v-if="shop.banner_url"
                                    :src="shop.banner_url"
                                    :alt="shop.name"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    class="absolute inset-0 bg-black bg-opacity-20"
                                ></div>

                                <!-- Shop Logo -->
                                <div class="absolute bottom-4 left-4">
                                    <div
                                        class="w-12 h-12 rounded-full bg-white shadow-lg overflow-hidden border-2 border-white"
                                    >
                                        <img
                                            v-if="shop.logo_url"
                                            :src="shop.logo_url"
                                            :alt="shop.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="w-full h-full bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center"
                                        >
                                            <i
                                                class="pi pi-shop text-white text-sm"
                                            ></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Verification Badge -->
                                <div
                                    v-if="shop.is_verified"
                                    class="absolute top-4 right-4 bg-green-500 text-white px-2 py-1 rounded-full text-xs flex items-center gap-1"
                                >
                                    <i class="pi pi-check-circle"></i>
                                    Verified
                                </div>
                            </div>

                            <!-- Shop Details -->
                            <div class="p-6">
                                <div
                                    class="flex items-start justify-between mb-2"
                                >
                                    <h3
                                        class="text-lg font-bold text-gray-800 line-clamp-1"
                                    >
                                        {{ shop.name }}
                                    </h3>
                                    <div
                                        class="flex items-center gap-1 text-sm text-yellow-500"
                                    >
                                        <i class="pi pi-star-fill"></i>
                                        <span>{{
                                            shop.average_rating
                                                ? shop.average_rating.toFixed(1)
                                                : "New"
                                        }}</span>
                                    </div>
                                </div>

                                <p
                                    v-if="shop.description"
                                    class="text-sm text-gray-600 mb-4 line-clamp-2"
                                >
                                    {{ shop.description }}
                                </p>

                                <!-- Shop Stats -->
                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div>
                                        <div
                                            class="text-lg font-bold text-purple-600"
                                        >
                                            {{ shop.active_items_count }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Items
                                        </div>
                                    </div>
                                    <div>
                                        <div
                                            class="text-lg font-bold text-blue-600"
                                        >
                                            {{ shop.followers_count }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Followers
                                        </div>
                                    </div>
                                    <div>
                                        <div
                                            class="text-lg font-bold text-green-600"
                                        >
                                            {{ shop.reviews_count }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Reviews
                                        </div>
                                    </div>
                                </div>

                                <!-- Shop Owner -->
                                <div
                                    class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2"
                                >
                                    <div
                                        class="w-6 h-6 rounded-full bg-gray-300 overflow-hidden"
                                    >
                                        <div
                                            class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                                        >
                                            <i
                                                class="pi pi-user text-white text-xs"
                                            ></i>
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-600"
                                        >by {{ shop.owner.name }}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="shops.last_page > 1"
                        class="flex justify-center mt-8"
                    >
                        <div class="flex items-center gap-2">
                            <button
                                @click="goToPage(shops.current_page - 1)"
                                :disabled="shops.current_page <= 1"
                                class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                            >
                                <i class="pi pi-chevron-left"></i>
                            </button>

                            <span class="px-4 py-2 text-sm text-gray-600">
                                Page {{ shops.current_page }} of
                                {{ shops.last_page }}
                            </span>

                            <button
                                @click="goToPage(shops.current_page + 1)"
                                :disabled="
                                    shops.current_page >= shops.last_page
                                "
                                class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                            >
                                <i class="pi pi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="bg-white rounded-xl shadow-lg p-12 text-center"
                >
                    <i class="pi pi-shop text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-600 mb-2">
                        No Shops Found
                    </h3>
                    <p class="text-gray-500">
                        {{
                            filters.search
                                ? "Try adjusting your filters"
                                : "No shops are currently available"
                        }}
                    </p>
                    <button
                        v-if="filters.search"
                        @click="clearFilters"
                        class="mt-4 px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors"
                    >
                        Clear Filters
                    </button>
                </div>

                <!-- Create Shop Button (for shop owners) -->
                <div
                    v-if="
                        currentUser &&
                        currentUser.role === 'shop_owner' &&
                        !currentUser.has_shop
                    "
                    class="fixed bottom-6 right-6"
                >
                    <button
                        @click="showCreateShopDialog = true"
                        class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-full shadow-lg transition-colors"
                    >
                        <i class="pi pi-plus text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Create Shop Dialog -->
        <div
            v-if="showCreateShopDialog"
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
                            @click="showCreateShopDialog = false"
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
            shops: { data: [] },
            categories: [],
            currentUser: {},
            viewMode: "grid", // 'grid' or 'list'

            // Filters
            filters: {
                search: "",
                sort: "name",
            },

            sortOptions: [
                { label: "Name", value: "name" },
                { label: "Rating", value: "rating" },
                { label: "Followers", value: "followers" },
                { label: "Newest", value: "newest" },
            ],

            // Create shop
            showCreateShopDialog: false,
            creatingShop: false,
            newShop: {
                name: "",
                description: "",
            },

            // Debounce
            searchTimeout: null,
        };
    },

    methods: {
        async fetchShops(page = 1) {
            try {
                this.loading = true;
                const params = new URLSearchParams({
                    page: page,
                    ...this.filters,
                });

                // Fetch shops and user data separately for fresh data
                const [shopsResponse, userResponse] = await Promise.all([
                    axios.get(`/api/shops?${params}`),
                    axios
                        .get("/api/user/profile")
                        .catch(() => ({ data: { user: {} } })), // Handle unauthenticated users
                ]);

                if (shopsResponse.data.success) {
                    this.shops = shopsResponse.data.shops;

                    // Use fresh user data from profile endpoint
                    if (userResponse.data.success) {
                        this.currentUser = userResponse.data.user;
                    } else {
                        this.currentUser = {};
                    }
                }
            } catch (error) {
                console.error("Error fetching shops:", error);
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load shops",
                });

                // Set defaults for unauthenticated users
                this.currentUser = {};
            } finally {
                this.loading = false;
            }
        },

        goToShop(shop) {
            this.$router.push(`/shop/${shop.id}`);
        },

        goToPage(page) {
            if (page >= 1 && page <= this.shops.last_page) {
                this.fetchShops(page);
            }
        },

        applyFilters() {
            this.fetchShops(1);
        },

        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.applyFilters();
            }, 500);
        },

        clearFilters() {
            this.filters = {
                search: "",
                sort: "name",
            };
            this.applyFilters();
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

                    this.showCreateShopDialog = false;
                    this.newShop = { name: "", description: "" };

                    // Redirect to shop management or refresh
                    await this.fetchShops();
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

        formatPoints(points) {
            return (points || 0).toLocaleString();
        },
    },

    async mounted() {
        await this.fetchShops();
    },
};
</script>
