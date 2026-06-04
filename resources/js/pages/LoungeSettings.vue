<template>
    <Layout>
        <div class="max-w-md mx-auto py-2">
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-4"
            >
                <h2 class="text-lg font-bold text-gray-800 mb-1">
                    Lounge Pricing
                </h2>
                <p class="text-sm text-gray-500 mb-3">
                    These rates apply to Level 1 and walk-in customers.
                </p>

                <div v-if="loading" class="text-center py-8 text-gray-400">
                    <i class="pi pi-spin pi-spinner text-2xl"></i>
                </div>

                <div v-else>
                    <div class="space-y-3 mb-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Hourly Rate (₱)</label
                            >
                            <input
                                v-model="form.hourly_rate"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Bundle Rate (₱) — per
                                {{ form.bundle_hours }} hours
                            </label>
                            <input
                                v-model="form.bundle_rate"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Bundle Duration (hours)</label
                            >
                            <input
                                v-model="form.bundle_hours"
                                type="number"
                                min="1"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Day Rate (₱) — price ceiling</label
                            >
                            <input
                                v-model="form.day_rate"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            />
                        </div>
                    </div>

                    <!-- Preview -->
                    <div
                        class="bg-indigo-50 rounded-lg p-3 mb-4 text-sm text-indigo-800"
                    >
                        <p class="font-semibold mb-1">
                            Current pricing preview:
                        </p>
                        <p>1 hr = ₱{{ form.hourly_rate }}</p>
                        <p>
                            {{ form.bundle_hours }} hrs = ₱{{
                                form.bundle_rate
                            }}
                            (bundle)
                        </p>
                        <p>Full day = ₱{{ form.day_rate }} (max anyone pays)</p>
                    </div>

                    <div
                        v-if="message"
                        :class="success ? 'text-green-600' : 'text-red-600'"
                        class="text-sm mb-4"
                    >
                        {{ message }}
                    </div>

                    <button
                        @click="save"
                        :disabled="saving"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors disabled:opacity-50"
                    >
                        {{ saving ? "Saving..." : "Save Pricing" }}
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
export default {
    name: "LoungeSettings",
    data() {
        return {
            loading: true,
            saving: false,
            message: null,
            success: false,
            form: {
                hourly_rate: 40,
                bundle_rate: 100,
                bundle_hours: 3,
                day_rate: 200,
            },
        };
    },

    async mounted() {
        await this.fetchPricing();
    },

    methods: {
        async fetchPricing() {
            try {
                const token = localStorage.getItem("auth-token");
                axios.defaults.headers.common["Authorization"] =
                    `Bearer ${token}`;
                const res = await axios.get("/api/admin/lounge/pricing");
                if (res.data.success) {
                    this.form = { ...res.data.pricing };
                }
            } catch (e) {
                this.message =
                    "Failed to load pricing: " +
                    (e.response?.status || e.message);
                console.error(e.response?.data || e);
            } finally {
                this.loading = false;
            }
        },

        async save() {
            this.saving = true;
            this.message = null;
            try {
                const token = localStorage.getItem("auth-token");
                axios.defaults.headers.common["Authorization"] =
                    `Bearer ${token}`;
                const res = await axios.put(
                    "/api/admin/lounge/pricing",
                    this.form,
                );
                if (res.data.success) {
                    this.success = true;
                    this.message = "Pricing updated successfully.";
                }
            } catch (e) {
                this.success = false;
                this.message = "Failed to save pricing.";
            } finally {
                this.saving = false;
            }
        },
    },
};
</script>
