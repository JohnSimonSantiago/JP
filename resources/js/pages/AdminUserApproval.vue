<template>
    <Layout>
        <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                User Approval Management
            </h2>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
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
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"
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

            <!-- Users Table -->
            <div v-else class="overflow-x-auto">
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
                                        :src="
                                            user.profile_image ||
                                            'https://via.placeholder.com/40'
                                        "
                                        :alt="user.name"
                                        class="h-10 w-10 rounded-full object-cover"
                                    />
                                    <div class="ml-4">
                                        <div
                                            class="text-sm font-medium text-gray-900 dark:text-white"
                                        >
                                            {{ user.name }}
                                        </div>
                                        <div
                                            class="text-sm text-gray-500 dark:text-gray-400"
                                        >
                                            {{ user.email || "No email" }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white"
                            >
                                {{ formatDate(user.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="getRoleColor(user.role)"
                                >
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="
                                        user.is_approved
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400'
                                    "
                                >
                                    {{
                                        user.is_approved
                                            ? "Approved"
                                            : "Pending"
                                    }}
                                </span>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2"
                            >
                                <button
                                    v-if="!user.is_approved"
                                    @click="approveUser(user)"
                                    :disabled="processingUsers[user.id]"
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                                >
                                    <svg
                                        v-if="!processingUsers[user.id]"
                                        class="w-4 h-4 mr-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>
                                    <div v-else class="w-4 h-4 mr-1">
                                        <div
                                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
                                        ></div>
                                    </div>
                                    Approve
                                </button>

                                <button
                                    v-if="user.is_approved"
                                    @click="revokeApproval(user)"
                                    :disabled="processingUsers[user.id]"
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                                >
                                    <svg
                                        v-if="!processingUsers[user.id]"
                                        class="w-4 h-4 mr-1"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                    <div v-else class="w-4 h-4 mr-1">
                                        <div
                                            class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"
                                        ></div>
                                    </div>
                                    Revoke
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Empty State -->
                <div v-if="filteredUsers.length === 0" class="text-center py-8">
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
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
                    <h3
                        class="mt-4 text-lg font-medium text-gray-900 dark:text-white"
                    >
                        No {{ activeTab }} users found
                    </h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        {{
                            activeTab === "pending"
                                ? "All users have been reviewed."
                                : "No approved users yet."
                        }}
                    </p>
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
        async fetchUsers() {
            this.isLoading = true;
            try {
                const response = await axios.get("/admin/users");
                this.users = response.data.users;
            } catch (error) {
                console.error("Error fetching users:", error);
                this.$toast.error("Failed to load users");
            } finally {
                this.isLoading = false;
            }
        },

        async approveUser(user) {
            this.$set(this.processingUsers, user.id, true);

            try {
                await axios.post(`/admin/users/${user.id}/approve`);

                // Update local state
                const userIndex = this.users.findIndex((u) => u.id === user.id);
                if (userIndex !== -1) {
                    this.$set(this.users[userIndex], "is_approved", true);
                }

                this.$toast.success(`${user.name} has been approved`);
            } catch (error) {
                console.error("Error approving user:", error);
                this.$toast.error("Failed to approve user");
            } finally {
                this.$delete(this.processingUsers, user.id);
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

            this.$set(this.processingUsers, user.id, true);

            try {
                await axios.post(`/admin/users/${user.id}/revoke`);

                // Update local state
                const userIndex = this.users.findIndex((u) => u.id === user.id);
                if (userIndex !== -1) {
                    this.$set(this.users[userIndex], "is_approved", false);
                }

                this.$toast.success(`Approval revoked for ${user.name}`);
            } catch (error) {
                console.error("Error revoking approval:", error);
                this.$toast.error("Failed to revoke approval");
            } finally {
                this.$delete(this.processingUsers, user.id);
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

    mounted() {
        this.fetchUsers();
    },
};
</script>
