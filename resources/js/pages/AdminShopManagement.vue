<template>
    <Layout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    Store Management
                </h1>
                <p class="text-gray-600">
                    Manage store verification and active status
                </p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg
                                class="w-8 h-8 text-green-600"
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
                            <h3 class="text-lg font-semibold text-green-800">
                                {{ verifiedCount }}
                            </h3>
                            <p class="text-green-600">Verified Stores</p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-yellow-50 border border-yellow-200 rounded-lg p-4"
                >
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg
                                class="w-8 h-8 text-yellow-600"
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
                            <h3 class="text-lg font-semibold text-yellow-800">
                                {{ unverifiedCount }}
                            </h3>
                            <p class="text-yellow-600">Unverified Stores</p>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg
                                class="w-8 h-8 text-blue-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-blue-800">
                                {{ totalCount }}
                            </h3>
                            <p class="text-blue-600">Total Stores</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="flex space-x-1 rounded-lg bg-gray-100 p-1 mb-6">
                <button
                    @click="activeTab = 'all'"
                    :class="
                        activeTab === 'all'
                            ? 'bg-white text-gray-900 shadow'
                            : 'text-gray-600 hover:text-gray-900'
                    "
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition-colors"
                >
                    All ({{ totalCount }})
                </button>
                <button
                    @click="activeTab = 'unverified'"
                    :class="
                        activeTab === 'unverified'
                            ? 'bg-white text-gray-900 shadow'
                            : 'text-gray-600 hover:text-gray-900'
                    "
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition-colors"
                >
                    Unverified ({{ unverifiedCount }})
                </button>
                <button
                    @click="activeTab = 'verified'"
                    :class="
                        activeTab === 'verified'
                            ? 'bg-white text-gray-900 shadow'
                            : 'text-gray-600 hover:text-gray-900'
                    "
                    class="flex-1 py-2 px-3 rounded-md text-sm font-medium transition-colors"
                >
                    Verified ({{ verifiedCount }})
                </button>
            </div>

            <!-- Loading -->
            <div v-if="isLoading" class="text-center py-12">
                <i
                    class="pi pi-spin pi-spinner text-blue-500 text-3xl mb-3"
                ></i>
                <p class="text-gray-500">Loading stores...</p>
            </div>

            <!-- Error -->
            <div
                v-else-if="error"
                class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"
            >
                <p class="text-red-600">{{ error }}</p>
                <button
                    @click="fetchShops"
                    class="mt-2 text-sm text-red-700 underline"
                >
                    Try again
                </button>
            </div>

            <!-- Empty -->
            <div
                v-else-if="filteredShops.length === 0"
                class="text-center py-12 text-gray-500"
            >
                <i class="pi pi-building text-4xl mb-3 block text-gray-300"></i>
                <p>No stores found.</p>
            </div>

            <!-- Table -->
            <div v-else class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Store
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Owner
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Items
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="shop in filteredShops"
                            :key="shop.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img
                                        v-if="shop.logo_url"
                                        :src="shop.logo_url"
                                        class="h-10 w-10 rounded-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="h-10 w-10 rounded-full bg-teal-100 flex items-center justify-center"
                                    >
                                        <span
                                            class="text-teal-600 font-bold text-sm"
                                            >{{ shop.name[0] }}</span
                                        >
                                    </div>
                                    <div class="ml-4">
                                        <div
                                            class="text-sm font-medium text-gray-900"
                                        >
                                            {{ shop.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID #{{ shop.id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ shop.owner?.name ?? "—" }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ shop.active_items_count ?? 0 }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <span
                                        :class="
                                            shop.is_verified
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-yellow-100 text-yellow-800'
                                        "
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full w-fit"
                                    >
                                        {{
                                            shop.is_verified
                                                ? "Verified"
                                                : "Unverified"
                                        }}
                                    </span>
                                    <span
                                        :class="
                                            shop.is_active
                                                ? 'bg-blue-100 text-blue-800'
                                                : 'bg-red-100 text-red-800'
                                        "
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full w-fit"
                                    >
                                        {{
                                            shop.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}
                                    </span>
                                </div>
                            </td>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                            >
                                <div class="flex gap-2">
                                    <button
                                        @click="toggleVerification(shop)"
                                        :disabled="shop.toggling"
                                        :class="
                                            shop.is_verified
                                                ? 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-500'
                                                : 'text-white bg-green-600 hover:bg-green-700 focus:ring-green-500'
                                        "
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <i
                                            v-if="shop.toggling"
                                            class="pi pi-spin pi-spinner mr-1"
                                        ></i>
                                        <i
                                            v-else-if="shop.is_verified"
                                            class="pi pi-times mr-1"
                                        ></i>
                                        <i v-else class="pi pi-check mr-1"></i>
                                        {{
                                            shop.toggling
                                                ? "Saving..."
                                                : shop.is_verified
                                                  ? "Unverify"
                                                  : "Verify"
                                        }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="pagination && totalCount > 0"
                class="flex items-center justify-between mt-4 text-sm text-gray-500"
            >
                <span
                    >Showing {{ pagination.from }}–{{ pagination.to }} of
                    {{ pagination.total }}</span
                >
                <div class="flex gap-2">
                    <button
                        @click="fetchShops(pagination.current_page - 1)"
                        :disabled="!pagination.prev_page_url"
                        class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>
                    <button
                        @click="fetchShops(pagination.current_page + 1)"
                        :disabled="!pagination.next_page_url"
                        class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
import axios from "axios";

export default {
    name: "AdminShopManagement",
    data() {
        return {
            shops: [],
            pagination: null,
            isLoading: false,
            error: null,
            activeTab: "all",
        };
    },
    computed: {
        filteredShops() {
            if (this.activeTab === "verified")
                return this.shops.filter((s) => s.is_verified);
            if (this.activeTab === "unverified")
                return this.shops.filter((s) => !s.is_verified);
            return this.shops;
        },
        verifiedCount() {
            return this.shops.filter((s) => s.is_verified).length;
        },
        unverifiedCount() {
            return this.shops.filter((s) => !s.is_verified).length;
        },
        totalCount() {
            return this.shops.length;
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
        async fetchShops(page = 1) {
            this.isLoading = true;
            this.error = null;
            try {
                if (!this.setupAxiosToken()) throw new Error("No auth token");
                const { data } = await axios.get(
                    `/api/admin/shops?page=${page}`,
                );
                this.shops = (data.shops.data || data.shops).map((s) => ({
                    ...s,
                    toggling: false,
                }));
                this.pagination = data.shops.data ? data.shops : null;
            } catch (err) {
                this.error = "Failed to load stores.";
                console.error(err);
            } finally {
                this.isLoading = false;
            }
        },
        async toggleVerification(shop) {
            shop.toggling = true;
            try {
                const { data } = await axios.post(
                    `/api/admin/shops/${shop.id}/verify`,
                    {},
                );
                if (data.success) shop.is_verified = data.shop.is_verified;
            } catch (err) {
                console.error(err);
            } finally {
                shop.toggling = false;
            }
        },
    },
    mounted() {
        this.fetchShops();
    },
};
</script>
