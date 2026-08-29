<template>
    <Layout>
        <div class="min-h-screen bg-gray-50">
            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center py-12">
                <i class="pi pi-spin pi-spinner text-green-500 text-3xl"></i>
            </div>

            <div v-else-if="shop" class="max-w-4xl mx-auto">
                <!-- Banner + Shop Header (mobile-style, flowing) -->
                <div class="relative">
                    <div
                        class="h-40 md:h-52 bg-gradient-to-r from-green-500 to-green-600 relative"
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

                        <!-- Back button -->
                        <button
                            @click="$router.push('/shops')"
                            class="absolute top-4 left-4 bg-black bg-opacity-30 hover:bg-opacity-50 text-white rounded-full p-2 transition-colors"
                        >
                            <i class="pi pi-arrow-left"></i>
                        </button>
                    </div>

                    <!-- Shop info row -->
                    <div class="px-4 md:px-6 relative z-10">
                        <div class="flex items-end gap-4 -mt-10">
                            <!-- Logo -->
                            <div
                                class="w-20 h-20 rounded-2xl bg-gray-100 overflow-hidden flex-shrink-0 border-4 border-white shadow-lg"
                            >
                                <img
                                    v-if="shop.logo_url"
                                    :src="shop.logo_url"
                                    :alt="shop.name"
                                    class="w-full h-full object-contain"
                                />
                                <div
                                    v-else
                                    class="w-full h-full bg-gradient-to-br from-green-400 to-green-500 flex items-center justify-center"
                                >
                                    <i
                                        class="pi pi-shop text-white text-2xl"
                                    ></i>
                                </div>
                            </div>

                            <!-- Action buttons (aligned to logo bottom) -->
                            <div class="flex-1 flex justify-end gap-2 pb-1">
                                <button
                                    v-if="currentUser"
                                    @click="toggleFollow"
                                    :disabled="followLoading"
                                    :class="
                                        isFollowing
                                            ? 'bg-green-500 hover:bg-green-600 text-white'
                                            : 'bg-white border border-green-500 text-green-600 hover:bg-green-50'
                                    "
                                    class="px-4 py-2 rounded-full text-sm font-semibold transition-colors flex items-center gap-2"
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
                                    {{ isFollowing ? "Following" : "Follow" }}
                                </button>

                                <button
                                    v-if="canReview"
                                    @click="showReviewDialog = true"
                                    class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-full text-sm font-semibold transition-colors flex items-center gap-2"
                                >
                                    <i class="pi pi-star"></i>
                                    Review
                                </button>
                            </div>
                        </div>

                        <!-- Name + description -->
                        <div class="mt-3">
                            <h1
                                class="text-2xl font-bold text-gray-800 flex items-center gap-2"
                            >
                                {{ shop.name }}
                                <i
                                    v-if="shop.is_verified"
                                    class="pi pi-check-circle text-green-500 text-lg"
                                ></i>
                            </h1>
                            <p class="text-sm text-gray-500 mt-0.5">
                                by {{ shop.owner.name }}
                            </p>
                            <p
                                v-if="shop.description"
                                class="text-gray-700 text-sm mt-2"
                            >
                                {{ shop.description }}
                            </p>
                        </div>

                        <!-- Stats row (compact chips like mobile) -->
                        <div class="flex flex-wrap gap-2 mt-4">
                            <div
                                class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-3 py-1.5"
                            >
                                <i class="pi pi-box text-green-500 text-xs"></i>
                                <span
                                    class="text-sm font-semibold text-gray-700"
                                >
                                    {{ shop.total_items }}
                                </span>
                                <span class="text-xs text-gray-400">items</span>
                            </div>
                            <div
                                class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-3 py-1.5"
                            >
                                <i
                                    class="pi pi-users text-blue-500 text-xs"
                                ></i>
                                <span
                                    class="text-sm font-semibold text-gray-700"
                                >
                                    {{ shop.follower_count }}
                                </span>
                                <span class="text-xs text-gray-400"
                                    >followers</span
                                >
                            </div>
                            <div
                                class="flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-3 py-1.5"
                            >
                                <i
                                    class="pi pi-star-fill text-yellow-500 text-xs"
                                ></i>
                                <span
                                    class="text-sm font-semibold text-gray-700"
                                >
                                    {{
                                        shop.average_rating
                                            ? shop.average_rating.toFixed(1)
                                            : "New"
                                    }}
                                </span>
                                <span class="text-xs text-gray-400">
                                    ({{ shop.total_reviews }})
                                </span>
                            </div>
                        </div>

                        <!-- Cash balance -->
                        <div
                            class="mt-4 bg-green-50 border border-green-200 rounded-lg px-4 py-2 inline-flex items-center gap-2"
                        >
                            <i class="pi pi-wallet text-green-600 text-sm"></i>
                            <span class="text-sm font-bold text-green-700">
                                ₱{{ formatCash(currentUser.cash || 0) }}
                                available
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="px-4 md:px-6 mt-6">
                    <div class="flex border-b border-gray-200">
                        <button
                            @click="activeTab = 'shop'"
                            :class="
                                activeTab === 'shop'
                                    ? 'border-green-500 text-green-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'
                            "
                            class="flex-1 md:flex-none md:px-8 py-3 border-b-2 font-medium text-sm flex items-center justify-center gap-2"
                        >
                            <i class="pi pi-shop"></i>
                            Items
                        </button>
                        <button
                            @click="activeTab = 'purchases'"
                            :class="
                                activeTab === 'purchases'
                                    ? 'border-green-500 text-green-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700'
                            "
                            class="flex-1 md:flex-none md:px-8 py-3 border-b-2 font-medium text-sm flex items-center justify-center gap-2"
                        >
                            <i class="pi pi-history"></i>
                            My Orders
                            <span
                                v-if="pendingPurchasesCount > 0"
                                class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5"
                            >
                                {{ pendingPurchasesCount }}
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="px-4 md:px-6 py-6">
                    <!-- Shop Tab -->
                    <div v-if="activeTab === 'shop'" class="space-y-4">
                        <!-- Search -->
                        <div class="relative">
                            <i
                                class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"
                            ></i>
                            <input
                                v-model="filters.search"
                                @input="debounceSearch"
                                type="text"
                                placeholder="Search items..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                            />
                        </div>

                        <!-- Sort pills -->
                        <div class="flex gap-2 overflow-x-auto pb-1">
                            <button
                                v-for="opt in sortOptions"
                                :key="opt.value"
                                @click="
                                    filters.sort = opt.value;
                                    applyFilters();
                                "
                                :class="
                                    filters.sort === opt.value
                                        ? 'bg-gray-800 text-white border-gray-800'
                                        : 'bg-white text-gray-600 border-gray-300'
                                "
                                class="flex-shrink-0 px-4 py-1.5 rounded-full border text-sm font-medium transition-colors"
                            >
                                {{ opt.label }}
                            </button>
                        </div>

                        <!-- Items grid (compact mobile-style cards, desktop width) -->
                        <div
                            v-if="itemsLoading"
                            class="flex justify-center py-12"
                        >
                            <i
                                class="pi pi-spin pi-spinner text-green-500 text-2xl"
                            ></i>
                        </div>

                        <div
                            v-else-if="items.data && items.data.length > 0"
                            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3"
                        >
                            <div
                                v-for="item in items.data"
                                :key="item.id"
                                @click="openDetail(item)"
                                class="bg-white rounded-2xl border border-gray-200 overflow-hidden flex flex-col cursor-pointer hover:shadow-md transition-shadow"
                            >
                                <!-- Image -->
                                <div
                                    class="aspect-square bg-gray-100 flex items-center justify-center relative"
                                >
                                    <img
                                        v-if="item.image_url"
                                        :src="item.image_url"
                                        :alt="item.name"
                                        class="w-full h-full object-cover"
                                    />
                                    <i
                                        v-else
                                        class="pi pi-box text-gray-300 text-4xl"
                                    ></i>
                                    <div
                                        v-if="
                                            item.stock !== null &&
                                            item.stock <= 0
                                        "
                                        class="absolute bottom-0 inset-x-0 bg-black bg-opacity-50 text-center py-1"
                                    >
                                        <span
                                            class="text-red-300 text-xs font-bold"
                                        >
                                            Out of Stock
                                        </span>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div class="p-3 flex flex-col flex-1">
                                    <h3
                                        class="text-sm font-bold text-gray-800 line-clamp-2"
                                    >
                                        {{ item.name }}
                                    </h3>
                                    <p
                                        v-if="item.description"
                                        class="text-xs text-gray-500 mt-1 line-clamp-2"
                                    >
                                        {{ item.description }}
                                    </p>

                                    <!-- Price -->
                                    <div
                                        class="mt-2 flex items-baseline gap-1.5 flex-wrap"
                                    >
                                        <span
                                            class="text-base font-bold text-green-600"
                                        >
                                            ₱{{
                                                formatCash(
                                                    item.has_discount
                                                        ? item.discounted_price
                                                        : item.cash_price,
                                                )
                                            }}
                                        </span>
                                        <span
                                            v-if="item.has_discount"
                                            class="text-xs text-gray-400 line-through"
                                        >
                                            ₱{{ formatCash(item.cash_price) }}
                                        </span>
                                    </div>

                                    <!-- Stock line -->
                                    <p class="text-xs text-gray-400 mt-1">
                                        <span v-if="item.stock === null"
                                            >Unlimited stock</span
                                        >
                                        <span v-else-if="item.stock > 0"
                                            >{{ item.stock }} left</span
                                        >
                                        <span v-else class="text-red-500"
                                            >Out of stock</span
                                        >
                                    </p>

                                    <!-- Buy button (stop click so it doesn't open detail) -->
                                    <button
                                        @click.stop="buyItem(item)"
                                        :disabled="
                                            !canBuyItem(item) ||
                                            purchasingItem === item.id
                                        "
                                        :class="
                                            canBuyItem(item)
                                                ? 'bg-green-500 hover:bg-green-600 text-white'
                                                : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                        "
                                        class="mt-3 w-full py-2 rounded-lg text-sm font-semibold transition-colors flex items-center justify-center gap-1.5"
                                    >
                                        <i
                                            v-if="purchasingItem === item.id"
                                            class="pi pi-spin pi-spinner text-xs"
                                        ></i>
                                        <i
                                            v-else
                                            class="pi pi-shopping-cart text-xs"
                                        ></i>
                                        {{ getButtonText(item) }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty items -->
                        <div v-else class="text-center py-12">
                            <i
                                class="pi pi-box text-gray-300 text-5xl mb-3"
                            ></i>
                            <h3
                                class="text-base font-medium text-gray-600 mb-1"
                            >
                                No items found
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{
                                    filters.search
                                        ? "Try adjusting your filters"
                                        : "This shop has no items yet"
                                }}
                            </p>
                        </div>

                        <!-- Items pagination -->
                        <div
                            v-if="items.last_page > 1"
                            class="flex justify-center pt-2"
                        >
                            <div class="flex items-center gap-2">
                                <button
                                    @click="
                                        goToItemsPage(items.current_page - 1)
                                    "
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
                                    @click="
                                        goToItemsPage(items.current_page + 1)
                                    "
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

                    <!-- Purchases Tab -->
                    <div
                        v-else-if="activeTab === 'purchases'"
                        class="space-y-3"
                    >
                        <select
                            v-model="purchaseFilters.status"
                            @change="fetchPurchases"
                            class="px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                        >
                            <option value="">All Orders</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>

                        <div
                            v-if="purchasesLoading"
                            class="flex justify-center py-12"
                        >
                            <i
                                class="pi pi-spin pi-spinner text-green-500 text-2xl"
                            ></i>
                        </div>

                        <div
                            v-else-if="
                                purchases.data && purchases.data.length > 0
                            "
                            class="space-y-3"
                        >
                            <div
                                v-for="purchase in purchases.data"
                                :key="purchase.id"
                                class="bg-white rounded-xl p-4 border-l-4"
                                :class="getPurchaseStatusColor(purchase.status)"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="flex-1">
                                        <h3
                                            class="text-base font-bold text-gray-800"
                                        >
                                            {{ purchase.shop_item.name }}
                                        </h3>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Qty: {{ purchase.quantity }} · ₱{{
                                                formatCash(
                                                    purchase.price_paid *
                                                        purchase.quantity,
                                                )
                                            }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{
                                                formatDate(purchase.created_at)
                                            }}
                                        </p>
                                        <p
                                            v-if="purchase.rejection_reason"
                                            class="text-xs text-red-600 mt-1"
                                        >
                                            Reason:
                                            {{ purchase.rejection_reason }}
                                        </p>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0"
                                        :class="
                                            getPurchaseStatusBadge(
                                                purchase.status,
                                            )
                                        "
                                    >
                                        {{
                                            purchase.status
                                                .charAt(0)
                                                .toUpperCase() +
                                            purchase.status.slice(1)
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Empty purchases -->
                        <div v-else class="text-center py-12">
                            <i
                                class="pi pi-history text-gray-300 text-5xl mb-3"
                            ></i>
                            <h3
                                class="text-base font-medium text-gray-600 mb-1"
                            >
                                No orders yet
                            </h3>
                            <p class="text-sm text-gray-500">
                                You haven't ordered from this shop yet
                            </p>
                        </div>

                        <!-- Purchase pagination -->
                        <div
                            v-if="purchases.last_page > 1"
                            class="flex justify-center pt-2"
                        >
                            <div class="flex items-center gap-2">
                                <button
                                    @click="
                                        goToPurchasePage(
                                            purchases.current_page - 1,
                                        )
                                    "
                                    :disabled="purchases.current_page <= 1"
                                    class="px-3 py-2 rounded-lg border border-gray-300 disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                                >
                                    <i class="pi pi-chevron-left"></i>
                                </button>
                                <span class="px-4 py-2 text-sm text-gray-600">
                                    Page {{ purchases.current_page }} of
                                    {{ purchases.last_page }}
                                </span>
                                <button
                                    @click="
                                        goToPurchasePage(
                                            purchases.current_page + 1,
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

        <!-- Item Detail Bottom Sheet -->
        <div
            v-if="showDetailDialog"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-end md:items-center justify-center z-50"
            @click.self="showDetailDialog = false"
        >
            <div
                class="bg-white rounded-t-3xl md:rounded-3xl w-full md:max-w-md max-h-[85vh] overflow-y-auto p-6 pb-8"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">
                        Item Details
                    </h3>
                    <button @click="showDetailDialog = false">
                        <i class="pi pi-times text-gray-400"></i>
                    </button>
                </div>

                <div v-if="detailItem">
                    <!-- Big image -->
                    <div
                        class="aspect-square md:aspect-video bg-gray-100 rounded-2xl overflow-hidden flex items-center justify-center mb-4"
                    >
                        <img
                            v-if="detailItem.image_url"
                            :src="detailItem.image_url"
                            :alt="detailItem.name"
                            class="w-full h-full object-cover"
                        />
                        <i v-else class="pi pi-box text-gray-300 text-6xl"></i>
                    </div>

                    <!-- Name -->
                    <h2 class="text-xl font-bold text-gray-800">
                        {{ detailItem.name }}
                    </h2>

                    <!-- Price -->
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-green-600">
                            ₱{{
                                formatCash(
                                    detailItem.has_discount
                                        ? detailItem.discounted_price
                                        : detailItem.cash_price,
                                )
                            }}
                        </span>
                        <span
                            v-if="detailItem.has_discount"
                            class="text-sm text-gray-400 line-through"
                        >
                            ₱{{ formatCash(detailItem.cash_price) }}
                        </span>
                    </div>

                    <!-- Stock -->
                    <p class="text-sm text-gray-500 mt-1">
                        <span v-if="detailItem.stock === null"
                            >Unlimited stock available</span
                        >
                        <span v-else-if="detailItem.stock > 0"
                            >{{ detailItem.stock }} in stock</span
                        >
                        <span v-else class="text-red-500 font-medium"
                            >Out of stock</span
                        >
                    </p>

                    <!-- Full description -->
                    <p
                        v-if="detailItem.description"
                        class="text-sm text-gray-700 mt-4 leading-relaxed whitespace-pre-line"
                    >
                        {{ detailItem.description }}
                    </p>

                    <!-- Buy button -->
                    <button
                        @click="buyFromDetail"
                        :disabled="!canBuyItem(detailItem)"
                        :class="
                            canBuyItem(detailItem)
                                ? 'bg-green-500 hover:bg-green-600 text-white'
                                : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                        "
                        class="mt-6 w-full py-3.5 rounded-xl font-semibold transition-colors flex items-center justify-center gap-2"
                    >
                        <i class="pi pi-shopping-cart"></i>
                        {{ getButtonText(detailItem) }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Purchase Bottom Sheet -->
        <div
            v-if="showPurchaseDialog"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-end md:items-center justify-center z-50"
            @click.self="cancelPurchase"
        >
            <div
                class="bg-white rounded-t-3xl md:rounded-3xl w-full md:max-w-md p-6 pb-8"
            >
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-gray-800">
                        Confirm Purchase
                    </h3>
                    <button @click="cancelPurchase">
                        <i class="pi pi-times text-gray-400"></i>
                    </button>
                </div>

                <!-- Item row -->
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <p class="font-semibold text-gray-800">
                            {{ selectedItem?.name }}
                        </p>
                        <p class="text-sm text-gray-500">
                            from {{ shop.name }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p
                            v-if="selectedItem?.has_discount"
                            class="text-xs text-gray-400 line-through"
                        >
                            ₱{{ formatCash(selectedItem?.cash_price) }}
                        </p>
                        <p class="text-lg font-bold text-green-600">
                            ₱{{ formatCash(effectivePrice(selectedItem)) }}
                        </p>
                        <p class="text-xs text-gray-400">per item</p>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="flex items-center justify-between mb-4">
                    <span class="text-sm font-semibold text-gray-700"
                        >Quantity</span
                    >
                    <div class="flex items-center gap-3">
                        <button
                            @click="decreaseQuantity"
                            :disabled="purchaseQuantity <= 1"
                            class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 disabled:opacity-50 flex items-center justify-center"
                        >
                            <i class="pi pi-minus text-sm text-green-600"></i>
                        </button>
                        <input
                            v-model.number="purchaseQuantity"
                            type="number"
                            :min="1"
                            :max="getMaxQuantity(selectedItem)"
                            @input="validateQuantity"
                            class="w-14 text-center text-lg font-bold text-gray-800 border-none focus:ring-0"
                        />
                        <button
                            @click="increaseQuantity"
                            :disabled="
                                purchaseQuantity >= getMaxQuantity(selectedItem)
                            "
                            class="w-9 h-9 rounded-xl bg-gray-100 hover:bg-gray-200 disabled:opacity-50 flex items-center justify-center"
                        >
                            <i class="pi pi-plus text-sm text-green-600"></i>
                        </button>
                    </div>
                </div>

                <!-- Totals -->
                <div
                    class="bg-gray-50 rounded-xl p-4 space-y-2 mb-5 border border-gray-200"
                >
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Total cost</span>
                        <span class="font-bold text-green-600">
                            ₱{{ formatCash(getTotalCost()) }}
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">After purchase</span>
                        <span
                            :class="
                                getBalanceAfterPurchase() >= 0
                                    ? 'text-gray-700'
                                    : 'text-red-600'
                            "
                            class="font-semibold"
                        >
                            ₱{{ formatCash(getBalanceAfterPurchase()) }}
                        </span>
                    </div>
                </div>

                <button
                    @click="confirmPurchase"
                    :disabled="processingPurchase || !canAffordPurchase()"
                    class="w-full bg-green-500 hover:bg-green-600 text-white py-3.5 rounded-xl font-semibold transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
                >
                    <i
                        v-if="processingPurchase"
                        class="pi pi-spin pi-spinner"
                    ></i>
                    {{
                        !canAffordPurchase() ? "Not Enough Cash" : "Place Order"
                    }}
                </button>
            </div>
        </div>

        <!-- Review Bottom Sheet -->
        <div
            v-if="showReviewDialog"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-end md:items-center justify-center z-50"
            @click.self="showReviewDialog = false"
        >
            <div
                class="bg-white rounded-t-3xl md:rounded-3xl w-full md:max-w-md p-6 pb-8"
            >
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-gray-800">
                        Review {{ shop.name }}
                    </h3>
                    <button @click="showReviewDialog = false">
                        <i class="pi pi-times text-gray-400"></i>
                    </button>
                </div>

                <form @submit.prevent="submitReview">
                    <div class="mb-4">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Rating
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
                                class="text-3xl hover:text-yellow-500 transition-colors"
                            >
                                <i class="pi pi-star-fill"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Comment (optional)
                        </label>
                        <textarea
                            v-model="newReview.comment"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                            placeholder="Share your experience..."
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="!newReview.rating || submittingReview"
                        class="w-full bg-yellow-500 hover:bg-yellow-600 text-white py-3.5 rounded-xl font-semibold transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
                    >
                        <i
                            v-if="submittingReview"
                            class="pi pi-spin pi-spinner"
                        ></i>
                        Submit Review
                    </button>
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

            sortOptions: [
                { label: "Name", value: "name" },
                { label: "Price ↑", value: "price_low" },
                { label: "Price ↓", value: "price_high" },
                { label: "Newest", value: "newest" },
                { label: "Popular", value: "popular" },
            ],

            // Purchase filters
            purchaseFilters: {
                status: "",
            },

            // Item detail dialog
            showDetailDialog: false,
            detailItem: null,

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

                const [shopResponse, userResponse] = await Promise.all([
                    axios.get(`/api/shops/${shopId}`),
                    axios.get("/api/user/profile"),
                ]);

                if (shopResponse.data.success) {
                    this.shop = shopResponse.data.shop;
                    this.items = shopResponse.data.items;
                    this.isFollowing = shopResponse.data.is_following;
                    this.canReview = shopResponse.data.can_review;

                    if (userResponse.data.success) {
                        this.currentUser = userResponse.data.user;
                    } else {
                        this.currentUser = {};
                    }
                } else {
                    this.$router.push("/shops");
                }

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
                    shop_id: this.shop?.id,
                    ...this.purchaseFilters,
                });

                const response = await axios.get(`/api/my-purchases?${params}`);

                if (response.data.success) {
                    this.purchases = response.data.purchases;
                    this.pendingPurchasesCount = this.purchases.data.filter(
                        (p) =>
                            p.status === "pending" &&
                            p.shop_id === this.shop?.id,
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
                    `/api/shops/${shopId}?${params}`,
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

        openDetail(item) {
            this.detailItem = item;
            this.showDetailDialog = true;
        },

        buyFromDetail() {
            const item = this.detailItem;
            this.showDetailDialog = false;
            this.buyItem(item);
        },

        async toggleFollow() {
            if (!this.currentUser) {
                this.$router.push("/login");
                return;
            }

            try {
                this.followLoading = true;
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/follow`,
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
                    this.newReview,
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
                    },
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    this.currentUser.cash = response.data.new_balance;
                    this.cancelPurchase();

                    await Promise.all([
                        this.fetchShop(),
                        this.fetchPurchases(),
                    ]);

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

        effectivePrice(item) {
            if (!item) return 0;
            return item.has_discount
                ? parseFloat(item.discounted_price)
                : parseFloat(item.cash_price);
        },

        getMaxQuantity(item) {
            if (!item) return 1;
            const unit = this.effectivePrice(item) || 1;

            if (item.stock === null) {
                const affordableQuantity = Math.floor(
                    this.currentUser.cash / unit,
                );
                return Math.min(affordableQuantity, 10);
            }

            const affordableQuantity = Math.floor(this.currentUser.cash / unit);
            return Math.min(item.stock, affordableQuantity, 10);
        },

        getTotalCost() {
            if (!this.selectedItem) return 0;
            return (
                this.effectivePrice(this.selectedItem) * this.purchaseQuantity
            );
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

            const userCash = parseFloat(this.currentUser.cash || 0);
            const itemPrice = this.effectivePrice(item);

            return (
                item.is_active &&
                (item.stock === null || item.stock > 0) &&
                userCash >= itemPrice &&
                parseFloat(item.cash_price || 0) > 0
            );
        },

        getButtonText(item) {
            if (!this.currentUser) return "Login";
            if (!item.is_active) return "Unavailable";
            if (item.stock !== null && item.stock <= 0) return "Sold Out";

            const userCash = parseFloat(this.currentUser.cash || 0);
            if (userCash < this.effectivePrice(item)) return "No Cash";
            return "Buy";
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
                this.fetchShop();
            }
        },
    },
};
</script>
