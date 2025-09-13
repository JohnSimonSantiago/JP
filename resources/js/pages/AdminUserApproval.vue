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
            <div v-else-if="users.length > 0" class="overflow-x-auto">
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
                                            <span class="text-xs text-gray-500">
                                                Level {{ user.level || 1 }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                •
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ user.points || 0 }} points
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
                                <span
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
                                        v-if="!user.is_approved"
                                        @click="approveUser(user)"
                                        :disabled="processingUsers[user.id]"
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <i
                                            v-if="processingUsers[user.id]"
                                            class="pi pi-spin pi-spinner mr-1"
                                        ></i>
                                        <i v-else class="pi pi-check mr-1"></i>
                                        {{
                                            processingUsers[user.id]
                                                ? "Approving..."
                                                : "Approve"
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
                                        <i v-else class="pi pi-times mr-1"></i>
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
            processingUsers: {}, // Track which users are being processed
        };
    },
    computed: {
        filteredUsers() {
            return this.users.filter((user) => {
                if (this.activeTab === "pending") {
                    return !user.is_approved;
                } else {
                    return user.is_approved;
                }
            });
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
    },
    methods: {
        setupAxiosToken() {
            const token = localStorage.getItem("auth-token");
            if (token) {
                axios.defaults.headers.common[
                    "Authorization"
                ] = `Bearer ${token}`;
                return true;
            }
            return false;
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
                        response.data.message || "Failed to fetch users"
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

        async approveUser(user) {
            // Vue 3 compatible: Use reactive object assignment
            this.processingUsers[user.id] = true;

            try {
                if (!this.setupAxiosToken()) {
                    throw new Error("No authentication token found");
                }

                const response = await axios.post(
                    `/api/admin/users/${user.id}/approve`
                );

                if (response.data.success) {
                    // Update local state - Vue 3 compatible
                    const userIndex = this.users.findIndex(
                        (u) => u.id === user.id
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
                        response.data.message || "Failed to approve user"
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
                    `Are you sure you want to revoke approval for ${user.name}? They will no longer be able to log in.`
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
                    `/api/admin/users/${user.id}/revoke`
                );

                if (response.data.success) {
                    // Update local state - Vue 3 compatible
                    const userIndex = this.users.findIndex(
                        (u) => u.id === user.id
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
                        response.data.message || "Failed to revoke approval"
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
        await this.fetchUsers();
    },
};
</script>
