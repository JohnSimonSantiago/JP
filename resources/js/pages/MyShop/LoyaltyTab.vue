<template>
    <div class="space-y-6">
        <!-- Loyalty Card Header -->
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">
                    Loyalty Card System
                </h3>
                <p class="text-gray-600">Manage customer loyalty and rewards</p>
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
                            loyaltyCardActive ? 'pi pi-pause' : 'pi pi-play'
                        "
                        class="mr-2"
                    ></i>
                    {{ loyaltyCardActive ? "Deactivate" : "Activate" }}
                </button>
            </div>
        </div>

        <!-- No Loyalty Card State -->
        <div
            v-if="!hasLoyaltyCard"
            class="bg-white rounded-xl shadow-lg p-12 text-center"
        >
            <i class="pi pi-gift text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">
                No Loyalty Card System
            </h3>
            <p class="text-gray-600 mb-6">
                Create a loyalty card to reward your customers and increase
                retention.
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
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold mb-2">
                            {{ loyaltyCard.name }}
                        </h3>
                        <p class="opacity-90 mb-4">
                            {{
                                loyaltyCard.description ||
                                "Reward your loyal customers"
                            }}
                        </p>
                        <div class="flex items-center gap-4">
                            <span
                                class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm"
                            >
                                <i class="pi pi-shopping-cart mr-1"></i>
                                {{ loyaltyCard.required_purchases }} purchases =
                                1 free item
                            </span>
                            <span
                                :class="
                                    loyaltyCardActive
                                        ? 'bg-green-500 bg-opacity-30'
                                        : 'bg-red-500 bg-opacity-30'
                                "
                                class="px-3 py-1 rounded-full text-sm"
                            >
                                {{ loyaltyCardActive ? "Active" : "Inactive" }}
                            </span>
                        </div>
                    </div>
                    <i class="pi pi-gift text-4xl opacity-50"></i>
                </div>
            </div>

            <!-- Customer Search and Management -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-lg font-semibold text-gray-900">
                        Customer Loyalty Management
                    </h4>
                </div>

                <!-- Search Bar -->
                <div class="relative mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Search Customers
                    </label>
                    <div class="relative">
                        <input
                            v-model="loyaltySearchQuery"
                            @input="searchLoyaltyUsers"
                            @focus="showLoyaltySearchDropdown = true"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent pl-10"
                            placeholder="Search customers by name..."
                            autocomplete="off"
                        />
                        <i
                            class="pi pi-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                        ></i>

                        <!-- Search Results Dropdown -->
                        <div
                            v-if="
                                showLoyaltySearchDropdown &&
                                loyaltySearchResults.length > 0
                            "
                            class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                        >
                            <div
                                v-for="user in loyaltySearchResults"
                                :key="user.id"
                                @click="selectLoyaltyUser(user)"
                                class="px-4 py-2 hover:bg-purple-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full overflow-hidden border border-gray-200 flex-shrink-0"
                                    >
                                        <img
                                            v-if="user.profile_image"
                                            :src="`/storage/profiles/${user.profile_image}`"
                                            :alt="user.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                                        >
                                            <i
                                                class="pi pi-user text-white text-xs"
                                            ></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ user.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ user.email }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loading indicator -->
                        <div
                            v-if="searchingLoyaltyUsers"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2"
                        >
                            <i class="pi pi-spin pi-spinner text-gray-400"></i>
                        </div>
                    </div>

                    <!-- Clear search -->
                    <div v-if="loyaltySearchQuery" class="mt-2">
                        <button
                            @click="clearLoyaltySearch"
                            class="text-sm text-purple-600 hover:text-purple-800"
                        >
                            <i class="pi pi-times mr-1"></i>
                            Clear search
                        </button>
                    </div>
                </div>

                <!-- Customer List -->
                <div
                    v-if="filteredLoyaltyCustomers.length > 0"
                    class="space-y-4"
                >
                    <h5 class="font-medium text-gray-900">
                        {{
                            loyaltySearchQuery
                                ? "Search Results"
                                : "Active Customers"
                        }}
                        ({{ filteredLoyaltyCustomers.length }})
                    </h5>
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                    >
                        <div
                            v-for="customer in filteredLoyaltyCustomers"
                            :key="customer.user_id"
                            class="bg-gradient-to-r from-purple-50 to-pink-50 p-4 rounded-lg border border-purple-200 hover:shadow-md transition-shadow cursor-pointer"
                            @click="openCustomerLoyaltyModal(customer)"
                        >
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="w-12 h-12 rounded-full overflow-hidden border-2 border-purple-200 flex-shrink-0"
                                >
                                    <img
                                        v-if="customer.user.profile_image"
                                        :src="`/storage/profiles/${customer.user.profile_image}`"
                                        :alt="customer.user.name"
                                        class="w-full h-full object-cover"
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
                                <div class="flex-1">
                                    <h6 class="font-semibold text-gray-900">
                                        {{ customer.user.name }}
                                    </h6>
                                    <p class="text-sm text-gray-600">
                                        {{ customer.current_purchases }}
                                        purchases
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span>Progress:</span>
                                    <span class="font-medium">
                                        {{
                                            getCurrentCardPurchases(customer)
                                        }}/{{ loyaltyCard.required_purchases }}
                                    </span>
                                </div>
                                <div
                                    class="w-full bg-purple-200 rounded-full h-2"
                                >
                                    <div
                                        class="bg-purple-500 h-2 rounded-full transition-all duration-300"
                                        :style="{
                                            width:
                                                getProgressPercentage(
                                                    customer,
                                                ) + '%',
                                        }"
                                    ></div>
                                </div>
                                <div class="text-xs text-gray-600 text-center">
                                    {{ getCompletedCards(customer) }} cards
                                    completed
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="loyaltySearchQuery && !searchingLoyaltyUsers"
                    class="text-center py-8 text-gray-500"
                >
                    <i class="pi pi-search text-3xl mb-2"></i>
                    <p>
                        No customers found matching "{{ loyaltySearchQuery }}"
                    </p>
                </div>

                <div
                    v-else-if="!loyaltySearchQuery"
                    class="text-center py-8 text-gray-500"
                >
                    <i class="pi pi-users text-3xl mb-2"></i>
                    <p>No customers with loyalty purchases yet</p>
                    <p class="text-sm mt-1">
                        Use the search above to find and add purchases for any
                        customer
                    </p>
                </div>
            </div>

            <!-- Claimed Rewards -->
            <div
                v-if="claimedRewards.length > 0"
                class="bg-white rounded-xl shadow-lg p-6"
            >
                <h4 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="pi pi-check-circle text-green-500 mr-2"></i>
                    Claimed Rewards ({{ claimedRewards.length }})
                </h4>
                <div class="space-y-4">
                    <div
                        v-for="reward in claimedRewards"
                        :key="reward.id"
                        class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-full overflow-hidden border-2 border-green-200 flex-shrink-0"
                            >
                                <img
                                    v-if="reward.user.profile_image"
                                    :src="`/storage/profiles/${reward.user.profile_image}`"
                                    :alt="reward.user.name"
                                    class="w-full h-full object-cover"
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
                            <div>
                                <h6 class="font-semibold text-gray-900">
                                    {{ reward.user.name }}
                                </h6>
                                <p class="text-sm text-gray-600">
                                    Claimed
                                    {{ formatDate(reward.claimed_at) }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div
                                class="bg-green-500 text-white px-3 py-1 rounded-full text-sm font-medium"
                            >
                                <i class="pi pi-check mr-1"></i>
                                Claimed
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Rewards -->
            <div
                v-if="pendingRewards.length > 0"
                class="bg-white rounded-xl shadow-lg p-6"
            >
                <h4 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="pi pi-clock text-orange-500 mr-2"></i>
                    Pending Rewards ({{ pendingRewards.length }})
                </h4>
                <div class="space-y-4">
                    <div
                        v-for="reward in pendingRewards"
                        :key="reward.id"
                        class="flex items-center justify-between p-4 bg-orange-50 border border-orange-200 rounded-lg"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 rounded-full overflow-hidden border-2 border-orange-200 flex-shrink-0"
                            >
                                <img
                                    v-if="reward.user.profile_image"
                                    :src="`/storage/profiles/${reward.user.profile_image}`"
                                    :alt="reward.user.name"
                                    class="w-full h-full object-cover"
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
                            <div>
                                <h6 class="font-semibold text-gray-900">
                                    {{ reward.user.name }}
                                </h6>
                                <p class="text-sm text-gray-600">
                                    Requested
                                    {{ formatDate(reward.created_at) }}
                                </p>
                                <div class="text-xs text-gray-500 mt-1">
                                    Completed
                                    {{
                                        reward.completed_purchases ||
                                        loyaltyCard.required_purchases
                                    }}
                                    purchases
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="markAsClaimed(reward.id)"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm transition-colors"
                            >
                                <i class="pi pi-check mr-1"></i>
                                Mark as Claimed
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loyalty Card Form Modal -->
        <div
            v-if="showLoyaltyCardForm"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
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
                            v-model.number="loyaltyCardForm.required_purchases"
                            type="number"
                            :min="1"
                            :max="100"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                        <small class="text-gray-500"
                            >How many items customers need to buy for 1 free
                            item</small
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
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    {{
                        selectedCustomerLoyalty
                            ? `${selectedCustomerLoyalty.user.name}'s Loyalty Progress`
                            : "Customer Loyalty"
                    }}
                </h3>
                <div v-if="selectedCustomerLoyalty" class="space-y-6">
                    <!-- Progress Overview -->
                    <div class="text-center bg-purple-50 p-6 rounded-lg">
                        <div class="text-3xl font-bold text-purple-600 mb-2">
                            {{ selectedCustomerLoyalty.current_purchases }}
                        </div>
                        <p class="text-gray-600">Total Purchases</p>

                        <div class="mt-4 text-center">
                            <div class="text-xl font-semibold text-gray-900">
                                {{ getCompletedCards(selectedCustomerLoyalty) }}
                                Cards Completed
                            </div>
                            <div class="text-sm text-gray-600">
                                Current Progress:
                                {{
                                    getCurrentCardPurchases(
                                        selectedCustomerLoyalty,
                                    )
                                }}/{{ loyaltyCard.required_purchases }}
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-4 mt-4">
                            <div
                                class="bg-purple-500 h-4 rounded-full transition-all duration-300"
                                :style="{
                                    width:
                                        getProgressPercentage(
                                            selectedCustomerLoyalty,
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
                                    'add',
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
                                    'remove',
                                )
                            "
                            :disabled="
                                adjustingLoyalty ||
                                selectedCustomerLoyalty.current_purchases === 0
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
                            formatDate(selectedCustomerLoyalty.last_purchase_at)
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
</template>

<script>
export default {
    name: "LoyaltyTab",
    props: {
        shop: { type: Object, required: true },
    },
    // Lets the parent update the pending-rewards badge count on the tab
    emits: ["pending-count-changed"],
    data() {
        return {
            loyaltySearchQuery: "",
            loyaltySearchResults: [],
            loyaltySearchTimeout: null,
            showLoyaltySearchDropdown: false,
            searchingLoyaltyUsers: false,
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
            _clickOutsideHandler: null,
        };
    },
    computed: {
        filteredLoyaltyCustomers() {
            if (!this.loyaltyProgress) return [];

            if (
                this.loyaltySearchQuery &&
                this.loyaltySearchResults.length > 0
            ) {
                return this.loyaltySearchResults.map((user) => {
                    const existingProgress = this.loyaltyProgress.find(
                        (p) => p.user_id === user.id,
                    );
                    return (
                        existingProgress || {
                            user_id: user.id,
                            user: user,
                            current_purchases: 0,
                            completed_cards: 0,
                            last_purchase_at: null,
                        }
                    );
                });
            }

            return this.loyaltyProgress.filter(
                (customer) => customer.current_purchases > 0,
            );
        },
        pendingRewards() {
            return this.loyaltyRewards.filter(
                (reward) => reward.status === "pending",
            );
        },
        claimedRewards() {
            return this.loyaltyRewards.filter(
                (reward) => reward.status === "claimed",
            );
        },
        loyaltyCardActive() {
            return this.loyaltyCard && this.loyaltyCard.is_active;
        },
        hasLoyaltyCard() {
            return this.loyaltyCard !== null;
        },
    },
    watch: {
        // Whenever pending rewards change, tell the parent so it can update the tab badge
        pendingRewards(newVal) {
            this.$emit("pending-count-changed", newVal.length);
        },
    },
    async mounted() {
        await this.fetchLoyaltyCard();

        this._clickOutsideHandler = (e) => {
            if (!e.target.closest(".relative")) {
                this.showLoyaltySearchDropdown = false;
            }
        };
        document.addEventListener("click", this._clickOutsideHandler);
    },
    beforeUnmount() {
        // Clean up the listener so it doesn't pile up each time the tab mounts
        if (this._clickOutsideHandler) {
            document.removeEventListener("click", this._clickOutsideHandler);
        }
    },
    methods: {
        searchLoyaltyUsers() {
            clearTimeout(this.loyaltySearchTimeout);

            if (this.loyaltySearchQuery.length < 2) {
                this.loyaltySearchResults = [];
                this.showLoyaltySearchDropdown = false;
                return;
            }

            this.loyaltySearchTimeout = setTimeout(async () => {
                try {
                    this.searchingLoyaltyUsers = true;
                    const response = await axios.get(
                        `/api/users/search?q=${encodeURIComponent(
                            this.loyaltySearchQuery,
                        )}`,
                    );

                    if (response.data.success) {
                        this.loyaltySearchResults = response.data.users;
                        this.showLoyaltySearchDropdown = true;
                    }
                } catch (error) {
                    console.error("Error searching loyalty users:", error);
                    this.loyaltySearchResults = [];
                } finally {
                    this.searchingLoyaltyUsers = false;
                }
            }, 300);
        },

        selectLoyaltyUser(user) {
            let customer = this.loyaltyProgress.find(
                (p) => p.user_id === user.id,
            );
            if (!customer) {
                customer = {
                    user_id: user.id,
                    user: user,
                    current_purchases: 0,
                    completed_cards: 0,
                    last_purchase_at: null,
                };
            }

            this.openCustomerLoyaltyModal(customer);
            this.clearLoyaltySearch();
        },

        clearLoyaltySearch() {
            this.loyaltySearchQuery = "";
            this.loyaltySearchResults = [];
            this.showLoyaltySearchDropdown = false;
        },

        async fetchLoyaltyCard() {
            if (!this.shop) return;

            try {
                this.loadingLoyaltyData = true;
                const response = await axios.get(
                    `/api/shops/${this.shop.id}/loyalty-card`,
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
                    `/api/shops/${this.shop.id}/loyalty-progress`,
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
                    this.loyaltyCardForm,
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
                    this.loyaltyCardForm,
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
                    `/api/shops/${this.shop.id}/loyalty-card/toggle`,
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
                        action: action,
                    },
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

        async markAsClaimed(rewardId) {
            try {
                const response = await axios.post(
                    `/api/shops/${this.shop.id}/loyalty-rewards/${rewardId}/mark-claimed`,
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

        openLoyaltyCardForm() {
            if (this.loyaltyCard) {
                this.loyaltyCardForm = {
                    name: this.loyaltyCard.name,
                    description: this.loyaltyCard.description,
                    required_purchases: this.loyaltyCard.required_purchases,
                    is_active: this.loyaltyCard.is_active,
                };
            } else {
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
                customer.current_purchases /
                    this.loyaltyCard.required_purchases,
            );
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
    },
};
</script>
