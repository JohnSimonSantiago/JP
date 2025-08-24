<template>
    <Layout>
        <div class="min-h-screen bg-gray-50">
            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center py-12">
                <i class="pi pi-spin pi-spinner text-blue-500 text-3xl"></i>
            </div>

            <div v-else-if="shop" class="space-y-6">
                <!-- Shop Header -->
                <div class="relative">
                    <!-- Banner -->
                    <div
                        class="h-64 bg-gradient-to-r from-blue-600 to-blue-600 relative overflow-hidden"
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
                                        class="w-full h-full bg-gradient-to-br from-blue-400 to-blue-500 flex items-center justify-center"
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
                                                        : 'bg-blue-500 hover:bg-blue-600 text-white'
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
                                                class="text-2xl font-bold text-blue-600"
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

                <!-- User Cash Balance Display -->
                <div class="max-w-6xl mx-auto px-6">
                    <div
                        class="bg-green-100 px-4 py-3 rounded-lg inline-flex items-center gap-2"
                    >
                        <i class="pi pi-dollar text-green-600"></i>
                        <span class="text-lg font-bold text-green-700">
                            ${{ formatCash(currentUser.cash || 0) }}
                        </span>
                    </div>
                </div>

                <!-- Main Content Tabs -->
                <div class="max-w-6xl mx-auto px-6">
                    <div class="bg-white rounded-xl shadow-lg">
                        <!-- Tab Navigation -->
                        <div class="border-b border-gray-200">
                            <nav class="flex">
                                <button
                                    @click="activeTab = 'shop'"
                                    :class="
                                        activeTab === 'shop'
                                            ? 'border-green-500 text-green-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    "
                                    class="px-6 py-4 border-b-2 font-medium text-sm whitespace-nowrap flex items-center gap-2"
                                >
                                    <i class="pi pi-shop"></i>
                                    Shop Items
                                </button>
                                <button
                                    @click="activeTab = 'purchases'"
                                    :class="
                                        activeTab === 'purchases'
                                            ? 'border-green-500 text-green-600'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    "
                                    class="px-6 py-4 border-b-2 font-medium text-sm whitespace-nowrap flex items-center gap-2"
                                >
                                    <i class="pi pi-history"></i>
                                    My Purchases
                                    <span
                                        v-if="pendingPurchasesCount > 0"
                                        class="bg-red-500 text-white text-xs rounded-full px-2 py-1"
                                    >
                                        {{ pendingPurchasesCount }}
                                    </span>
                                </button>
                            </nav>
                        </div>

                        <!-- Tab Content -->
                        <div class="p-6">
                            <!-- Shop Tab -->
                            <div v-if="activeTab === 'shop'" class="space-y-6">
                                <!-- Filters -->
                                <div
                                    class="grid grid-cols-1 md:grid-cols-4 gap-4"
                                >
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
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        />
                                    </div>

                                    <!-- Category -->
                                    <select
                                        v-model="filters.category"
                                        @change="applyFilters"
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    >
                                        <option value="name">Name (A-Z)</option>
                                        <option value="price_low">
                                            Price (Low to High)
                                        </option>
                                        <option value="price_high">
                                            Price (High to Low)
                                        </option>
                                        <option value="newest">Newest</option>
                                        <option value="popular">
                                            Most Popular
                                        </option>
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

                                <!-- Items Grid -->
                                <div
                                    v-if="itemsLoading"
                                    class="flex justify-center py-12"
                                >
                                    <i
                                        class="pi pi-spin pi-spinner text-green-500 text-2xl"
                                    ></i>
                                </div>

                                <div
                                    v-else-if="
                                        items.data && items.data.length > 0
                                    "
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
                                                <i
                                                    class="pi pi-box text-5xl"
                                                ></i>
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
                                            <div class="mb-3">
                                                <div
                                                    class="flex items-center justify-between text-sm"
                                                >
                                                    <span class="text-gray-500"
                                                        >Stock:</span
                                                    >
                                                    <span
                                                        v-if="
                                                            item.stock === null
                                                        "
                                                        class="text-green-600 font-medium"
                                                    >
                                                        Unlimited
                                                    </span>
                                                    <span
                                                        v-else
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
                                            <div
                                                class="flex items-center justify-between"
                                            >
                                                <div
                                                    class="text-xl font-bold text-green-600"
                                                >
                                                    ${{
                                                        formatCash(
                                                            item.cash_price
                                                        )
                                                    }}
                                                </div>
                                                <button
                                                    @click="buyItem(item)"
                                                    :disabled="
                                                        !canBuyItem(item) ||
                                                        purchasingItem ===
                                                            item.id
                                                    "
                                                    :class="
                                                        canBuyItem(item)
                                                            ? 'bg-green-500 hover:bg-green-600 text-white'
                                                            : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                                    "
                                                    class="px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                                                >
                                                    <i
                                                        v-if="
                                                            purchasingItem ===
                                                            item.id
                                                        "
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
                                <div v-else class="text-center py-12">
                                    <i
                                        class="pi pi-box text-gray-300 text-5xl mb-4"
                                    ></i>
                                    <h3
                                        class="text-lg font-medium text-gray-600 mb-2"
                                    >
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
                                <div
                                    v-if="items.last_page > 1"
                                    class="flex justify-center"
                                >
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="
                                                goToItemsPage(
                                                    items.current_page - 1
                                                )
                                            "
                                            :disabled="items.current_page <= 1"
                                            class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                                        >
                                            <i class="pi pi-chevron-left"></i>
                                        </button>

                                        <span
                                            class="px-4 py-2 text-sm text-gray-600"
                                        >
                                            Page {{ items.current_page }} of
                                            {{ items.last_page }}
                                        </span>

                                        <button
                                            @click="
                                                goToItemsPage(
                                                    items.current_page + 1
                                                )
                                            "
                                            :disabled="
                                                items.current_page >=
                                                items.last_page
                                            "
                                            class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                                        >
                                            <i class="pi pi-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Purchases Tab -->
                            <div
                                v-else-if="activeTab === 'purchases'"
                                class="space-y-6"
                            >
                                <!-- Purchase Filters -->
                                <div class="flex gap-4">
                                    <select
                                        v-model="purchaseFilters.status"
                                        @change="fetchPurchases"
                                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    >
                                        <option value="">All Purchases</option>
                                        <option value="pending">Pending</option>
                                        <option value="completed">
                                            Approved
                                        </option>
                                        <option value="rejected">
                                            Rejected
                                        </option>
                                    </select>
                                </div>

                                <!-- Purchase Loading -->
                                <div
                                    v-if="purchasesLoading"
                                    class="flex justify-center py-12"
                                >
                                    <i
                                        class="pi pi-spin pi-spinner text-green-500 text-2xl"
                                    ></i>
                                </div>

                                <!-- Purchase List -->
                                <div
                                    v-else-if="
                                        purchases.data &&
                                        purchases.data.length > 0
                                    "
                                    class="space-y-4"
                                >
                                    <div
                                        v-for="purchase in purchases.data"
                                        :key="purchase.id"
                                        class="bg-gray-50 rounded-xl p-6 border-l-4"
                                        :class="
                                            getPurchaseStatusColor(
                                                purchase.status
                                            )
                                        "
                                    >
                                        <div
                                            class="flex flex-col md:flex-row md:items-center justify-between gap-4"
                                        >
                                            <div class="flex-1">
                                                <div
                                                    class="flex items-start justify-between mb-2"
                                                >
                                                    <div>
                                                        <h3
                                                            class="text-lg font-bold text-gray-800"
                                                        >
                                                            {{
                                                                purchase
                                                                    .shop_item
                                                                    .name
                                                            }}
                                                        </h3>
                                                        <p
                                                            class="text-sm text-gray-600"
                                                        >
                                                            from
                                                            {{
                                                                purchase.shop
                                                                    ? purchase
                                                                          .shop
                                                                          .name
                                                                    : shop.name
                                                            }}
                                                        </p>
                                                    </div>
                                                    <div class="text-right">
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                            :class="
                                                                getPurchaseStatusBadge(
                                                                    purchase.status
                                                                )
                                                            "
                                                        >
                                                            {{
                                                                purchase.status
                                                                    .charAt(0)
                                                                    .toUpperCase() +
                                                                purchase.status.slice(
                                                                    1
                                                                )
                                                            }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div
                                                    class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm"
                                                >
                                                    <div>
                                                        <span
                                                            class="text-gray-500"
                                                            >Quantity:</span
                                                        >
                                                        <span
                                                            class="font-medium ml-1"
                                                            >{{
                                                                purchase.quantity
                                                            }}</span
                                                        >
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="text-gray-500"
                                                            >Total Cost:</span
                                                        >
                                                        <span
                                                            class="font-medium ml-1"
                                                            >${{
                                                                formatCash(
                                                                    purchase.price_paid *
                                                                        purchase.quantity
                                                                )
                                                            }}</span
                                                        >
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="text-gray-500"
                                                            >Date:</span
                                                        >
                                                        <span
                                                            class="font-medium ml-1"
                                                            >{{
                                                                formatDate(
                                                                    purchase.created_at
                                                                )
                                                            }}</span
                                                        >
                                                    </div>
                                                    <div
                                                        v-if="
                                                            purchase.rejection_reason
                                                        "
                                                    >
                                                        <span
                                                            class="text-gray-500"
                                                            >Reason:</span
                                                        >
                                                        <span
                                                            class="font-medium ml-1 text-red-600"
                                                            >{{
                                                                purchase.rejection_reason
                                                            }}</span
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty Purchases State -->
                                <div v-else class="text-center py-12">
                                    <i
                                        class="pi pi-history text-gray-300 text-5xl mb-4"
                                    ></i>
                                    <h3
                                        class="text-lg font-medium text-gray-600 mb-2"
                                    >
                                        No Purchases Found
                                    </h3>
                                    <p class="text-gray-500">
                                        You haven't made any purchases from this
                                        shop yet
                                    </p>
                                </div>

                                <!-- Purchase Pagination -->
                                <div
                                    v-if="purchases.last_page > 1"
                                    class="flex justify-center"
                                >
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="
                                                goToPurchasePage(
                                                    purchases.current_page - 1
                                                )
                                            "
                                            :disabled="
                                                purchases.current_page <= 1
                                            "
                                            class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                                        >
                                            <i class="pi pi-chevron-left"></i>
                                        </button>

                                        <span
                                            class="px-4 py-2 text-sm text-gray-600"
                                        >
                                            Page {{ purchases.current_page }} of
                                            {{ purchases.last_page }}
                                        </span>

                                        <button
                                            @click="
                                                goToPurchasePage(
                                                    purchases.current_page + 1
                                                )
                                            "
                                            :disabled="
                                                purchases.current_page >=
                                                purchases.last_page
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
                        class="w-16 h-16 mx-auto mb-4 rounded-full bg-green-100 flex items-center justify-center"
                    >
                        <i
                            class="pi pi-shopping-cart text-green-500 text-2xl"
                        ></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">
                        Confirm Purchase
                    </h3>
                    <p class="text-gray-600 mb-4">
                        Select quantity for
                        <strong>{{ selectedItem?.name }}</strong>
                    </p>

                    <!-- Item preview -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="font-medium text-gray-800">
                                    {{ selectedItem?.name }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    from {{ shop.name }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-green-600">
                                    ${{ formatCash(selectedItem?.cash_price) }}
                                </p>
                                <p class="text-sm text-gray-500">per item</p>
                            </div>
                        </div>

                        <!-- Quantity Selection -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label
                                    class="text-sm font-medium text-gray-700"
                                >
                                    Quantity:
                                </label>
                                <div class="flex items-center gap-2">
                                    <button
                                        @click="decreaseQuantity"
                                        :disabled="purchaseQuantity <= 1"
                                        class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                    >
                                        <i class="pi pi-minus text-sm"></i>
                                    </button>
                                    <input
                                        v-model.number="purchaseQuantity"
                                        type="number"
                                        :min="1"
                                        :max="getMaxQuantity(selectedItem)"
                                        class="w-16 px-2 py-1 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        @input="validateQuantity"
                                    />
                                    <button
                                        @click="increaseQuantity"
                                        :disabled="
                                            purchaseQuantity >=
                                            getMaxQuantity(selectedItem)
                                        "
                                        class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                                    >
                                        <i class="pi pi-plus text-sm"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Stock info -->
                            <div class="text-xs text-gray-500 text-left">
                                <span v-if="selectedItem?.stock === null">
                                    Unlimited stock available
                                </span>
                                <span v-else>
                                    {{ selectedItem?.stock }} items available
                                </span>
                            </div>

                            <!-- Total cost -->
                            <div
                                class="bg-green-50 rounded-lg p-3 border-l-4 border-green-400"
                            >
                                <div class="flex justify-between items-center">
                                    <span
                                        class="text-sm font-medium text-green-700"
                                    >
                                        Total Cost:
                                    </span>
                                    <span
                                        class="text-lg font-bold text-green-600"
                                    >
                                        ${{ formatCash(getTotalCost()) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Balance info -->
                    <div class="text-sm text-gray-600 mb-6">
                        <div class="flex justify-between">
                            <span>Current cash:</span>
                            <span>${{ formatCash(currentUser.cash) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>After purchase:</span>
                            <span
                                :class="
                                    getBalanceAfterPurchase() >= 0
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                "
                            >
                                ${{ formatCash(getBalanceAfterPurchase()) }}
                            </span>
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
                            :disabled="
                                processingPurchase || !canAffordPurchase()
                            "
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50"
                        >
                            <i
                                v-if="processingPurchase"
                                class="pi pi-spin pi-spinner mr-2"
                            ></i>
                            {{
                                !canAffordPurchase()
                                    ? "Not Enough Cash"
                                    : "Confirm Purchase"
                            }}
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
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
            purchasesLoading: false,
            shop: null,
            items: { data: [] },
            purchases: { data: [] },
            categories: [],
            currentUser: {},
            activeTab: "shop",
            pendingPurchasesCount: 0,

            // Following
            isFollowing: false,
            followLoading: false,
            canReview: false,

            // Filters
            filters: {
                search: "",
                sort: "name",
            },

            // Purchase filters
            purchaseFilters: {
                status: "",
            },

            // Purchase dialog
            showPurchaseDialog: false,
            selectedItem: null,
            processingPurchase: false,
            purchasingItem: null,
            purchaseQuantity: 1,

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

                // Fetch shop data and user data separately
                const [shopResponse, userResponse] = await Promise.all([
                    axios.get(`/api/shops/${shopId}`),
                    axios.get("/api/user/profile"), // Get fresh user data
                ]);

                if (shopResponse.data.success) {
                    this.shop = shopResponse.data.shop;
                    this.items = shopResponse.data.items;
                    this.isFollowing = shopResponse.data.is_following;
                    this.canReview = shopResponse.data.can_review;

                    // Use fresh user data from profile endpoint
                    if (userResponse.data.success) {
                        this.currentUser = userResponse.data.user;
                    } else {
                        this.currentUser = {};
                    }
                } else {
                    this.$router.push("/shops");
                }

                // Fetch purchases to get pending count
                await this.fetchPurchases();
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

        async fetchPurchases(page = 1) {
            try {
                this.purchasesLoading = true;
                const params = new URLSearchParams({
                    page: page,
                    shop_id: this.shop?.id, // Filter by current shop
                    ...this.purchaseFilters,
                });

                const response = await axios.get(`/api/my-purchases?${params}`);

                if (response.data.success) {
                    this.purchases = response.data.purchases;
                    // Count pending purchases for this shop
                    this.pendingPurchasesCount = this.purchases.data.filter(
                        (p) =>
                            p.status === "pending" &&
                            p.shop_id === this.shop?.id
                    ).length;
                }
            } catch (error) {
                console.error("Error fetching purchases:", error);
            } finally {
                this.purchasesLoading = false;
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
            this.purchaseQuantity = 1;
            this.showPurchaseDialog = true;
        },

        async confirmPurchase() {
            if (!this.selectedItem) return;

            try {
                this.processingPurchase = true;
                const response = await axios.post(
                    `/api/shop-items/${this.selectedItem.id}/purchase`,
                    {
                        quantity: this.purchaseQuantity,
                    }
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    // Update user cash balance
                    this.currentUser.cash = response.data.new_balance;
                    this.cancelPurchase();

                    // Refresh shop data to update stock counts and fetch purchases
                    await Promise.all([
                        this.fetchShop(),
                        this.fetchPurchases(),
                    ]);

                    // Switch to purchases tab to show the new purchase
                    this.activeTab = "purchases";
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
            this.purchaseQuantity = 1;
        },

        // Quantity management methods
        increaseQuantity() {
            const max = this.getMaxQuantity(this.selectedItem);
            if (this.purchaseQuantity < max) {
                this.purchaseQuantity++;
            }
        },

        decreaseQuantity() {
            if (this.purchaseQuantity > 1) {
                this.purchaseQuantity--;
            }
        },

        validateQuantity() {
            const max = this.getMaxQuantity(this.selectedItem);
            if (this.purchaseQuantity > max) {
                this.purchaseQuantity = max;
            } else if (this.purchaseQuantity < 1) {
                this.purchaseQuantity = 1;
            }
        },

        getMaxQuantity(item) {
            if (!item) return 1;

            if (item.stock === null) {
                const affordableQuantity = Math.floor(
                    this.currentUser.cash / item.cash_price
                );
                return Math.min(affordableQuantity, 10);
            }

            const affordableQuantity = Math.floor(
                this.currentUser.cash / item.cash_price
            );
            return Math.min(item.stock, affordableQuantity, 10);
        },

        getTotalCost() {
            if (!this.selectedItem) return 0;
            return this.selectedItem.cash_price * this.purchaseQuantity;
        },

        getBalanceAfterPurchase() {
            return this.currentUser.cash - this.getTotalCost();
        },

        canAffordPurchase() {
            return this.getBalanceAfterPurchase() >= 0;
        },

        goToItemsPage(page) {
            if (page >= 1 && page <= this.items.last_page) {
                this.fetchItems(page);
            }
        },

        goToPurchasePage(page) {
            if (page >= 1 && page <= this.purchases.last_page) {
                this.fetchPurchases(page);
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
                this.currentUser.cash >= item.cash_price
            );
        },

        getButtonText(item) {
            if (!this.currentUser) return "Login to Buy";
            if (!item.is_active) return "Unavailable";
            if (item.stock !== null && item.stock <= 0) return "Out of Stock";
            if (this.currentUser.cash < item.cash_price)
                return "Not Enough Cash";
            return "Buy Now";
        },

        getPurchaseStatusColor(status) {
            switch (status) {
                case "pending":
                    return "border-yellow-400";
                case "completed":
                    return "border-green-400";
                case "rejected":
                    return "border-red-400";
                default:
                    return "border-gray-400";
            }
        },

        getPurchaseStatusBadge(status) {
            switch (status) {
                case "pending":
                    return "bg-yellow-100 text-yellow-800";
                case "completed":
                    return "bg-green-100 text-green-800";
                case "rejected":
                    return "bg-red-100 text-red-800";
                default:
                    return "bg-gray-100 text-gray-800";
            }
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        },

        formatCash(amount) {
            return parseFloat(amount || 0).toFixed(2);
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

        activeTab(newTab) {
            if (newTab === "purchases") {
                this.fetchPurchases();
            }
        },
    },
};
</script>
