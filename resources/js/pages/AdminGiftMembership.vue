<template>
    <Layout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Gift Membership
                </h1>
                <p class="text-gray-600">
                    Manually grant a membership to any user
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Gift Form -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">
                        Grant Membership
                    </h2>

                    <!-- Success -->
                    <div
                        v-if="successMessage"
                        class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4 flex items-center gap-3"
                    >
                        <i
                            class="pi pi-check-circle text-green-600 text-xl"
                        ></i>
                        <p class="text-green-700 text-sm font-medium">
                            {{ successMessage }}
                        </p>
                    </div>

                    <!-- Error -->
                    <div
                        v-if="errorMessage"
                        class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4 flex items-center gap-3"
                    >
                        <i
                            class="pi pi-exclamation-circle text-red-600 text-xl"
                        ></i>
                        <p class="text-red-700 text-sm font-medium">
                            {{ errorMessage }}
                        </p>
                    </div>

                    <!-- User Search -->
                    <div class="mb-5">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Search User</label
                        >
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                @input="searchUsers"
                                type="text"
                                placeholder="Type username..."
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                            />
                            <i
                                class="pi pi-search absolute right-3 top-2.5 text-gray-400"
                            ></i>
                        </div>

                        <!-- Dropdown results -->
                        <div
                            v-if="searchResults.length > 0 && !selectedUser"
                            class="mt-1 border border-gray-200 rounded-lg shadow-md bg-white max-h-48 overflow-y-auto z-10"
                        >
                            <div
                                v-for="user in searchResults"
                                :key="user.id"
                                @click="selectUser(user)"
                                class="flex items-center gap-3 px-4 py-2 hover:bg-yellow-50 cursor-pointer text-sm"
                            >
                                <img
                                    v-if="user.profile_image"
                                    :src="`/storage/profiles/${user.profile_image}`"
                                    class="w-7 h-7 rounded-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-7 h-7 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 text-xs font-bold"
                                >
                                    {{ user.name[0] }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">
                                        {{ user.name }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        Level {{ user.level }} ·
                                        {{
                                            user.is_premium
                                                ? "Premium"
                                                : "Standard"
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected user chip -->
                        <div
                            v-if="selectedUser"
                            class="mt-2 flex items-center gap-3 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-2"
                        >
                            <img
                                v-if="selectedUser.profile_image"
                                :src="`/storage/profiles/${selectedUser.profile_image}`"
                                class="w-8 h-8 rounded-full object-cover"
                            />
                            <div
                                v-else
                                class="w-8 h-8 rounded-full bg-yellow-200 flex items-center justify-center text-yellow-700 font-bold text-sm"
                            >
                                {{ selectedUser.name[0] }}
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-800">
                                    {{ selectedUser.name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Currently Level {{ selectedUser.level }}
                                </div>
                            </div>
                            <button
                                @click="clearUser"
                                class="text-gray-400 hover:text-red-500 transition-colors"
                            >
                                <i class="pi pi-times text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Membership Level -->
                    <div class="mb-5">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Membership Level</label
                        >
                        <div class="grid grid-cols-2 gap-3">
                            <div
                                @click="form.type = 'level_2'"
                                :class="
                                    form.type === 'level_2'
                                        ? 'border-yellow-400 bg-yellow-50 ring-2 ring-yellow-300'
                                        : 'border-gray-200 hover:border-yellow-300'
                                "
                                class="cursor-pointer border rounded-lg p-4 transition-all"
                            >
                                <div
                                    class="text-sm font-semibold text-gray-800"
                                >
                                    Level 2
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Standard premium
                                </div>
                            </div>
                            <div
                                @click="form.type = 'level_3'"
                                :class="
                                    form.type === 'level_3'
                                        ? 'border-yellow-400 bg-yellow-50 ring-2 ring-yellow-300'
                                        : 'border-gray-200 hover:border-yellow-300'
                                "
                                class="cursor-pointer border rounded-lg p-4 transition-all"
                            >
                                <div
                                    class="text-sm font-semibold text-gray-800"
                                >
                                    Level 3
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Top tier premium
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Days -->
                    <div class="mb-6">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            >Duration (days)</label
                        >
                        <div class="flex gap-2 mb-2">
                            <button
                                @click="form.days = 7"
                                :class="
                                    form.days === 7
                                        ? 'bg-yellow-500 text-white'
                                        : 'bg-gray-100 text-gray-700'
                                "
                                class="px-3 py-1.5 rounded-lg text-xs font-medium"
                            >
                                7d
                            </button>
                            <button
                                @click="form.days = 30"
                                :class="
                                    form.days === 30
                                        ? 'bg-yellow-500 text-white'
                                        : 'bg-gray-100 text-gray-700'
                                "
                                class="px-3 py-1.5 rounded-lg text-xs font-medium"
                            >
                                30d
                            </button>
                            <button
                                @click="form.days = 90"
                                :class="
                                    form.days === 90
                                        ? 'bg-yellow-500 text-white'
                                        : 'bg-gray-100 text-gray-700'
                                "
                                class="px-3 py-1.5 rounded-lg text-xs font-medium"
                            >
                                90d
                            </button>
                            <button
                                @click="form.days = 365"
                                :class="
                                    form.days === 365
                                        ? 'bg-yellow-500 text-white'
                                        : 'bg-gray-100 text-gray-700'
                                "
                                class="px-3 py-1.5 rounded-lg text-xs font-medium"
                            >
                                1yr
                            </button>
                        </div>
                        <input
                            v-model.number="form.days"
                            type="number"
                            min="1"
                            max="3650"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                            placeholder="Or enter custom days"
                        />
                    </div>

                    <button
                        @click="giftMembership"
                        :disabled="
                            !selectedUser ||
                            !form.type ||
                            !form.days ||
                            submitting
                        "
                        class="w-full bg-yellow-500 hover:bg-yellow-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-semibold py-2.5 rounded-lg transition-colors flex items-center justify-center gap-2"
                    >
                        <i v-if="submitting" class="pi pi-spin pi-spinner"></i>
                        <i v-else class="pi pi-gift"></i>
                        {{ submitting ? "Gifting..." : "Gift Membership" }}
                    </button>
                </div>

                <!-- Recent Gifts -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        Recent Memberships
                    </h2>

                    <div v-if="loadingHistory" class="text-center py-8">
                        <i
                            class="pi pi-spin pi-spinner text-yellow-500 text-2xl"
                        ></i>
                    </div>

                    <div
                        v-else-if="recentMemberships.length === 0"
                        class="text-center py-8 text-gray-400"
                    >
                        <i class="pi pi-inbox text-3xl mb-2 block"></i>
                        No memberships yet.
                    </div>

                    <div v-else class="space-y-3 max-h-[500px] overflow-y-auto">
                        <div
                            v-for="m in recentMemberships"
                            :key="m.id"
                            class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50"
                        >
                            <div
                                class="w-9 h-9 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 font-bold text-sm flex-shrink-0"
                            >
                                {{ m.user?.name?.[0] ?? "?" }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-800">
                                    {{ m.user?.name }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{
                                        m.type === "level_2"
                                            ? "Level 2"
                                            : "Level 3"
                                    }}
                                    · {{ formatDate(m.start_date) }} →
                                    {{ formatDate(m.end_date) }}
                                </div>
                            </div>
                            <div
                                class="flex flex-col items-end gap-1 flex-shrink-0"
                            >
                                <span
                                    :class="
                                        m.source === 'gifted'
                                            ? 'bg-purple-100 text-purple-700'
                                            : 'bg-blue-100 text-blue-700'
                                    "
                                    class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                >
                                    {{
                                        m.source === "gifted"
                                            ? "Gifted"
                                            : "Applied"
                                    }}
                                </span>
                                <span
                                    :class="
                                        m.status === 'approved'
                                            ? 'bg-green-100 text-green-700'
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
            </div>
        </div>
    </Layout>
</template>

<script>
import axios from "axios";

export default {
    name: "AdminGiftMembership",
    data() {
        return {
            searchQuery: "",
            searchResults: [],
            selectedUser: null,
            form: { type: "level_2", days: 30 },
            submitting: false,
            successMessage: "",
            errorMessage: "",
            recentMemberships: [],
            loadingHistory: false,
            searchTimeout: null,
        };
    },
    methods: {
        setupAxiosToken() {
            const token = localStorage.getItem("auth-token");
            if (token)
                axios.defaults.headers.common["Authorization"] =
                    `Bearer ${token}`;
        },
        searchUsers() {
            clearTimeout(this.searchTimeout);
            this.selectedUser = null;
            if (!this.searchQuery.trim()) {
                this.searchResults = [];
                return;
            }
            this.searchTimeout = setTimeout(async () => {
                try {
                    const { data } = await axios.get(
                        `/api/users/search?q=${this.searchQuery}`,
                    );
                    this.searchResults = data.users || [];
                } catch (e) {
                    console.error(e);
                }
            }, 300);
        },
        selectUser(user) {
            this.selectedUser = user;
            this.searchQuery = user.name;
            this.searchResults = [];
        },
        clearUser() {
            this.selectedUser = null;
            this.searchQuery = "";
            this.searchResults = [];
        },
        formatDate(d) {
            if (!d) return "";
            return new Date(d).toLocaleDateString("en-PH", {
                month: "short",
                day: "numeric",
                year: "numeric",
            });
        },
        async giftMembership() {
            this.successMessage = "";
            this.errorMessage = "";
            this.submitting = true;
            try {
                const { data } = await axios.post(
                    "/api/admin/memberships/gift",
                    {
                        user_id: this.selectedUser.id,
                        type: this.form.type,
                        days: this.form.days,
                    },
                );
                if (data.success) {
                    this.successMessage = data.message;
                    this.clearUser();
                    this.form = { type: "level_2", days: 30 };
                    await this.fetchRecentMemberships();
                } else {
                    this.errorMessage =
                        data.message || "Failed to gift membership.";
                }
            } catch (e) {
                this.errorMessage =
                    e.response?.data?.message || "An error occurred.";
            } finally {
                this.submitting = false;
            }
        },
        async fetchRecentMemberships() {
            this.loadingHistory = true;
            try {
                const { data } = await axios.get("/api/admin/memberships");
                this.recentMemberships = data.memberships || [];
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingHistory = false;
            }
        },
    },
    async mounted() {
        this.setupAxiosToken();
        await this.fetchRecentMemberships();
    },
};
</script>
