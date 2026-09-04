<template>
    <div>
        <h3 class="text-lg font-semibold mb-6">Shop Settings</h3>

        <!-- Current Shop Images Preview -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Current Logo -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Current Logo
                </label>
                <div
                    class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center"
                >
                    <img
                        v-if="shop.logo_url"
                        :src="shop.logo_url"
                        alt="Shop Logo"
                        class="w-20 h-20 object-cover rounded-lg mx-auto"
                    />
                    <div v-else class="text-gray-400">
                        <i class="pi pi-image text-3xl mb-2"></i>
                        <p class="text-sm">No logo uploaded</p>
                    </div>
                </div>
            </div>

            <!-- Current Banner -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Current Banner
                </label>
                <div
                    class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center"
                >
                    <img
                        v-if="shop.banner_url"
                        :src="shop.banner_url"
                        alt="Shop Banner"
                        class="w-full h-20 object-cover rounded-lg"
                    />
                    <div v-else class="text-gray-400">
                        <i class="pi pi-image text-3xl mb-2"></i>
                        <p class="text-sm">No banner uploaded</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Discount Settings (read-only) -->
        <div class="mb-6 bg-purple-50 border border-purple-100 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-md font-semibold text-purple-800">
                    <i class="pi pi-percentage mr-1"></i>
                    Discount Settings
                </h4>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">
                        Store Discount
                    </p>
                    <p class="text-2xl font-bold text-purple-700 mt-1">
                        {{ shop.store_discount_percent || 0 }}%
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Comes out of your earnings
                    </p>
                </div>
                <div class="bg-white rounded-lg p-3 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">
                        Level Lounge Discount
                    </p>
                    <p class="text-2xl font-bold text-purple-700 mt-1">
                        {{ shop.admin_discount_percent || 0 }}%
                    </p>
                    <p class="text-xs text-gray-400 mt-1">
                        Covered by Level Lounge
                    </p>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3">
                <i class="pi pi-info-circle mr-1"></i>
                These rates are set by Level Lounge and apply to app purchases
                made with balance. To request a change, please contact the
                admin.
            </p>
        </div>

        <form @submit.prevent="updateShop" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Shop Name
                </label>
                <input
                    v-model="shopForm.name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea
                    v-model="shopForm.description"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                ></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Logo Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Shop Logo (Square recommended)
                    </label>
                    <input
                        ref="logoInput"
                        type="file"
                        accept="image/*"
                        @change="handleLogoUpload"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                    <p class="text-xs text-gray-500 mt-1">Max 2MB, JPG/PNG</p>
                </div>

                <!-- Banner Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Shop Banner (16:9 recommended)
                    </label>
                    <input
                        ref="bannerInput"
                        type="file"
                        accept="image/*"
                        @change="handleBannerUpload"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    />
                    <p class="text-xs text-gray-500 mt-1">Max 4MB, JPG/PNG</p>
                </div>
            </div>

            <button
                type="submit"
                :disabled="updatingShop"
                class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors disabled:opacity-50"
            >
                <i v-if="updatingShop" class="pi pi-spin pi-spinner mr-2"></i>
                Update Shop
            </button>
        </form>
    </div>
</template>

<script>
export default {
    name: "SettingsTab",
    props: {
        shop: { type: Object, required: true },
    },
    // Tells the parent the shop object was updated, passing the fresh shop
    emits: ["shop-updated"],
    data() {
        return {
            shopForm: {
                name: this.shop.name,
                description: this.shop.description || "",
                logo: null,
                banner: null,
            },
            updatingShop: false,
        };
    },
    methods: {
        async updateShop() {
            try {
                this.updatingShop = true;
                const formData = new FormData();

                formData.append("name", this.shopForm.name);
                formData.append("description", this.shopForm.description || "");

                if (this.shopForm.logo) {
                    formData.append("logo", this.shopForm.logo);
                }

                if (this.shopForm.banner) {
                    formData.append("banner", this.shopForm.banner);
                }

                const response = await axios.post(
                    `/api/shops/${this.shop.id}`,
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                            "X-HTTP-Method-Override": "PUT",
                        },
                    },
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Shop updated successfully",
                    });

                    this.shopForm.logo = null;
                    this.shopForm.banner = null;
                    this.$refs.logoInput.value = "";
                    this.$refs.bannerInput.value = "";
                    this.$emit("shop-updated", response.data.shop);
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to update shop",
                });
            } finally {
                this.updatingShop = false;
            }
        },

        handleLogoUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.shopForm.logo = file;
            }
        },

        handleBannerUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.shopForm.banner = file;
            }
        },
    },
};
</script>
