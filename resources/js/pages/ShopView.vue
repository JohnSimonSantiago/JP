<template>
    <Layout>
        <div class="min-h-screen bg-gray-50">
            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center py-12">
                <i class="pi pi-spin pi-spinner text-purple-500 text-3xl"></i>
            </div>

            <div v-else-if="shop" class="space-y-6">
                <!-- Shop Header -->
                <div class="relative">
                    <!-- Banner -->
                    <div
                        class="h-64 bg-gradient-to-r from-purple-600 to-blue-600 relative overflow-hidden"
                    >
                        <img
                            v-if="shop.banner_url"
                            :src="shop.banner_url"
                            :alt="shop.name"
                            class="w-full h-full object-cover"
                        />
                        <div
                            class="absolute inset-0 bg-black bg-opacity-30"
                        ></div>
                    </div>

                    <!-- Shop Info -->
                    <div class="max-w-6xl mx-auto px-6 relative">
                        <div
                            class="bg-white rounded-xl shadow-lg -mt-16 relative z-10 p-8"
                        >
                            <div
                                class="flex flex-col md:flex-row items-start gap-6"
                            >
                                <!-- Shop Logo -->
                                <div
                                    class="w-24 h-24 rounded-xl bg-gray-100 overflow-hidden flex-shrink-0 border-4 border-white shadow-lg"
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
                                            class="pi pi-shop text-white text-2xl"
                                        ></i>
                                    </div>
                                </div>

                                <!-- Shop Details -->
                                <div class="flex-1">
                                    <div
                                        class="flex flex-col md:flex-row md:items-start md:justify-between gap-4"
                                    >
                                        <div>
                                            <h1
                                                class="text-3xl font-bold text-gray-800 flex items-center gap-3"
                                            >
                                                {{ shop.name }}
                                                <i
                                                    v-if="shop.is_verified"
                                                    class="pi pi-check-circle text-green-500 text-xl"
                                                ></i>
                                            </h1>
                                            <p class="text-gray-600 mt-1">
                                                by {{ shop.owner.name }}
                                            </p>
                                            <p
                                                v-if="shop.description"
                                                class="text-gray-700 mt-3"
                                            >
                                                {{ shop.description }}
                                            </p>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex items-center gap-3">
                                            <button
                                                v-if="currentUser"
                                                @click="toggleFollow"
                                                :disabled="followLoading"
                                                :class="
                                                    isFollowing
                                                        ? 'bg-green-500 hover:bg-green-600 text-white'
                                                        : 'bg-purple-500 hover:bg-purple-600 text-white'
                                                "
                                                class="px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                                            >
                                                <i
                                                    v-if="followLoading"
                                                    class="pi pi-spin pi-spinner"
                                                ></i>
                                                <i
                                                    v-else
                                                    :class="
                                                        isFollowing
                                                            ? 'pi pi-check'
                                                            : 'pi pi-plus'
                                                    "
                                                ></i>
                                                {{
                                                    isFollowing
                                                        ? "Following"
                                                        : "Follow"
                                                }}
                                            </button>

                                            <button
                                                v-if="canReview"
                                                @click="showReviewDialog = true"
                                                class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition-colors flex items-center gap-2"
                                            >
                                                <i class="pi pi-star"></i>
                                                Review
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Shop Stats -->
                                    <div
                                        class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100"
                                    >
                                        <div class="text-center">
                                            <div
                                                class="text-2xl font-bold text-purple-600"
                                            >
                                                {{ shop.total_items }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Items
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div
                                                class="text-2xl font-bold text-blue-600"
                                            >
                                                {{ shop.follower_count }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Followers
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div
                                                class="text-2xl font-bold text-yellow-600 flex items-center justify-center gap-1"
                                            >
                                                {{
                                                    shop.average_rating
                                                        ? shop.average_rating.toFixed(
                                                              1
                                                          )
                                                        : "New"
                                                }}
                                                <i
                                                    class="pi pi-star-fill text-sm"
                                                ></i>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Rating
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div
                                                class="text-2xl font-bold text-green-600"
                                            >
                                                {{ shop.total_reviews }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                Reviews
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Points Display -->
                <div class="max-w-6xl mx-auto px-6">
                    <div
                        class="bg-purple-100 px-4 py-3 rounded-lg inline-flex items-center gap-2"
                    >
                        <i class="pi pi-wallet text-purple-600"></i>
                        <span class="text-lg font-bold text-purple-700">
                            {{ formatPoints(currentUser.points || 0) }} Points
                        </span>
                    </div>
                </div>

                <!-- Filters and Items -->
                <div class="max-w-6xl mx-auto px-6 space-y-6">
                    <!-- Filters -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div class="relative">
                                <i
                                    class="pi pi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                                ></i>
                                <input
                                    v-model="filters.search"
                                    @input="debounceSearch"
                                    type="text"
                                    placeholder="Search items..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                />
                            </div>

                            <!-- Category -->
                            <select
                                v-model="filters.category"
                                @change="applyFilters"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            >
                                <option value="">All Categories</option>
                                <option
                                    v-for="category in categories"
                                    :key="category.value"
                                    :value="category.value"
                                >
                                    {{ category.label }}
                                </option>
                            </select>

                            <!-- Sort -->
                            <select
                                v-model="filters.sort"
                                @change="applyFilters"
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            >
                                <option value="name">Name (A-Z)</option>
                                <option value="price_low">
                                    Price (Low to High)
                                </option>
                                <option value="price_high">
                                    Price (High to Low)
                                </option>
                                <option value="newest">Newest</option>
                                <option value="popular">Most Popular</option>
                            </select>

                            <!-- Back to Shops -->
                            <button
                                @click="$router.push('/shops')"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors flex items-center justify-center gap-2"
                            >
                                <i class="pi pi-arrow-left"></i>
                                Back to Shops
                            </button>
                        </div>
                    </div>

                    <!-- Items Grid -->
                    <div v-if="itemsLoading" class="flex justify-center py-12">
                        <i
                            class="pi pi-spin pi-spinner text-purple-500 text-2xl"
                        ></i>
                    </div>

                    <div
                        v-else-if="items.data && items.data.length > 0"
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
                    >
                        <div
                            v-for="item in items.data"
                            :key="item.id"
                            class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow"
                        >
                            <!-- Item Image -->
                            <div
                                class="aspect-square bg-gray-100 flex items-center justify-center"
                            >
                                <img
                                    v-if="item.image_url"
                                    :src="item.image_url"
                                    :alt="item.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="text-gray-400">
                                    <i class="pi pi-box text-5xl"></i>
                                </div>
                            </div>

                            <!-- Item Details -->
                            <div class="p-6">
                                <div
                                    class="flex items-start justify-between mb-2"
                                >
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
                                        <span class="text-gray-500"
                                            >Stock:</span
                                        >
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
                                    <div
                                        class="text-xl font-bold text-purple-600"
                                    >
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
                                        <i
                                            v-else
                                            class="pi pi-shopping-cart"
                                        ></i>
                                        {{ getButtonText(item) }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty Items State -->
                    <div
                        v-else
                        class="bg-white rounded-xl shadow-lg p-12 text-center"
                    >
                        <i class="pi pi-box text-gray-300 text-5xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-600 mb-2">
                            No Items Found
                        </h3>
                        <p class="text-gray-500">
                            {{
                                filters.search
                                    ? "Try adjusting your filters"
                                    : "This shop has no items yet"
                            }}
                        </p>
                    </div>

                    <!-- Items Pagination -->
                    <div v-if="items.last_page > 1" class="flex justify-center">
                        <div class="flex items-center gap-2">
                            <button
                                @click="goToItemsPage(items.current_page - 1)"
                                :disabled="items.current_page <= 1"
                                class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                            >
                                <i class="pi pi-chevron-left"></i>
                            </button>

                            <span class="px-4 py-2 text-sm text-gray-600">
                                Page {{ items.current_page }} of
                                {{ items.last_page }}
                            </span>

                            <button
                                @click="goToItemsPage(items.current_page + 1)"
                                :disabled="
                                    items.current_page >= items.last_page
                                "
                                class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                            >
                                <i class="pi pi-chevron-right"></i>
                            </button>
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
                                <p class="text-sm text-gray-600">
                                    from {{ shop.name }}
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

        <!-- Review Dialog -->
        <div
            v-if="showReviewDialog"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    Review {{ shop.name }}
                </h3>

                <form @submit.prevent="submitReview">
                    <div class="space-y-4">
                        <!-- Rating -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Rating *
                            </label>
                            <div class="flex items-center gap-1">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    @click="newReview.rating = star"
                                    :class="
                                        star <= newReview.rating
                                            ? 'text-yellow-500'
                                            : 'text-gray-300'
                                    "
                                    class="text-2xl hover:text-yellow-500 transition-colors"
                                >
                                    <i class="pi pi-star-fill"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Comment -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Comment (Optional)
                            </label>
                            <textarea
                                v-model="newReview.comment"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Share your experience..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button
                            type="button"
                            @click="showReviewDialog = false"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="!newReview.rating || submittingReview"
                            class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50"
                        >
                            <i
                                v-if="submittingReview"
                                class="pi pi-spin pi-spinner mr-2"
                            ></i>
                            Submit Review
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
            itemsLoading: false,
            shop: null,
            items: { data: [] },
            categories: [],
            currentUser: {},

            // Following
            isFollowing: false,
            followLoading: false,
            canReview: false,

            // Filters
            filters: {
                search: "",
                sort: "name",
            },

            // Purchase dialog
            showPurchaseDialog: false,
            selectedItem: null,
            processingPurchase: false,
            purchasingItem: null,

            // Review dialog
            showReviewDialog: false,
            submittingReview: false,
            newReview: {
                rating: 0,
                comment: "",
            },

            // Debounce
            searchTimeout: null,
        };
    },

    methods: {
        async fetchShop() {
            try {
                this.loading = true;
                const shopId = this.$route.params.id;
                const response = await axios.get(`/api/shops/${shopId}`);

                if (response.data.success) {
                    this.shop = response.data.shop;
                    this.items = response.data.items;
                    this.isFollowing = response.data.is_following;
                    this.canReview = response.data.can_review;
                    this.currentUser = response.data.user || {};
                } else {
                    this.$router.push("/shops");
                }
            } catch (error) {
                console.error("Error fetching shop:", error);
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load shop",
                });
                this.$router.push("/shops");
            } finally {
                this.loading = false;
            }
        },

        async fetchItems(page = 1) {
            try {
                this.itemsLoading = true;
                const shopId = this.$route.params.id;
                const params = new URLSearchParams({
                    page: page,
                    ...this.filters,
                });

                const response = await axios.get(
                    `/api/shops/${shopId}?${params}`
                );

                if (response.data.success) {
                    this.items = response.data.items;
                }
            } catch (error) {
                console.error("Error fetching items:", error);
            } finally {
                this.itemsLoading = false;
            }
        },

        async toggleFollow() {
            if (!this.currentUser) {
                this.$router.push("/login");
                return;
            }

            try {
                this.followLoading = true;
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/follow`
                );

                if (response.data.success) {
                    this.isFollowing = response.data.following;
                    this.shop.follower_count = response.data.follower_count;

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
                    detail: "Failed to update follow status",
                });
            } finally {
                this.followLoading = false;
            }
        },

        async submitReview() {
            try {
                this.submittingReview = true;
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/reviews`,
                    this.newReview
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    this.showReviewDialog = false;
                    this.newReview = { rating: 0, comment: "" };
                    this.canReview = false;
                    this.shop.average_rating = response.data.shop_rating;
                    this.shop.total_reviews++;
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to submit review",
                });
            } finally {
                this.submittingReview = false;
            }
        },

        buyItem(item) {
            if (!this.currentUser) {
                this.$router.push("/login");
                return;
            }

            this.selectedItem = item;
            this.showPurchaseDialog = true;
        },

        async confirmPurchase() {
            if (!this.selectedItem) return;

            try {
                this.processingPurchase = true;
                const response = await axios.post(
                    `/api/shop-items/${this.selectedItem.id}/purchase`
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    // Update user balance
                    this.currentUser.points = response.data.new_balance;
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

        goToItemsPage(page) {
            if (page >= 1 && page <= this.items.last_page) {
                this.fetchItems(page);
            }
        },

        applyFilters() {
            this.fetchItems(1);
        },

        debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.applyFilters();
            }, 500);
        },

        canBuyItem(item) {
            if (!this.currentUser) return false;

            return (
                item.is_active &&
                (item.stock === null || item.stock > 0) &&
                this.currentUser.points >= item.price
            );
        },

        getButtonText(item) {
            if (!this.currentUser) return "Login to Buy";
            if (!item.is_active) return "Unavailable";
            if (item.stock !== null && item.stock <= 0) return "Out of Stock";
            if (this.currentUser.points < item.price)
                return "Not Enough Points";
            return "Buy Now";
        },

        getCategoryIcon(category) {
            // Since we don't use categories, return default icon
            return "pi pi-box";
        },

        formatPoints(points) {
            return (points || 0).toLocaleString();
        },
    },

    async mounted() {
        await this.fetchShop();
    },

    watch: {
        $route() {
            if (this.$route.params.id) {
                this.fetchShop();
            }
        },
    },
};
</script>
