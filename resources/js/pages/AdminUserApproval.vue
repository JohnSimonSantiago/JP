<template>
    <Layout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1
                    class="text-3xl font-bold text-gray-900 dark:text-white mb-2"
                >
                    User Management
                </h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Manage user registrations and approvals
                </p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg
                                class="w-8 h-8 text-yellow-600 dark:text-yellow-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3
                                class="text-lg font-semibold text-yellow-800 dark:text-yellow-200"
                            >
                                {{ pendingCount }}
                            </h3>
                            <p class="text-yellow-600 dark:text-yellow-400">
                                Pending Approval
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg
                                class="w-8 h-8 text-green-600 dark:text-green-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3
                                class="text-lg font-semibold text-green-800 dark:text-green-200"
                            >
                                {{ approvedCount }}
                            </h3>
                            <p class="text-green-600 dark:text-green-400">
                                Approved Users
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg
                                class="w-8 h-8 text-blue-600 dark:text-blue-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3
                                class="text-lg font-semibold text-blue-800 dark:text-blue-200"
                            >
                                {{ totalCount }}
                            </h3>
                            <p class="text-blue-600 dark:text-blue-400">
                                Total Users
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div
                class="flex space-x-1 rounded-lg bg-gray-100 dark:bg-gray-700 p-1 mb-6"
            >
                <button
                    @click="activeTab = 'pending'"
                    :class="
                        activeTab === 'pending'
                            ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow'
                            : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'
                    "
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition-colors"
                >
                    Pending ({{ pendingCount }})
                </button>
                <button
                    @click="activeTab = 'approved'"
                    :class="
                        activeTab === 'approved'
                            ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow'
                            : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white'
                    "
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition-colors"
                >
                    Approved ({{ approvedCount }})
                </button>
            </div>

            <!-- Search + Sort -->
            <div class="flex flex-col sm:flex-row gap-3 mb-6">
                <div class="relative flex-1">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name or email..."
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <i
                        class="pi pi-search absolute right-3 top-2.5 text-gray-400"
                    ></i>
                </div>
                <button
                    @click="sortAlphabetical = !sortAlphabetical"
                    :class="
                        sortAlphabetical
                            ? 'bg-blue-600 text-white border-blue-600'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600'
                    "
                    class="flex items-center justify-center gap-2 px-4 py-2 rounded-lg border text-sm font-medium transition-colors"
                >
                    <i class="pi pi-sort-alpha-down"></i>
                    {{ sortAlphabetical ? "A → Z" : "Newest first" }}
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" class="flex justify-center items-center py-8">
                <div
                    class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"
                ></div>
                <span class="ml-2 text-gray-600 dark:text-gray-400"
                    >Loading users...</span
                >
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="text-center py-8">
                <div class="text-red-600 dark:text-red-400 mb-4">
                    <i class="pi pi-exclamation-triangle text-2xl"></i>
                </div>
                <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                >
                    Error Loading Users
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">{{ error }}</p>
                <button
                    @click="fetchUsers"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                >
                    Retry
                </button>
            </div>

            <!-- Users Table -->
            <div v-else-if="users.length > 0" class="flex gap-2">
                <div class="flex-1 overflow-x-auto">
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    User
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    Registration Date
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    Role
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"
                        >
                            <tr
                                v-for="user in filteredUsers"
                                :key="user.id"
                                :data-letter="
                                    (user.name || '').charAt(0).toUpperCase()
                                "
                                class="hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <img
                                            v-if="user.profile_image"
                                            :src="`/storage/profiles/${user.profile_image}`"
                                            :alt="user.name"
                                            class="h-10 w-10 rounded-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center"
                                        >
                                            <i
                                                class="pi pi-user text-gray-500 dark:text-gray-400"
                                            ></i>
                                        </div>
                                        <div class="ml-4">
                                            <div
                                                class="text-sm font-medium text-gray-900 dark:text-white"
                                            >
                                                {{ user.name }}
                                            </div>
                                            <div
                                                class="text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {{ user.email }}
                                            </div>
                                            <div
                                                class="flex items-center gap-2 mt-1"
                                            >
                                                <span
                                                    class="text-xs text-gray-500"
                                                >
                                                    Level {{ user.level || 1 }}
                                                </span>
                                                <span
                                                    class="text-xs text-gray-500"
                                                >
                                                    •
                                                </span>
                                                <span
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{ user.points || 0 }}
                                                    points
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div
                                        class="text-sm text-gray-900 dark:text-white"
                                    >
                                        {{ formatDate(user.created_at) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <!-- Role display for pending users or non-admin users -->
                                    <span
                                        v-if="
                                            !user.is_approved ||
                                            !isCurrentUserAdmin
                                        "
                                        :class="getRoleColor(user.role)"
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{
                                            user.role === "admin"
                                                ? "Administrator"
                                                : user.role === "shop_owner"
                                                  ? "Shop Owner"
                                                  : "User"
                                        }}
                                    </span>

                                    <!-- Role dropdown for approved users (admin only) -->
                                    <div v-else class="relative">
                                        <select
                                            v-model="user.role"
                                            @change="updateUserRole(user)"
                                            :disabled="
                                                processingUsers[user.id] ||
                                                user.id === currentUserId
                                            "
                                            :class="[
                                                getRoleColor(user.role),
                                                processingUsers[user.id]
                                                    ? 'opacity-50'
                                                    : '',
                                                user.id === currentUserId
                                                    ? 'cursor-not-allowed'
                                                    : 'cursor-pointer',
                                            ]"
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border-0 bg-transparent appearance-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                                        >
                                            <option value="user">User</option>
                                            <option value="shop_owner">
                                                Shop Owner
                                            </option>
                                            <option value="admin">
                                                Administrator
                                            </option>
                                        </select>
                                        <!-- Custom dropdown arrow -->
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center pr-1 pointer-events-none"
                                        >
                                            <svg
                                                class="w-3 h-3 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 9l-7 7-7-7"
                                                ></path>
                                            </svg>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="
                                            user.is_approved
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                                        "
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{
                                            user.is_approved
                                                ? "Approved"
                                                : "Pending"
                                        }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                >
                                    <div class="flex gap-2">
                                        <button
                                            v-if="user.valid_id"
                                            @click="idModalUser = user"
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-purple-700 bg-purple-100 hover:bg-purple-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
                                        >
                                            View ID
                                        </button>
                                        <button
                                            v-if="!user.is_approved"
                                            @click="approveUser(user)"
                                            :disabled="processingUsers[user.id]"
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <i
                                                v-if="processingUsers[user.id]"
                                                class="pi pi-spin pi-spinner mr-1"
                                            ></i>
                                            <i
                                                v-else
                                                class="pi pi-check mr-1"
                                            ></i>
                                            {{
                                                processingUsers[user.id]
                                                    ? "Approving..."
                                                    : "Approve"
                                            }}
                                        </button>
                                        <button
                                            v-if="!user.is_approved"
                                            @click="deleteUser(user)"
                                            :disabled="processingUsers[user.id]"
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <i
                                                v-if="processingUsers[user.id]"
                                                class="pi pi-spin pi-spinner mr-1"
                                            ></i>
                                            <i
                                                v-else
                                                class="pi pi-trash mr-1"
                                            ></i>
                                            {{
                                                processingUsers[user.id]
                                                    ? "Deleting..."
                                                    : "Delete"
                                            }}
                                        </button>
                                        <button
                                            v-if="
                                                user.is_approved &&
                                                user.role !== 'admin'
                                            "
                                            @click="revokeApproval(user)"
                                            :disabled="processingUsers[user.id]"
                                            class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <i
                                                v-if="processingUsers[user.id]"
                                                class="pi pi-spin pi-spinner mr-1"
                                            ></i>
                                            <i
                                                v-else
                                                class="pi pi-times mr-1"
                                            ></i>
                                            {{
                                                processingUsers[user.id]
                                                    ? "Revoking..."
                                                    : "Revoke"
                                            }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- A-Z jump strip -->
                <div class="flex flex-col text-base leading-snug pt-12">
                    <button
                        v-for="L in alphabet"
                        :key="L"
                        @click="scrollToLetter(L)"
                        :disabled="!availableLetters.has(L)"
                        :class="
                            availableLetters.has(L)
                                ? 'text-blue-600 hover:font-bold'
                                : 'text-gray-300 dark:text-gray-600 cursor-default'
                        "
                        class="px-1"
                    >
                        {{ L }}
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
                <div class="text-gray-400 dark:text-gray-600 mb-4">
                    <i class="pi pi-users text-6xl"></i>
                </div>
                <h3
                    class="text-lg font-semibold text-gray-900 dark:text-white mb-2"
                >
                    No Users Found
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    {{
                        activeTab === "pending"
                            ? "All users have been reviewed."
                            : "No approved users yet."
                    }}
                </p>
            </div>
            <!-- Valid ID Modal -->
            <div
                v-if="idModalUser"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                @click.self="idModalUser = null"
            >
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-lg w-full mx-4"
                >
                    <h3
                        class="text-lg font-semibold text-gray-900 dark:text-white mb-4"
                    >
                        Valid ID — {{ idModalUser.name }}
                    </h3>
                    <img
                        :src="`/storage/${idModalUser.valid_id}`"
                        alt="Valid ID"
                        class="w-full rounded-lg object-contain max-h-80"
                    />
                    <button
                        @click="idModalUser = null"
                        class="mt-4 w-full px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
export default {
    name: "AdminUserApproval",
    data() {
        return {
            users: [],
            activeTab: "pending",
            isLoading: false,
            error: null,
            processingUsers: {},
            idModalUser: null,
            currentUserId: null,
            isCurrentUserAdmin: false,
            searchQuery: "",
            sortAlphabetical: false,
            alphabet: "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split(""),
        };
    },
    computed: {
        filteredUsers() {
            let result = this.users.filter((user) => {
                if (this.activeTab === "pending") {
                    return !user.is_approved;
                } else {
                    return user.is_approved;
                }
            });

            const q = this.searchQuery.trim().toLowerCase();
            if (q) {
                result = result.filter(
                    (user) =>
                        user.name?.toLowerCase().includes(q) ||
                        user.email?.toLowerCase().includes(q),
                );
            }

            if (this.sortAlphabetical) {
                result = [...result].sort((a, b) =>
                    (a.name || "").localeCompare(b.name || ""),
                );
            }

            return result;
        },
        pendingCount() {
            return this.users.filter((user) => !user.is_approved).length;
        },
        approvedCount() {
            return this.users.filter((user) => user.is_approved).length;
        },
        totalCount() {
            return this.users.length;
        },
        availableLetters() {
            const set = new Set();
            this.filteredUsers.forEach((u) => {
                const c = (u.name || "").charAt(0).toUpperCase();
                if (c >= "A" && c <= "Z") set.add(c);
            });
            return set;
        },
    },
    methods: {
        setupAxiosToken() {
            const token = localStorage.getItem("auth-token");
            if (token) {
                axios.defaults.headers.common["Authorization"] =
                    `Bearer ${token}`;
                return true;
            }
            return false;
        },

        initializeCurrentUser() {
            const user = JSON.parse(localStorage.getItem("user") || "null");
            if (user) {
                this.currentUserId = user.id;
                this.isCurrentUserAdmin = user.role === "admin";
            }
        },

        async fetchUsers() {
            this.isLoading = true;
            this.error = null;

            try {
                if (!this.setupAxiosToken()) {
                    throw new Error("No authentication token found");
                }

                const response = await axios.get("/api/admin/users");

                if (response.data.success) {
                    this.users = response.data.users || [];
                } else {
                    throw new Error(
                        response.data.message || "Failed to fetch users",
                    );
                }
            } catch (error) {
                console.error("Error fetching users:", error);
                this.error =
                    error.response?.data?.message ||
                    error.message ||
                    "Failed to load users";

                // If unauthorized, redirect to login
                if (error.response?.status === 401) {
                    localStorage.removeItem("auth-token");
                    localStorage.removeItem("user");
                    this.$router.push("/");
                }
            } finally {
                this.isLoading = false;
            }
        },

        async updateUserRole(user) {
            // Prevent changing own role
            if (user.id === this.currentUserId) {
                if (this.$toast) {
                    this.$toast.add({
                        severity: "warn",
                        summary: "Warning",
                        detail: "You cannot change your own role",
                        life: 3000,
                    });
                } else {
                    alert("You cannot change your own role");
                }
                return;
            }

            // Store original role in case we need to revert
            const originalRole = this.users.find((u) => u.id === user.id).role;

            this.processingUsers[user.id] = true;

            try {
                if (!this.setupAxiosToken()) {
                    throw new Error("No authentication token found");
                }

                const response = await axios.post(
                    `/api/admin/users/${user.id}/update-role`,
                    { role: user.role },
                );

                if (response.data.success) {
                    // Update local state
                    const userIndex = this.users.findIndex(
                        (u) => u.id === user.id,
                    );
                    if (userIndex !== -1) {
                        this.users[userIndex].role = user.role;
                    }

                    // Show success toast if available
                    if (this.$toast) {
                        this.$toast.add({
                            severity: "success",
                            summary: "Success",
                            detail: `${
                                user.name
                            }'s role updated to ${this.getRoleDisplayName(
                                user.role,
                            )}`,
                            life: 3000,
                        });
                    }
                } else {
                    // Revert role change on failure
                    user.role = originalRole;
                    throw new Error(
                        response.data.message || "Failed to update user role",
                    );
                }
            } catch (error) {
                console.error("Error updating user role:", error);

                // Revert role change on error
                user.role = originalRole;

                const errorMessage =
                    error.response?.data?.message ||
                    error.message ||
                    "Failed to update user role";

                if (this.$toast) {
                    this.$toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: errorMessage,
                        life: 5000,
                    });
                } else {
                    alert(errorMessage);
                }
            } finally {
                delete this.processingUsers[user.id];
            }
        },

        getRoleDisplayName(role) {
            const roleNames = {
                admin: "Administrator",
                shop_owner: "Shop Owner",
                user: "User",
            };
            return roleNames[role] || "User";
        },

        async approveUser(user) {
            // Vue 3 compatible: Use reactive object assignment
            this.processingUsers[user.id] = true;

            try {
                if (!this.setupAxiosToken()) {
                    throw new Error("No authentication token found");
                }

                const response = await axios.post(
                    `/api/admin/users/${user.id}/approve`,
                );

                if (response.data.success) {
                    // Update local state - Vue 3 compatible
                    const userIndex = this.users.findIndex(
                        (u) => u.id === user.id,
                    );
                    if (userIndex !== -1) {
                        this.users[userIndex].is_approved = true;
                    }

                    // Show success toast if available
                    if (this.$toast) {
                        this.$toast.add({
                            severity: "success",
                            summary: "Success",
                            detail: `${user.name} has been approved`,
                            life: 3000,
                        });
                    }
                } else {
                    throw new Error(
                        response.data.message || "Failed to approve user",
                    );
                }
            } catch (error) {
                console.error("Error approving user:", error);
                const errorMessage =
                    error.response?.data?.message ||
                    error.message ||
                    "Failed to approve user";

                if (this.$toast) {
                    this.$toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: errorMessage,
                        life: 5000,
                    });
                } else {
                    alert(errorMessage);
                }
            } finally {
                // Vue 3 compatible: Use delete operator
                delete this.processingUsers[user.id];
            }
        },

        async revokeApproval(user) {
            if (
                !confirm(
                    `Are you sure you want to revoke approval for ${user.name}? They will no longer be able to log in.`,
                )
            ) {
                return;
            }

            // Vue 3 compatible: Use reactive object assignment
            this.processingUsers[user.id] = true;

            try {
                if (!this.setupAxiosToken()) {
                    throw new Error("No authentication token found");
                }

                const response = await axios.post(
                    `/api/admin/users/${user.id}/revoke`,
                );

                if (response.data.success) {
                    // Update local state - Vue 3 compatible
                    const userIndex = this.users.findIndex(
                        (u) => u.id === user.id,
                    );
                    if (userIndex !== -1) {
                        this.users[userIndex].is_approved = false;
                    }

                    // Show success toast if available
                    if (this.$toast) {
                        this.$toast.add({
                            severity: "success",
                            summary: "Success",
                            detail: `Approval revoked for ${user.name}`,
                            life: 3000,
                        });
                    }
                } else {
                    throw new Error(
                        response.data.message || "Failed to revoke approval",
                    );
                }
            } catch (error) {
                console.error("Error revoking approval:", error);
                const errorMessage =
                    error.response?.data?.message ||
                    error.message ||
                    "Failed to revoke approval";

                if (this.$toast) {
                    this.$toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: errorMessage,
                        life: 5000,
                    });
                } else {
                    alert(errorMessage);
                }
            } finally {
                // Vue 3 compatible: Use delete operator
                delete this.processingUsers[user.id];
            }
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        scrollToLetter(letter) {
            if (!this.availableLetters.has(letter)) return;
            this.$nextTick(() => {
                const target = document.querySelector(
                    `tr[data-letter="${letter}"]`,
                );
                if (target)
                    target.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
            });
        },

        async deleteUser(user) {
            if (
                !confirm(
                    `Permanently delete ${user.name}? This cannot be undone.`,
                )
            ) {
                return;
            }

            this.processingUsers[user.id] = true;

            try {
                if (!this.setupAxiosToken()) {
                    throw new Error("No authentication token found");
                }

                const response = await axios.delete(
                    `/api/admin/users/${user.id}`,
                );

                if (response.data.success) {
                    this.users = this.users.filter((u) => u.id !== user.id);

                    if (this.$toast) {
                        this.$toast.add({
                            severity: "success",
                            summary: "Success",
                            detail: `${user.name} has been deleted`,
                            life: 3000,
                        });
                    }
                } else {
                    throw new Error(
                        response.data.message || "Failed to delete user",
                    );
                }
            } catch (error) {
                console.error("Error deleting user:", error);
                const errorMessage =
                    error.response?.data?.message ||
                    error.message ||
                    "Failed to delete user";

                if (this.$toast) {
                    this.$toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: errorMessage,
                        life: 5000,
                    });
                } else {
                    alert(errorMessage);
                }
            } finally {
                delete this.processingUsers[user.id];
            }
        },

        getRoleColor(role) {
            const colors = {
                admin: "bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400",
                shop_owner:
                    "bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400",
                user: "bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300",
            };
            return colors[role] || colors["user"];
        },
    },

    async mounted() {
        this.initializeCurrentUser();
        await this.fetchUsers();
    },
};
</script>

<style scoped>
/* Custom select styling */
select {
    background-image: none;
}

select:focus {
    outline: none;
}

/* Hide default arrow in Firefox */
select {
    -moz-appearance: none;
}

/* Hide default arrow in Chrome/Safari */
select {
    -webkit-appearance: none;
    appearance: none;
}
</style>
