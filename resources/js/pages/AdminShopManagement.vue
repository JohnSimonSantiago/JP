<template>
    <Layout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-1">
                    Store Management
                </h1>
                <p class="text-gray-600">
                    Verification, discounts, and payouts
                </p>
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
                class="bg-red-50 border border-red-200 rounded-lg p-4"
            >
                <p class="text-red-600">{{ error }}</p>
                <button
                    @click="fetchShops"
                    class="mt-2 text-sm text-red-700 underline"
                >
                    Try again
                </button>
            </div>

            <!-- Main split layout -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- LEFT: shop list -->
                <div class="lg:col-span-1 space-y-2">
                    <button
                        v-for="shop in shops"
                        :key="shop.id"
                        @click="selectShop(shop)"
                        :class="[
                            'w-full text-left p-4 rounded-lg border transition-colors',
                            selectedShop && selectedShop.id === shop.id
                                ? 'border-indigo-400 bg-indigo-50'
                                : 'border-gray-200 bg-white hover:bg-gray-50',
                        ]"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center min-w-0">
                                <img
                                    v-if="shop.logo_url"
                                    :src="shop.logo_url"
                                    class="h-9 w-9 rounded-full object-cover flex-shrink-0"
                                />
                                <div
                                    v-else
                                    class="h-9 w-9 rounded-full bg-teal-100 flex items-center justify-center flex-shrink-0"
                                >
                                    <span
                                        class="text-teal-600 font-bold text-sm"
                                        >{{ shop.name[0] }}</span
                                    >
                                </div>
                                <div class="ml-3 min-w-0">
                                    <div
                                        class="text-sm font-medium text-gray-900 truncate"
                                    >
                                        {{ shop.name }}
                                    </div>
                                    <div class="text-xs text-gray-400 truncate">
                                        {{ shop.owner?.name || "—" }}
                                    </div>
                                </div>
                            </div>
                            <span
                                v-if="shop.payout_balance > 0"
                                class="ml-2 flex-shrink-0 px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-700"
                            >
                                ₱{{ Number(shop.payout_balance).toFixed(0) }}
                            </span>
                        </div>
                        <div class="flex gap-1 mt-2">
                            <span
                                :class="
                                    shop.is_verified
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-yellow-100 text-yellow-700'
                                "
                                class="px-2 py-0.5 text-xs font-medium rounded-full"
                            >
                                {{
                                    shop.is_verified ? "Verified" : "Unverified"
                                }}
                            </span>
                            <span
                                :class="
                                    shop.is_active
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-red-100 text-red-700'
                                "
                                class="px-2 py-0.5 text-xs font-medium rounded-full"
                            >
                                {{ shop.is_active ? "Active" : "Inactive" }}
                            </span>
                        </div>
                    </button>

                    <p
                        v-if="shops.length === 0"
                        class="text-center text-gray-400 py-8 text-sm"
                    >
                        No stores found.
                    </p>
                </div>

                <!-- RIGHT: detail panel -->
                <div class="lg:col-span-2">
                    <div
                        v-if="!selectedShop"
                        class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400"
                    >
                        <i
                            class="pi pi-building text-4xl mb-3 block text-gray-300"
                        ></i>
                        <p>Select a store to manage it.</p>
                    </div>

                    <div v-else class="space-y-6">
                        <!-- Verification card -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3
                                class="text-lg font-semibold text-gray-800 mb-4"
                            >
                                {{ selectedShop.name }}
                            </h3>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-700"
                                    >
                                        Verification
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        Verified stores are visible to customers
                                    </p>
                                </div>
                                <button
                                    @click="toggleVerification(selectedShop)"
                                    :disabled="selectedShop.toggling"
                                    :class="
                                        selectedShop.is_verified
                                            ? 'bg-red-600 hover:bg-red-700'
                                            : 'bg-green-600 hover:bg-green-700'
                                    "
                                    class="inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-lg disabled:opacity-50"
                                >
                                    <i
                                        v-if="selectedShop.toggling"
                                        class="pi pi-spin pi-spinner mr-2"
                                    ></i>
                                    {{
                                        selectedShop.is_verified
                                            ? "Unverify"
                                            : "Verify"
                                    }}
                                </button>
                            </div>
                        </div>

                        <!-- Discounts card -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3
                                class="text-lg font-semibold text-gray-800 mb-1"
                            >
                                Discounts
                            </h3>
                            <p class="text-xs text-gray-400 mb-4">
                                Admin % is shouldered by Level Lounge. Store %
                                is shouldered by the shop. Customers paying with
                                balance see the combined discount.
                            </p>
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Admin discount %</label
                                    >
                                    <input
                                        v-model.number="
                                            selectedShop.admin_discount_percent
                                        "
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-indigo-500"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Store discount %</label
                                    >
                                    <input
                                        v-model.number="
                                            selectedShop.store_discount_percent
                                        "
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-1 focus:ring-indigo-500"
                                    />
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-gray-500">
                                    Combined:
                                    <span
                                        :class="
                                            combinedDiscount > 100
                                                ? 'text-red-600 font-semibold'
                                                : 'text-gray-700'
                                        "
                                        >{{ combinedDiscount }}%</span
                                    >
                                </p>
                                <button
                                    @click="saveDiscounts(selectedShop)"
                                    :disabled="
                                        selectedShop.savingDiscount ||
                                        combinedDiscount > 100
                                    "
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg disabled:opacity-50"
                                >
                                    <i
                                        v-if="selectedShop.savingDiscount"
                                        class="pi pi-spin pi-spinner mr-2"
                                    ></i>
                                    Save discounts
                                </button>
                            </div>
                        </div>

                        <!-- Payouts card -->
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3
                                class="text-lg font-semibold text-gray-800 mb-4"
                            >
                                Payouts
                            </h3>

                            <div
                                v-if="payoutLoading"
                                class="flex justify-center py-6"
                            >
                                <i
                                    class="pi pi-spin pi-spinner text-indigo-500 text-2xl"
                                ></i>
                            </div>

                            <template v-else>
                                <!-- Balance -->
                                <div
                                    class="bg-indigo-50 rounded-lg p-4 mb-4 text-center"
                                >
                                    <p
                                        class="text-xs text-indigo-500 uppercase tracking-wide"
                                    >
                                        Currently owed
                                    </p>
                                    <p
                                        class="text-3xl font-bold text-indigo-700 my-1"
                                    >
                                        ₱{{
                                            Number(
                                                payoutData.balance || 0,
                                            ).toFixed(2)
                                        }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{
                                            payoutData.claimable_orders || 0
                                        }}
                                        completed order{{
                                            (payoutData.claimable_orders ||
                                                0) === 1
                                                ? ""
                                                : "s"
                                        }}
                                    </p>
                                </div>

                                <!-- Pay -->
                                <div class="mb-6">
                                    <input
                                        v-model="payoutNote"
                                        type="text"
                                        placeholder="Note (optional, e.g. paid via GCash)"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-3 focus:ring-1 focus:ring-indigo-500"
                                    />
                                    <button
                                        @click="processPayout"
                                        :disabled="
                                            !payoutData.balance ||
                                            payoutData.balance <= 0 ||
                                            paying
                                        "
                                        class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <i
                                            v-if="paying"
                                            class="pi pi-spin pi-spinner mr-2"
                                        ></i>
                                        Mark ₱{{
                                            Number(
                                                payoutData.balance || 0,
                                            ).toFixed(2)
                                        }}
                                        as paid
                                    </button>
                                </div>

                                <!-- History -->
                                <h4
                                    class="text-sm font-semibold text-gray-700 mb-2"
                                >
                                    History
                                </h4>
                                <div
                                    v-if="
                                        payoutData.history &&
                                        payoutData.history.length
                                    "
                                    class="space-y-2"
                                >
                                    <div
                                        v-for="p in payoutData.history"
                                        :key="p.id"
                                        class="p-3 bg-gray-50 rounded-lg text-sm"
                                    >
                                        <div class="font-medium text-gray-800">
                                            ₱{{ Number(p.amount).toFixed(2) }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            {{
                                                new Date(
                                                    p.created_at,
                                                ).toLocaleDateString()
                                            }}
                                            · {{ p.order_count }} orders
                                            <span v-if="p.processed_by"
                                                >·
                                                {{ p.processed_by.name }}</span
                                            >
                                        </div>
                                        <div
                                            v-if="p.note"
                                            class="text-xs text-gray-500 italic mt-0.5"
                                        >
                                            {{ p.note }}
                                        </div>
                                    </div>
                                </div>
                                <p
                                    v-else
                                    class="text-sm text-gray-400 text-center py-3"
                                >
                                    No payouts yet
                                </p>
                            </template>
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
    name: "AdminShopManagement",
    data() {
        return {
            shops: [],
            selectedShop: null,
            isLoading: false,
            error: null,

            // Payouts (for the selected shop)
            payoutLoading: false,
            payoutData: {},
            payoutNote: "",
            paying: false,
        };
    },
    computed: {
        combinedDiscount() {
            if (!this.selectedShop) return 0;
            return (
                (Number(this.selectedShop.admin_discount_percent) || 0) +
                (Number(this.selectedShop.store_discount_percent) || 0)
            );
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

        async fetchShops() {
            this.isLoading = true;
            this.error = null;
            try {
                if (!this.setupAxiosToken()) throw new Error("No auth token");
                const { data } = await axios.get("/api/admin/shops");
                this.shops = (data.shops.data || data.shops).map((s) => ({
                    ...s,
                    toggling: false,
                    savingDiscount: false,
                }));
            } catch (err) {
                this.error = "Failed to load stores.";
                console.error(err);
            } finally {
                this.isLoading = false;
            }
        },

        async selectShop(shop) {
            this.selectedShop = shop;
            this.payoutNote = "";
            await this.fetchPayouts(shop);
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

        async saveDiscounts(shop) {
            const admin = Number(shop.admin_discount_percent) || 0;
            const store = Number(shop.store_discount_percent) || 0;
            if (admin + store > 100) {
                this.$toast?.add({
                    severity: "warn",
                    summary: "Invalid",
                    detail: "Combined discount can't exceed 100%",
                });
                return;
            }
            shop.savingDiscount = true;
            try {
                const { data } = await axios.post(
                    `/api/admin/shops/${shop.id}/discounts`,
                    {
                        admin_discount_percent: admin,
                        store_discount_percent: store,
                    },
                );
                if (data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Saved",
                        detail: `Discounts updated for ${shop.name}`,
                    });
                }
            } catch (err) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        err.response?.data?.message ||
                        "Failed to save discounts",
                });
            } finally {
                shop.savingDiscount = false;
            }
        },

        async fetchPayouts(shop) {
            this.payoutLoading = true;
            this.payoutData = {};
            try {
                const { data } = await axios.get(
                    `/api/shops/${shop.id}/items/payouts`,
                );
                if (data.success) this.payoutData = data;
            } catch (err) {
                console.error(err);
            } finally {
                this.payoutLoading = false;
            }
        },

        async processPayout() {
            if (
                !this.selectedShop ||
                !this.payoutData.balance ||
                this.payoutData.balance <= 0
            )
                return;
            this.paying = true;
            try {
                const { data } = await axios.post(
                    `/api/admin/shops/${this.selectedShop.id}/payout`,
                    {
                        note: this.payoutNote || null,
                    },
                );
                if (data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Paid",
                        detail: data.message,
                    });
                    // Refresh balance for this shop, and clear the list badge
                    await this.fetchPayouts(this.selectedShop);
                    this.selectedShop.payout_balance = 0;
                }
            } catch (err) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: err.response?.data?.message || "Payout failed",
                });
            } finally {
                this.paying = false;
            }
        },
    },
    mounted() {
        this.fetchShops();
    },
};
</script>
