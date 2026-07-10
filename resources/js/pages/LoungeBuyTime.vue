<template>
    <Layout>
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">
                    Buy Consumable Time
                </h2>
                <p class="text-sm text-gray-500">
                    For Level 1 members only. ₱100 = 3 hours, multiples of ₱100.
                </p>
            </div>

            <!-- Search -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6"
            >
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Search Level 1 Member
                </label>
                <div class="relative">
                    <i
                        class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"
                    ></i>
                    <input
                        v-model="search"
                        @input="onSearchInput"
                        type="text"
                        placeholder="Search by name or username..."
                        class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    />
                </div>

                <!-- Results -->
                <div
                    v-if="searching"
                    class="text-center py-4 text-gray-400 text-sm"
                >
                    <i class="pi pi-spin pi-spinner"></i>
                </div>

                <div
                    v-else-if="results.length > 0"
                    class="mt-3 space-y-1 max-h-64 overflow-y-auto"
                >
                    <button
                        v-for="u in results"
                        :key="u.id"
                        @click="selectUser(u)"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 text-left transition-colors"
                        :class="selectedUser?.id === u.id ? 'bg-indigo-50' : ''"
                    >
                        <div>
                            <p class="text-sm font-medium text-gray-800">
                                {{ u.name }}
                            </p>
                            <p class="text-xs text-gray-400">
                                @{{ u.username }}
                            </p>
                        </div>
                        <span
                            class="text-xs font-semibold px-2 py-0.5 rounded-full"
                            :class="
                                u.consumable_minutes < 0
                                    ? 'bg-red-50 text-red-600'
                                    : 'bg-gray-50 text-gray-600'
                            "
                        >
                            {{ formatMinutes(u.consumable_minutes) }}
                        </span>
                    </button>
                </div>

                <div
                    v-else-if="search.length > 0 && !searching"
                    class="text-center py-4 text-gray-400 text-sm"
                >
                    No Level 1 members found.
                </div>
            </div>

            <!-- Selected user / purchase form -->
            <div
                v-if="selectedUser"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
            >
                <div
                    class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100"
                >
                    <div>
                        <p class="font-semibold text-gray-800">
                            {{ selectedUser.name }}
                        </p>
                        <p class="text-xs text-gray-400">
                            @{{ selectedUser.username }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Current Balance</p>
                        <p
                            class="text-lg font-bold"
                            :class="
                                selectedUser.consumable_minutes < 0
                                    ? 'text-red-600'
                                    : 'text-gray-800'
                            "
                        >
                            {{ formatMinutes(selectedUser.consumable_minutes) }}
                        </p>
                    </div>
                </div>

                <!-- Amount selector -->
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Amount (₱100 blocks)
                </label>
                <div class="grid grid-cols-4 gap-2 mb-3">
                    <button
                        v-for="blocks in [1, 2, 3, 5]"
                        :key="blocks"
                        @click="amountBlocks = blocks"
                        class="py-2 rounded-lg text-sm font-medium border transition-colors"
                        :class="
                            amountBlocks === blocks
                                ? 'bg-indigo-600 text-white border-indigo-600'
                                : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
                        "
                    >
                        ₱{{ blocks * 100 }}
                    </button>
                </div>

                <!-- Custom amount -->
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-sm text-gray-500">Custom:</span>
                    <div
                        class="flex items-center border border-gray-200 rounded-lg overflow-hidden"
                    >
                        <button
                            @click="
                                amountBlocks = Math.max(1, amountBlocks - 1)
                            "
                            class="px-3 py-1.5 text-gray-500 hover:bg-gray-50"
                        >
                            −
                        </button>
                        <span
                            class="px-4 py-1.5 text-sm font-medium min-w-[70px] text-center"
                        >
                            ₱{{ amountBlocks * 100 }}
                        </span>
                        <button
                            @click="amountBlocks++"
                            class="px-3 py-1.5 text-gray-500 hover:bg-gray-50"
                        >
                            +
                        </button>
                    </div>
                </div>

                <!-- Preview -->
                <div
                    class="bg-indigo-50 rounded-lg p-3 mb-4 text-sm text-indigo-700"
                >
                    Adds <strong>{{ amountBlocks * 3 }} hours</strong> ({{
                        amountBlocks * 180
                    }}
                    min). New balance will be
                    <strong>{{
                        formatMinutes(
                            selectedUser.consumable_minutes +
                                amountBlocks * 180,
                        )
                    }}</strong
                    >.
                </div>

                <!-- Payment method -->
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Method
                </label>
                <div
                    class="grid grid-cols-2 gap-2 mb-5 bg-gray-100 p-1 rounded-lg"
                >
                    <button
                        @click="paymentMethod = 'cash'"
                        class="py-1.5 rounded-md text-sm font-medium transition-all"
                        :class="
                            paymentMethod === 'cash'
                                ? 'bg-white shadow-sm text-indigo-600'
                                : 'text-gray-500'
                        "
                    >
                        <i class="pi pi-money-bill mr-1"></i> Cash
                    </button>
                    <button
                        @click="paymentMethod = 'balance'"
                        class="py-1.5 rounded-md text-sm font-medium transition-all"
                        :class="
                            paymentMethod === 'balance'
                                ? 'bg-white shadow-sm text-indigo-600'
                                : 'text-gray-500'
                        "
                    >
                        <i class="pi pi-wallet mr-1"></i> App Balance (₱{{
                            formatCash(selectedUser.cash)
                        }})
                    </button>
                </div>

                <p
                    v-if="
                        paymentMethod === 'balance' &&
                        selectedUser.cash < amountBlocks * 100
                    "
                    class="text-xs text-red-500 mb-3"
                >
                    Insufficient app balance for this amount.
                </p>

                <p v-if="errorMessage" class="text-xs text-red-500 mb-3">
                    {{ errorMessage }}
                </p>

                <button
                    @click="confirmPurchase"
                    :disabled="
                        purchasing ||
                        (paymentMethod === 'balance' &&
                            selectedUser.cash < amountBlocks * 100)
                    "
                    class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white font-medium py-2.5 rounded-lg text-sm transition-colors"
                >
                    {{
                        purchasing
                            ? "Processing..."
                            : `Confirm Purchase — ₱${amountBlocks * 100}`
                    }}
                </button>
            </div>

            <!-- Purchase History -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mt-6"
            >
                <h3 class="font-semibold text-gray-700 mb-4">
                    Purchase History
                </h3>

                <div
                    v-if="loadingHistory"
                    class="text-center py-6 text-gray-400"
                >
                    <i class="pi pi-spin pi-spinner"></i>
                </div>

                <div
                    v-else-if="history.length === 0"
                    class="text-center py-6 text-gray-400 text-sm"
                >
                    No purchases yet.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-gray-400 border-b border-gray-100"
                            >
                                <th class="py-2 font-medium">Member</th>
                                <th class="py-2 font-medium">Amount</th>
                                <th class="py-2 font-medium">Time Added</th>
                                <th class="py-2 font-medium">Payment</th>
                                <th class="py-2 font-medium">By</th>
                                <th class="py-2 font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="p in history"
                                :key="p.id"
                                class="border-b border-gray-50"
                            >
                                <td class="py-2 text-gray-700">
                                    {{ p.user?.name || "—" }}
                                </td>
                                <td class="py-2 text-gray-700">
                                    ₱{{ formatCash(p.amount) }}
                                </td>
                                <td class="py-2 text-gray-700">
                                    {{ formatMinutes(p.minutes_added) }}
                                </td>
                                <td class="py-2">
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full"
                                        :class="
                                            p.payment_method === 'cash'
                                                ? 'bg-green-50 text-green-600'
                                                : 'bg-blue-50 text-blue-600'
                                        "
                                    >
                                        {{
                                            p.payment_method === "cash"
                                                ? "Cash"
                                                : "App Balance"
                                        }}
                                    </span>
                                </td>
                                <td class="py-2 text-gray-500">
                                    {{ p.purchased_by?.name || "—" }}
                                </td>
                                <td class="py-2 text-gray-400 text-xs">
                                    {{ formatDateTime(p.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
export default {
    name: "LoungeBuyTime",
    data() {
        return {
            search: "",
            searching: false,
            results: [],
            searchTimeout: null,

            selectedUser: null,
            amountBlocks: 1,
            paymentMethod: "cash",
            purchasing: false,
            errorMessage: null,

            history: [],
            loadingHistory: false,
        };
    },
    methods: {
        async fetchHistory() {
            this.loadingHistory = true;
            try {
                const response = await axios.get(
                    "/api/lounge/consumable/history",
                );
                if (response.data.success) {
                    this.history = response.data.purchases;
                }
            } catch (error) {
                console.error("Failed to fetch history:", error);
            } finally {
                this.loadingHistory = false;
            }
        },

        formatDateTime(dateString) {
            return new Date(dateString).toLocaleString("en-PH", {
                month: "short",
                day: "numeric",
                hour: "numeric",
                minute: "2-digit",
            });
        },

        onSearchInput() {
            clearTimeout(this.searchTimeout);
            this.selectedUser = null;

            if (this.search.trim().length === 0) {
                this.results = [];
                return;
            }

            this.searchTimeout = setTimeout(() => {
                this.doSearch();
            }, 300);
        },

        async doSearch() {
            this.searching = true;
            try {
                const response = await axios.get(
                    "/api/lounge/consumable/search",
                    {
                        params: { q: this.search },
                    },
                );
                if (response.data.success) {
                    this.results = response.data.users;
                }
            } catch (error) {
                console.error("Search failed:", error);
                this.results = [];
            } finally {
                this.searching = false;
            }
        },

        selectUser(u) {
            this.selectedUser = u;
            this.amountBlocks = 1;
            this.paymentMethod = "cash";
            this.errorMessage = null;
            this.results = [];
            this.search = "";
        },

        formatMinutes(mins) {
            const sign = mins < 0 ? "-" : "";
            const abs = Math.abs(mins);
            const h = Math.floor(abs / 60);
            const m = abs % 60;
            return `${sign}${h}h ${m}m`;
        },

        formatCash(cash) {
            return parseFloat(cash || 0).toFixed(2);
        },

        async confirmPurchase() {
            this.purchasing = true;
            this.errorMessage = null;

            try {
                const response = await axios.post(
                    "/api/lounge/consumable/buy",
                    {
                        user_id: this.selectedUser.id,
                        amount: this.amountBlocks * 100,
                        payment_method: this.paymentMethod,
                    },
                );

                if (response.data.success) {
                    this.selectedUser.consumable_minutes =
                        response.data.user.consumable_minutes;
                    this.selectedUser.cash = response.data.user.cash;

                    // Reflect in the search results list too
                    const idx = this.results.findIndex(
                        (r) => r.id === this.selectedUser.id,
                    );
                    if (idx !== -1) {
                        this.results[idx].consumable_minutes =
                            response.data.user.consumable_minutes;
                    }

                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    this.amountBlocks = 1;
                    this.fetchHistory();
                }
            } catch (error) {
                this.errorMessage =
                    error.response?.data?.message ||
                    "Purchase failed. Please try again.";
            } finally {
                this.purchasing = false;
            }
        },
    },
    mounted() {
        this.fetchHistory();
    },
};
</script>
