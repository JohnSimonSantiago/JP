<template>
    <Layout>
        <div>
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        Lounge Sessions
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ activeSessions.length }} active session(s)
                    </p>
                </div>
                <button
                    @click="showCheckIn = true"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                >
                    <i class="pi pi-plus"></i> Check In
                </button>
            </div>

            <!-- Active Session Cards -->
            <div v-if="loadingSessions" class="text-center py-12 text-gray-400">
                <i class="pi pi-spin pi-spinner text-2xl"></i>
            </div>

            <div
                v-else-if="activeSessions.length === 0"
                class="text-center py-12 text-gray-400"
            >
                <i class="pi pi-home text-4xl mb-3"></i>
                <p class="text-sm">
                    No active sessions. Check someone in to get started.
                </p>
            </div>

            <div
                v-else
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8"
            >
                <div
                    v-for="session in activeSessions"
                    :key="session.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
                >
                    <!-- Customer Info -->
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ session.customer_name }}
                            </p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span
                                    v-if="session.is_free"
                                    class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-medium"
                                >
                                    Level {{ session.user_level }} — Free
                                </span>
                                <span
                                    v-else-if="
                                        session.customer_type === 'walk_in'
                                    "
                                    class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"
                                >
                                    Walk-in
                                </span>
                                <span
                                    v-else
                                    class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full"
                                >
                                    Level {{ session.user_level }}
                                </span>
                            </div>
                        </div>
                        <i class="pi pi-user text-gray-300 text-2xl"></i>
                    </div>

                    <!-- Timer -->
                    <div
                        class="bg-gray-50 rounded-lg px-4 py-3 mb-3 text-center"
                    >
                        <p class="text-2xl font-mono font-bold text-indigo-600">
                            {{ getElapsed(session) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Checked in at
                            {{ formatTime(session.checked_in_at) }}
                        </p>
                    </div>

                    <!-- Grace period disclaimer (non-free only) -->
                    <p
                        v-if="!session.is_free"
                        class="text-xs text-gray-400 mb-3 text-center"
                    >
                        ⏱ 10-min grace period per hour applies
                    </p>

                    <!-- Checkout Button -->
                    <button
                        @click="openCheckout(session)"
                        class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-medium py-2 rounded-lg text-sm transition-colors"
                    >
                        Check Out
                    </button>
                </div>
            </div>

            <!-- Session History -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-700">Session History</h3>
                    <input
                        v-model="historyDate"
                        type="date"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        @change="fetchHistory"
                    />
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
                    No completed sessions for this date.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-gray-500 border-b border-gray-100"
                            >
                                <th class="pb-2 font-medium">Customer</th>
                                <th class="pb-2 font-medium">Type</th>
                                <th class="pb-2 font-medium">Check In</th>
                                <th class="pb-2 font-medium">Check Out</th>
                                <th class="pb-2 font-medium">Duration</th>
                                <th class="pb-2 font-medium">Bill</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="s in history"
                                :key="s.id"
                                class="border-b border-gray-50 hover:bg-gray-50"
                            >
                                <td class="py-2 font-medium text-gray-800">
                                    {{ s.customer_name }}
                                </td>
                                <td class="py-2">
                                    <span
                                        v-if="s.is_free"
                                        class="text-yellow-600 font-medium"
                                        >Free</span
                                    >
                                    <span
                                        v-else-if="
                                            s.customer_type === 'walk_in'
                                        "
                                        class="text-gray-500"
                                        >Walk-in</span
                                    >
                                    <span v-else class="text-blue-600"
                                        >Lv{{ s.user_level }}</span
                                    >
                                </td>
                                <td class="py-2 text-gray-500">
                                    {{ formatTime(s.checked_in_at) }}
                                </td>
                                <td class="py-2 text-gray-500">
                                    {{ formatTime(s.checked_out_at) }}
                                </td>
                                <td class="py-2 text-gray-500">
                                    {{ getDuration(s) }}
                                </td>
                                <td
                                    class="py-2 font-semibold"
                                    :class="
                                        s.is_free
                                            ? 'text-yellow-600'
                                            : 'text-gray-800'
                                    "
                                >
                                    {{
                                        s.is_free ? "Free" : "₱" + s.total_bill
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Check In Modal ── -->
            <div
                v-if="showCheckIn"
                class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6"
                >
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        Check In Customer
                    </h3>

                    <div class="space-y-4">
                        <!-- Customer Type -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Customer Type</label
                            >
                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    @click="
                                        checkInForm.customer_type = 'walk_in';
                                        checkInForm.user_id = null;
                                        checkInForm.customer_name = '';
                                    "
                                    :class="
                                        checkInForm.customer_type === 'walk_in'
                                            ? 'border-indigo-400 bg-indigo-50 ring-2 ring-indigo-300'
                                            : 'border-gray-200'
                                    "
                                    class="cursor-pointer border rounded-lg p-3 text-center transition-all"
                                >
                                    <p class="text-sm font-medium">Walk-in</p>
                                    <p class="text-xs text-gray-400">
                                        Not a user
                                    </p>
                                </div>
                                <div
                                    @click="
                                        checkInForm.customer_type = 'member'
                                    "
                                    :class="
                                        checkInForm.customer_type === 'member'
                                            ? 'border-indigo-400 bg-indigo-50 ring-2 ring-indigo-300'
                                            : 'border-gray-200'
                                    "
                                    class="cursor-pointer border rounded-lg p-3 text-center transition-all"
                                >
                                    <p class="text-sm font-medium">Member</p>
                                    <p class="text-xs text-gray-400">
                                        Existing user
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Walk-in: just a name -->
                        <div v-if="checkInForm.customer_type === 'walk_in'">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Full Name</label
                            >
                            <input
                                v-model="checkInForm.customer_name"
                                type="text"
                                placeholder="e.g. John Simon Santiago"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            />
                        </div>

                        <!-- Member: search user -->
                        <div v-if="checkInForm.customer_type === 'member'">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Search User</label
                            >
                            <input
                                v-model="userSearch"
                                type="text"
                                placeholder="Type a name..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                @input="searchUsers"
                            />
                            <div
                                v-if="userResults.length > 0"
                                class="border border-gray-200 rounded-lg mt-1 max-h-40 overflow-y-auto shadow-sm"
                            >
                                <div
                                    v-for="u in userResults"
                                    :key="u.id"
                                    @click="selectUser(u)"
                                    class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm flex items-center justify-between"
                                >
                                    <span>{{ u.name }}</span>
                                    <span class="text-xs text-gray-400"
                                        >Level {{ u.level }}</span
                                    >
                                </div>
                            </div>
                            <div
                                v-if="selectedUser"
                                class="mt-2 bg-indigo-50 border border-indigo-200 rounded-lg px-3 py-2 text-sm flex justify-between items-center"
                            >
                                <span class="font-medium text-indigo-800">{{
                                    selectedUser.name
                                }}</span>
                                <span class="text-xs text-indigo-500">
                                    Level {{ selectedUser.level }}
                                    {{
                                        selectedUser.level >= 2
                                            ? "— Free Access"
                                            : ""
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="checkInError" class="text-red-500 text-sm mt-3">
                        {{ checkInError }}
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button
                            @click="
                                showCheckIn = false;
                                resetCheckIn();
                            "
                            class="flex-1 border border-gray-200 text-gray-600 py-2 rounded-lg text-sm hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitCheckIn"
                            :disabled="checkingIn"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50"
                        >
                            {{ checkingIn ? "Checking in..." : "Check In" }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Check Out Modal ── -->
            <div
                v-if="showCheckOut"
                class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6"
                >
                    <h3 class="text-lg font-bold text-gray-800 mb-1">
                        Check Out
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        {{ checkoutSession?.customer_name }}
                    </p>

                    <div v-if="checkoutResult">
                        <div
                            v-if="checkoutResult.is_free"
                            class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center mb-4"
                        >
                            <p class="text-yellow-700 font-semibold text-lg">
                                Free Access
                            </p>
                            <p class="text-yellow-600 text-sm">
                                Level {{ checkoutSession.user_level }} member —
                                no charge
                            </p>
                        </div>
                        <div v-else class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-500">Duration</span>
                                <span class="font-medium">{{
                                    checkoutDuration
                                }}</span>
                            </div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-500">Billing</span>
                                <span class="font-medium text-gray-700">{{
                                    checkoutResult.bill_breakdown
                                }}</span>
                            </div>
                            <div
                                class="border-t border-gray-200 mt-3 pt-3 flex justify-between"
                            >
                                <span class="font-semibold text-gray-700"
                                    >Total</span
                                >
                                <span class="text-xl font-bold text-indigo-600"
                                    >₱{{ checkoutResult.total_bill }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-6 text-gray-400">
                        <i class="pi pi-spin pi-spinner text-xl"></i>
                    </div>

                    <button
                        @click="confirmCheckout"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition-colors mt-2"
                    >
                        Confirm & Close Session
                    </button>
                    <button
                        @click="
                            showCheckOut = false;
                            checkoutResult = null;
                        "
                        class="w-full mt-2 text-sm text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
export default {
    name: "LoungeSessionsDashboard",
    data() {
        return {
            activeSessions: [],
            history: [],
            loadingSessions: true,
            loadingHistory: false,
            historyDate: new Date().toISOString().split("T")[0],
            tickInterval: null,
            tick: 0,

            // Check in
            showCheckIn: false,
            checkingIn: false,
            checkInError: null,
            checkInForm: {
                customer_name: "",
                customer_type: "walk_in",
                user_id: null,
            },
            userSearch: "",
            userResults: [],
            selectedUser: null,
            searchTimeout: null,

            // Check out
            showCheckOut: false,
            checkoutSession: null,
            checkoutResult: null,
            checkoutDuration: "",
        };
    },

    async mounted() {
        await this.fetchSessions();
        await this.fetchHistory();
        this.tickInterval = setInterval(() => {
            this.tick++;
        }, 1000);
    },

    beforeUnmount() {
        clearInterval(this.tickInterval);
    },

    methods: {
        token() {
            return localStorage.getItem("auth-token");
        },

        headers() {
            return { Authorization: `Bearer ${this.token()}` };
        },

        async fetchSessions() {
            this.loadingSessions = true;
            try {
                const res = await axios.get("/api/lounge/active-sessions", {
                    headers: this.headers(),
                });
                if (res.data.success) this.activeSessions = res.data.sessions;
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingSessions = false;
            }
        },

        async fetchHistory() {
            this.loadingHistory = true;
            try {
                const res = await axios.get("/api/lounge/session-history", {
                    headers: this.headers(),
                    params: { date: this.historyDate },
                });
                if (res.data.success) this.history = res.data.sessions;
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingHistory = false;
            }
        },

        getElapsed(session) {
            void this.tick; // reactivity trigger
            const diff = Math.floor(
                (Date.now() - new Date(session.checked_in_at).getTime()) / 1000,
            );
            const h = Math.floor(diff / 3600)
                .toString()
                .padStart(2, "0");
            const m = Math.floor((diff % 3600) / 60)
                .toString()
                .padStart(2, "0");
            const s = (diff % 60).toString().padStart(2, "0");
            return `${h}:${m}:${s}`;
        },

        formatTime(dt) {
            if (!dt) return "—";
            return new Date(dt).toLocaleTimeString("en-PH", {
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        getDuration(session) {
            if (!session.checked_out_at) return "—";
            const mins = Math.floor(
                (new Date(session.checked_out_at) -
                    new Date(session.checked_in_at)) /
                    60000,
            );
            const h = Math.floor(mins / 60);
            const m = mins % 60;
            return h > 0 ? `${h}h ${m}m` : `${m}m`;
        },

        searchUsers() {
            clearTimeout(this.searchTimeout);
            if (this.userSearch.length < 2) {
                this.userResults = [];
                return;
            }
            this.searchTimeout = setTimeout(async () => {
                try {
                    const res = await axios.get("/api/users/search", {
                        headers: this.headers(),
                        params: { q: this.userSearch },
                    });
                    this.userResults = res.data.users || [];
                } catch (e) {
                    this.userResults = [];
                }
            }, 300);
        },

        selectUser(u) {
            this.selectedUser = u;
            this.checkInForm.user_id = u.id;
            this.checkInForm.customer_name = u.name;
            this.userResults = [];
            this.userSearch = u.name;
        },

        resetCheckIn() {
            this.checkInForm = {
                customer_name: "",
                customer_type: "walk_in",
                user_id: null,
            };
            this.userSearch = "";
            this.userResults = [];
            this.selectedUser = null;
            this.checkInError = null;
        },

        async submitCheckIn() {
            this.checkInError = null;
            if (!this.checkInForm.customer_name) {
                this.checkInError = "Please enter a name.";
                return;
            }
            this.checkingIn = true;
            try {
                const res = await axios.post(
                    "/api/lounge/check-in",
                    this.checkInForm,
                    { headers: this.headers() },
                );
                if (res.data.success) {
                    this.showCheckIn = false;
                    this.resetCheckIn();
                    await this.fetchSessions();
                }
            } catch (e) {
                this.checkInError =
                    e.response?.data?.message || "Check-in failed.";
            } finally {
                this.checkingIn = false;
            }
        },

        async openCheckout(session) {
            this.checkoutSession = session;
            this.checkoutResult = null;
            this.showCheckOut = true;

            // Pre-compute bill preview
            try {
                const res = await axios.post(
                    `/api/lounge/check-out/${session.id}?preview=1`,
                    {},
                    { headers: this.headers() },
                );
                if (res.data.success) {
                    this.checkoutResult = res.data;
                    this.checkoutResult.is_free = session.is_free;
                    this.checkoutDuration = this.getDurationFromNow(
                        session.checked_in_at,
                    );
                }
            } catch (e) {
                console.error(e);
            }
        },

        async confirmCheckout() {
            try {
                await axios.post(
                    `/api/lounge/check-out/${this.checkoutSession.id}`,
                    {},
                    { headers: this.headers() },
                );
                this.showCheckOut = false;
                this.checkoutResult = null;
                await this.fetchSessions();
                await this.fetchHistory();
            } catch (e) {
                console.error(e);
            }
        },

        getDurationFromNow(checkedInAt) {
            const mins = Math.floor(
                (Date.now() - new Date(checkedInAt).getTime()) / 60000,
            );
            const h = Math.floor(mins / 60);
            const m = mins % 60;
            return h > 0 ? `${h}h ${m}m` : `${m}m`;
        },
    },
};
</script>
