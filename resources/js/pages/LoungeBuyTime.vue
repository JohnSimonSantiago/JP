<template>
    <Layout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">
                    Buy Consumable Time
                </h2>
                <p class="text-sm text-gray-500">
                    For Level 1 members only. ₱100 = 3 hours, ₱40 per extra
                    hour.
                </p>
            </div>

            <!-- Tabs -->
            <div class="grid grid-cols-2 gap-2 mb-6 bg-gray-100 p-1 rounded-lg">
                <button
                    @click="activeTab = 'buy'"
                    class="py-2 rounded-md text-sm font-medium transition-all"
                    :class="
                        activeTab === 'buy'
                            ? 'bg-white shadow-sm text-indigo-600'
                            : 'text-gray-500'
                    "
                >
                    <i class="pi pi-plus-circle mr-1"></i> Buy Time
                </button>
                <button
                    @click="switchToBalances"
                    class="py-2 rounded-md text-sm font-medium transition-all"
                    :class="
                        activeTab === 'balances'
                            ? 'bg-white shadow-sm text-indigo-600'
                            : 'text-gray-500'
                    "
                >
                    <i class="pi pi-users mr-1"></i> Balances
                    <span
                        v-if="balances.length"
                        class="ml-1 text-xs text-gray-400"
                        >({{ balances.length }})</span
                    >
                </button>
            </div>

            <!-- Search -->
            <div
                v-show="activeTab === 'buy'"
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
                v-if="selectedUser && activeTab === 'buy'"
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

                <!-- Hours selector -->
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Hours
                </label>
                <div class="grid grid-cols-4 gap-2 mb-3">
                    <button
                        v-for="h in [1, 2, 3, 6]"
                        :key="h"
                        @click="hours = h"
                        class="py-2 rounded-lg text-sm font-medium border transition-colors"
                        :class="
                            hours === h
                                ? 'bg-indigo-600 text-white border-indigo-600'
                                : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
                        "
                    >
                        {{ h }}h
                    </button>
                </div>

                <!-- Custom hours -->
                <div class="flex items-center gap-2 mb-4">
                    <span class="text-sm text-gray-500">Custom:</span>
                    <div
                        class="flex items-center border border-gray-200 rounded-lg overflow-hidden"
                    >
                        <button
                            @click="hours = Math.max(1, hours - 1)"
                            class="px-3 py-1.5 text-gray-500 hover:bg-gray-50"
                        >
                            −
                        </button>
                        <span
                            class="px-4 py-1.5 text-sm font-medium min-w-[70px] text-center"
                        >
                            {{ hours }}h
                        </span>
                        <button
                            @click="hours = Math.min(24, hours + 1)"
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
                    <p>
                        <strong>{{ hours }} hour(s)</strong> —
                        {{ priceBreakdown }} =
                        <strong>₱{{ computedPrice }}</strong>
                    </p>
                    <p class="mt-1">
                        New balance:
                        <strong>{{
                            formatMinutes(
                                selectedUser.consumable_minutes + hours * 60,
                            )
                        }}</strong>
                    </p>
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
                        selectedUser.cash < computedPrice
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
                            selectedUser.cash < computedPrice)
                    "
                    class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white font-medium py-2.5 rounded-lg text-sm transition-colors"
                >
                    {{
                        purchasing
                            ? "Processing..."
                            : `Confirm Purchase — ₱${computedPrice}`
                    }}
                </button>
            </div>

            <!-- Balances Tab -->
            <div
                v-show="activeTab === 'balances'"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-700">
                        Members with Time
                    </h3>
                    <div class="flex items-center gap-3">
                        <button
                            @click="toggleSort"
                            :class="
                                sortAlphabetical
                                    ? 'bg-indigo-600 text-white border-indigo-600'
                                    : 'bg-white text-gray-600 border-gray-200'
                            "
                            class="flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-medium transition-colors"
                        >
                            <i class="pi pi-sort-alpha-down"></i>
                            {{ sortAlphabetical ? "A → Z" : "Default" }}
                        </button>
                        <button
                            @click="fetchBalances"
                            class="text-xs text-indigo-600 hover:underline"
                        >
                            <i class="pi pi-refresh mr-1"></i> Refresh
                        </button>
                    </div>
                </div>

                <div
                    v-if="loadingBalances"
                    class="text-center py-6 text-gray-400"
                >
                    <i class="pi pi-spin pi-spinner"></i>
                </div>

                <div
                    v-else-if="balances.length === 0"
                    class="text-center py-6 text-gray-400 text-sm"
                >
                    No members with a balance.
                </div>

                <div v-else>
                    <!-- Summary -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-indigo-50 rounded-lg p-3">
                            <p class="text-xs text-indigo-400">
                                Total Time Outstanding
                            </p>
                            <p class="text-lg font-bold text-indigo-700">
                                {{ formatMinutes(totalMinutes) }}
                            </p>
                        </div>
                        <div class="bg-red-50 rounded-lg p-3">
                            <p class="text-xs text-red-400">
                                Members Over Balance
                            </p>
                            <p class="text-lg font-bold text-red-600">
                                {{ owingCount }}
                            </p>
                        </div>
                    </div>

                    <input
                        v-model="balanceSearch"
                        type="text"
                        placeholder="Search by name or username..."
                        class="w-full mb-3 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    />
                    <div class="flex gap-2">
                        <div ref="balancesList" class="flex-1 space-y-1">
                            <button
                                v-for="u in filteredBalances"
                                :key="u.id"
                                :data-letter="
                                    (u.name || '').charAt(0).toUpperCase()
                                "
                                @click="selectFromBalances(u)"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 text-left transition-colors"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-800"
                                    >
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
                                            : 'bg-green-50 text-green-600'
                                    "
                                >
                                    {{ formatMinutes(u.consumable_minutes) }}
                                </span>
                            </button>
                        </div>
                        <div class="flex flex-col text-base leading-snug">
                            <button
                                v-for="L in alphabet"
                                :key="L"
                                @click="scrollToLetter(L)"
                                :disabled="!availableLetters.has(L)"
                                :class="
                                    availableLetters.has(L)
                                        ? 'text-indigo-600 hover:font-bold'
                                        : 'text-gray-300 cursor-default'
                                "
                                class="px-1"
                            >
                                {{ L }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase History -->
            <div
                v-show="activeTab === 'buy'"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mt-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-baseline gap-3">
                        <h3 class="font-semibold text-gray-700">
                            Purchase History
                        </h3>
                        <span
                            v-if="!loadingHistory && history.length > 0"
                            class="text-sm"
                        >
                            <span class="text-gray-400">
                                {{ rangeLabel }}:
                            </span>
                            <span class="font-bold text-green-600">
                                ₱{{ totalAmount }}
                            </span>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <select
                            v-model="rangePreset"
                            @change="applyPreset"
                            class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        >
                            <option value="today">Today</option>
                            <option value="week">This week</option>
                            <option value="month">This month</option>
                            <option value="custom">Custom</option>
                        </select>
                        <input
                            v-model="fromDate"
                            type="date"
                            class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            @change="onManualDateChange"
                        />
                        <span class="text-gray-400 text-sm">to</span>
                        <input
                            v-model="toDate"
                            type="date"
                            class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            @change="onManualDateChange"
                        />
                    </div>
                </div>

                <!-- Range totals -->
                <div
                    v-if="!loadingHistory && history.length > 0"
                    class="grid grid-cols-3 gap-3 mb-4"
                >
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-xs text-green-400">Cash</p>
                        <p class="text-lg font-bold text-green-600">
                            ₱{{ totalCash }}
                        </p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-xs text-blue-400">App Balance</p>
                        <p class="text-lg font-bold text-blue-600">
                            ₱{{ totalBalance }}
                        </p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-3">
                        <p class="text-xs text-indigo-400">Hours Sold</p>
                        <p class="text-lg font-bold text-indigo-700">
                            {{ formatMinutes(totalMinutesSold) }}
                        </p>
                    </div>
                </div>

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
                    No purchases for this range.
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
            hours: 3,
            paymentMethod: "cash",
            purchasing: false,
            errorMessage: null,

            history: [],
            loadingHistory: false,
            fromDate: null,
            toDate: null,
            todayDate: null,
            rangePreset: "today",
            totalCash: 0,
            totalBalance: 0,
            totalAmount: 0,
            totalMinutesSold: 0,

            sortAlphabetical: true,
            balanceSearch: "",
            alphabet: "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split(""),
            activeTab: "buy",
            balances: [],
            totalMinutes: 0,
            owingCount: 0,
            loadingBalances: false,
        };
    },
    computed: {
        filteredBalances() {
            const q = this.balanceSearch.trim().toLowerCase();
            if (!q) return this.balances;
            return this.balances.filter(
                (u) =>
                    u.name?.toLowerCase().includes(q) ||
                    u.username?.toLowerCase().includes(q),
            );
        },
        availableLetters() {
            const set = new Set();
            this.balances.forEach((u) => {
                const c = (u.name || "").charAt(0).toUpperCase();
                if (c >= "A" && c <= "Z") set.add(c);
            });
            return set;
        },
        computedPrice() {
            const blocks = Math.floor(this.hours / 3);
            const extra = this.hours % 3;
            return blocks * 100 + extra * 40;
        },
        priceBreakdown() {
            const blocks = Math.floor(this.hours / 3);
            const extra = this.hours % 3;
            const parts = [];
            if (blocks > 0) parts.push(`${blocks} × ₱100 (3h)`);
            if (extra > 0) parts.push(`${extra} × ₱40`);
            return parts.join(" + ");
        },
        rangeLabel() {
            if (this.fromDate === this.toDate) {
                return this.fromDate === this.todayDate ? "Today" : "That day";
            }
            if (this.rangePreset === "week") return "This week";
            if (this.rangePreset === "month") return "This month";
            return "Range";
        },
    },
    methods: {
        async fetchBalances() {
            this.loadingBalances = true;
            try {
                const response = await axios.get(
                    "/api/lounge/consumable/balances",
                );
                if (response.data.success) {
                    this.balances = this.applySort(response.data.users);
                    this.totalMinutes = response.data.total_minutes;
                    this.owingCount = response.data.owing_count;
                }
            } catch (error) {
                console.error("Failed to fetch balances:", error);
            } finally {
                this.loadingBalances = false;
            }
        },

        toggleSort() {
            this.sortAlphabetical = !this.sortAlphabetical;
            this.balances = this.applySort(this.balances);
        },

        switchToBalances() {
            this.activeTab = "balances";
            if (this.balances.length === 0) this.fetchBalances();
        },

        selectFromBalances(u) {
            this.activeTab = "buy";
            this.selectUser(u);
        },
        localDate(d) {
            // Avoids the UTC shift that toISOString() causes in PH time
            const off = d.getTimezoneOffset() * 60000;
            return new Date(d.getTime() - off).toISOString().split("T")[0];
        },

        applyPreset() {
            const now = new Date();

            if (this.rangePreset === "today") {
                this.fromDate = this.localDate(now);
                this.toDate = this.localDate(now);
            } else if (this.rangePreset === "week") {
                // Monday as the start of the week
                const day = now.getDay();
                const diff = day === 0 ? 6 : day - 1;
                const monday = new Date(now);
                monday.setDate(now.getDate() - diff);
                this.fromDate = this.localDate(monday);
                this.toDate = this.localDate(now);
            } else if (this.rangePreset === "month") {
                const first = new Date(now.getFullYear(), now.getMonth(), 1);
                this.fromDate = this.localDate(first);
                this.toDate = this.localDate(now);
            } else {
                return; // custom — leave dates alone
            }

            this.fetchHistory();
        },

        onManualDateChange() {
            this.rangePreset = "custom";
            this.fetchHistory();
        },

        async fetchHistory() {
            this.loadingHistory = true;
            try {
                const response = await axios.get(
                    "/api/lounge/consumable/history",
                    {
                        params: { from: this.fromDate, to: this.toDate },
                    },
                );
                if (response.data.success) {
                    this.history = response.data.purchases;
                    this.totalCash = Number(response.data.total_cash) || 0;
                    this.totalBalance =
                        Number(response.data.total_balance) || 0;
                    this.totalAmount = Number(response.data.total_amount) || 0;
                    this.totalMinutesSold =
                        Number(response.data.total_minutes) || 0;
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
            this.hours = 3;
            this.paymentMethod = "cash";
            this.errorMessage = null;
            this.results = [];
            this.search = "";
        },

        scrollToLetter(letter) {
            if (!this.availableLetters.has(letter)) return;
            this.$nextTick(() => {
                const container = this.$refs.balancesList;
                const target = container?.querySelector(
                    `[data-letter="${letter}"]`,
                );
                if (target)
                    target.scrollIntoView({
                        behavior: "smooth",
                        block: "start",
                    });
            });
        },

        applySort(list) {
            if (!this.sortAlphabetical) return list;
            return [...list].sort((a, b) =>
                (a.name || "").localeCompare(b.name || ""),
            );
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
                        hours: this.hours,
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

                    this.hours = 3;
                    this.fetchHistory();
                    if (this.balances.length) this.fetchBalances();
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
        const today = this.localDate(new Date());
        this.fromDate = today;
        this.toDate = today;
        this.todayDate = today;
        this.fetchHistory();
    },
};
</script>
