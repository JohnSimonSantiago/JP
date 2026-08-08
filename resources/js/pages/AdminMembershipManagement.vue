<template>
    <Layout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Membership Applications
                </h1>
                <p class="text-gray-600">
                    Review and approve pending membership applications
                </p>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 mb-6">
                <button
                    @click="activeTab = 'pending'"
                    :class="
                        activeTab === 'pending'
                            ? 'bg-indigo-500 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    "
                    class="px-5 py-2 rounded-lg text-sm font-semibold transition-colors"
                >
                    Pending ({{ memberships.length }})
                </button>
                <button
                    @click="switchToActive"
                    :class="
                        activeTab === 'active'
                            ? 'bg-indigo-500 text-white'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    "
                    class="px-5 py-2 rounded-lg text-sm font-semibold transition-colors"
                >
                    Active ({{ active.length }})
                </button>
            </div>

            <!-- ===== PENDING TAB ===== -->
            <template v-if="activeTab === 'pending'">
                <!-- Stats bar -->
                <div
                    class="mb-4 bg-amber-50 border border-amber-200 rounded-lg px-4 py-2.5 flex items-center gap-2"
                >
                    <i class="pi pi-clock text-amber-600"></i>
                    <span class="text-sm font-medium text-amber-700">
                        {{ memberships.length }} pending application{{
                            memberships.length !== 1 ? "s" : ""
                        }}
                    </span>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="text-center py-16">
                    <i
                        class="pi pi-spin pi-spinner text-indigo-500 text-3xl"
                    ></i>
                </div>

                <!-- Empty -->
                <div
                    v-else-if="memberships.length === 0"
                    class="text-center py-16 bg-white rounded-lg shadow-sm"
                >
                    <i
                        class="pi pi-inbox text-gray-300 text-5xl mb-3 block"
                    ></i>
                    <p class="text-gray-400">No pending applications</p>
                </div>

                <!-- List -->
                <div v-else class="space-y-4">
                    <div
                        v-for="item in memberships"
                        :key="item.id"
                        class="bg-white shadow-sm rounded-xl p-5"
                    >
                        <!-- User info -->
                        <div class="flex items-center gap-3 mb-4">
                            <img
                                v-if="item.user?.profile_image"
                                :src="`/storage/profiles/${item.user.profile_image}`"
                                class="w-11 h-11 rounded-full object-cover"
                            />
                            <div
                                v-else
                                class="w-11 h-11 rounded-full bg-indigo-500 flex items-center justify-center text-white"
                            >
                                <i class="pi pi-user"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-800">
                                    {{ item.user?.name }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    {{ item.user?.email }}
                                </div>
                            </div>
                            <span
                                class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-1 rounded-lg capitalize"
                            >
                                {{ item.type }}
                            </span>
                        </div>

                        <!-- Dates -->
                        <div
                            class="grid grid-cols-3 gap-2 bg-gray-50 rounded-lg p-3 mb-4"
                        >
                            <div class="text-center">
                                <div class="text-xs text-gray-400 mb-1">
                                    Start
                                </div>
                                <div
                                    class="text-xs font-semibold text-gray-700"
                                >
                                    {{ formatDate(item.start_date) }}
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-xs text-gray-400 mb-1">
                                    End
                                </div>
                                <div
                                    class="text-xs font-semibold text-gray-700"
                                >
                                    {{ formatDate(item.end_date) }}
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-xs text-gray-400 mb-1">
                                    Applied
                                </div>
                                <div
                                    class="text-xs font-semibold text-gray-700"
                                >
                                    {{ formatDate(item.created_at) }}
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3">
                            <button
                                @click="rejectMembership(item)"
                                :disabled="processingIds.includes(item.id)"
                                class="flex items-center gap-2 border-2 border-red-500 text-red-600 font-semibold px-5 py-2 rounded-lg hover:bg-red-50 disabled:opacity-50 transition-colors"
                            >
                                <i class="pi pi-times"></i>
                                Reject
                            </button>
                            <button
                                @click="approveMembership(item)"
                                :disabled="processingIds.includes(item.id)"
                                class="flex items-center gap-2 bg-green-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors"
                            >
                                <i
                                    v-if="processingIds.includes(item.id)"
                                    class="pi pi-spin pi-spinner"
                                ></i>
                                <i v-else class="pi pi-check"></i>
                                Approve
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ===== ACTIVE TAB ===== -->
            <template v-else-if="activeTab === 'active'">
                <div
                    class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-2.5 flex items-center gap-2"
                >
                    <i class="pi pi-check-circle text-green-600"></i>
                    <span class="text-sm font-medium text-green-700">
                        {{ active.length }} active member{{
                            active.length !== 1 ? "s" : ""
                        }}
                    </span>
                </div>

                <div v-if="loadingActive" class="text-center py-16">
                    <i
                        class="pi pi-spin pi-spinner text-indigo-500 text-3xl"
                    ></i>
                </div>

                <div
                    v-else-if="active.length === 0"
                    class="text-center py-16 bg-white rounded-lg shadow-sm"
                >
                    <i
                        class="pi pi-users text-gray-300 text-5xl mb-3 block"
                    ></i>
                    <p class="text-gray-400">No active members</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="m in active"
                        :key="m.id"
                        class="bg-white shadow-sm rounded-xl p-4 flex items-center gap-3"
                    >
                        <img
                            v-if="m.user?.profile_image"
                            :src="`/storage/profiles/${m.user.profile_image}`"
                            class="w-11 h-11 rounded-full object-cover"
                        />
                        <div
                            v-else
                            class="w-11 h-11 rounded-full bg-indigo-500 flex items-center justify-center text-white"
                        >
                            <i class="pi pi-user"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-800">
                                {{ m.user?.name }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{
                                    m.type === "level_2" ? "Level 2" : "Level 3"
                                }}
                                · expires {{ formatDate(m.end_date) }}
                            </div>
                        </div>
                        <button
                            v-if="m.user?.valid_id"
                            @click="idModalUser = m.user"
                            class="flex items-center gap-2 bg-purple-100 text-purple-700 font-semibold px-4 py-2 rounded-lg hover:bg-purple-200 transition-colors text-sm"
                        >
                            <i class="pi pi-id-card"></i>
                            View ID
                        </button>
                        <span v-else class="text-xs text-gray-400 px-2">
                            No ID
                        </span>
                    </div>
                </div>
            </template>

            <!-- History Section -->
            <div class="mt-10">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">History</h2>
                    <span
                        v-if="!loadingHistory && history.length"
                        class="text-sm text-gray-500"
                    >
                        {{ rangeLabel }} revenue:
                        <span class="font-bold text-green-600"
                            >₱{{ revenue.toLocaleString() }}</span
                        >
                    </span>
                </div>

                <!-- Preset buttons -->
                <div class="flex gap-2 mb-3">
                    <button
                        v-for="p in ['today', 'week', 'month']"
                        :key="p"
                        @click="applyPreset(p)"
                        :class="
                            rangePreset === p
                                ? 'bg-indigo-500 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        "
                        class="px-4 py-1.5 rounded-lg text-sm font-medium capitalize transition-colors"
                    >
                        {{
                            p === "today"
                                ? "Today"
                                : p === "week"
                                  ? "This Week"
                                  : "This Month"
                        }}
                    </button>
                </div>

                <!-- Custom range -->
                <div class="flex items-center gap-2 mb-4">
                    <input
                        type="date"
                        v-model="fromDate"
                        @change="onCustomRange"
                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    />
                    <span class="text-gray-400 text-sm">to</span>
                    <input
                        type="date"
                        v-model="toDate"
                        @change="onCustomRange"
                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    />
                </div>

                <!-- History loading -->
                <div v-if="loadingHistory" class="text-center py-10">
                    <i
                        class="pi pi-spin pi-spinner text-indigo-500 text-2xl"
                    ></i>
                </div>

                <!-- History empty -->
                <div
                    v-else-if="history.length === 0"
                    class="text-center py-10 bg-white rounded-lg shadow-sm text-gray-400"
                >
                    No memberships in this range.
                </div>

                <!-- History list -->
                <div v-else class="space-y-2">
                    <div
                        v-for="m in history"
                        :key="m.id"
                        class="bg-white shadow-sm rounded-lg p-4 flex items-center gap-3"
                    >
                        <div
                            class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm flex-shrink-0"
                        >
                            {{ m.user?.name?.[0] ?? "?" }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-800">
                                {{ m.user?.name ?? "Unknown" }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{
                                    m.type === "level_2" ? "Level 2" : "Level 3"
                                }}
                                · applied {{ formatDate(m.created_at) }}
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span
                                :class="
                                    m.source === 'gifted'
                                        ? 'bg-purple-100 text-purple-700'
                                        : 'bg-blue-100 text-blue-700'
                                "
                                class="text-xs px-2 py-0.5 rounded-full font-medium"
                            >
                                {{
                                    m.source === "gifted" ? "Gifted" : "Applied"
                                }}
                            </span>
                            <span
                                :class="
                                    m.status === 'approved'
                                        ? 'bg-green-100 text-green-700'
                                        : m.status === 'rejected'
                                          ? 'bg-red-100 text-red-600'
                                          : m.status === 'expired'
                                            ? 'bg-gray-100 text-gray-500'
                                            : 'bg-yellow-100 text-yellow-700'
                                "
                                class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                            >
                                {{ m.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valid ID Modal -->
            <div
                v-if="idModalUser"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                @click.self="idModalUser = null"
            >
                <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Valid ID — {{ idModalUser.name }}
                    </h3>
                    <img
                        :src="`/storage/${idModalUser.valid_id}`"
                        alt="Valid ID"
                        class="w-full rounded-lg object-contain max-h-80"
                    />
                    <button
                        @click="idModalUser = null"
                        class="mt-4 w-full px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>
<script>
import axios from "axios";

export default {
    name: "AdminMembershipManagement",
    data() {
        return {
            memberships: [],
            loading: true,
            processingIds: [],
            history: [],
            loadingHistory: false,
            revenue: 0,
            fromDate: this.localDate(new Date()),
            toDate: this.localDate(new Date()),
            rangePreset: "today",
            activeTab: "pending",
            active: [],
            loadingActive: false,
            idModalUser: null,
        };
    },
    methods: {
        setupAxiosToken() {
            const token = localStorage.getItem("auth-token");
            if (token)
                axios.defaults.headers.common["Authorization"] =
                    `Bearer ${token}`;
        },
        formatDate(d) {
            if (!d) return "";
            return new Date(d).toLocaleDateString("en-PH", {
                month: "short",
                day: "numeric",
                year: "numeric",
            });
        },
        async fetchActive() {
            this.loadingActive = true;
            try {
                const { data } = await axios.get(
                    "/api/admin/memberships/active",
                );
                this.active = data.memberships || [];
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingActive = false;
            }
        },
        switchToActive() {
            this.activeTab = "active";
            if (this.active.length === 0) this.fetchActive();
        },
        localDate(d) {
            const off = d.getTimezoneOffset() * 60000;
            return new Date(d.getTime() - off).toISOString().split("T")[0];
        },
        async fetchHistory() {
            this.loadingHistory = true;
            try {
                const { data } = await axios.get(
                    `/api/admin/memberships/history?from=${this.fromDate}&to=${this.toDate}`,
                );
                this.history = data.memberships || [];
                this.revenue = data.revenue || 0;
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingHistory = false;
            }
        },
        applyPreset(preset) {
            const now = new Date();
            let from = this.localDate(now);
            const to = this.localDate(now);
            if (preset === "week") {
                const day = now.getDay();
                const diff = day === 0 ? 6 : day - 1;
                const monday = new Date(now);
                monday.setDate(now.getDate() - diff);
                from = this.localDate(monday);
            } else if (preset === "month") {
                from = this.localDate(
                    new Date(now.getFullYear(), now.getMonth(), 1),
                );
            }
            this.rangePreset = preset;
            this.fromDate = from;
            this.toDate = to;
            this.fetchHistory();
        },
        onCustomRange() {
            this.rangePreset = "custom";
            this.fetchHistory();
        },
        async fetchMemberships() {
            this.loading = true;
            try {
                const { data } = await axios.get(
                    "/api/admin/memberships/pending",
                );
                this.memberships = data.memberships || [];
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
        async approveMembership(item) {
            this.processingIds.push(item.id);
            try {
                await axios.post(`/api/admin/memberships/${item.id}/approve`);
                this.memberships = this.memberships.filter(
                    (x) => x.id !== item.id,
                );
            } catch (e) {
                alert(e.response?.data?.message || "Failed to approve");
            } finally {
                this.processingIds = this.processingIds.filter(
                    (id) => id !== item.id,
                );
            }
        },
        async rejectMembership(item) {
            if (!confirm(`Reject membership for ${item.user?.name}?`)) return;
            this.processingIds.push(item.id);
            try {
                await axios.post(`/api/admin/memberships/${item.id}/reject`);
                this.memberships = this.memberships.filter(
                    (x) => x.id !== item.id,
                );
            } catch (e) {
                alert(e.response?.data?.message || "Failed to reject");
            } finally {
                this.processingIds = this.processingIds.filter(
                    (id) => id !== item.id,
                );
            }
        },
    },
    computed: {
        rangeLabel() {
            if (this.rangePreset === "today") return "Today's";
            if (this.rangePreset === "week") return "This week's";
            if (this.rangePreset === "month") return "This month's";
            return "Range";
        },
    },
    async mounted() {
        this.setupAxiosToken();
        await this.fetchMemberships();
        await this.fetchActive();
        await this.fetchHistory();
    },
};
</script>
