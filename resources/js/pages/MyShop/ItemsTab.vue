<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold">Shop Items</h3>
            <button
                @click="openCreate"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors"
            >
                <i class="pi pi-plus mr-2"></i>
                Add Item
            </button>
        </div>

        <!-- Items List -->
        <div v-if="items.length > 0" class="space-y-4">
            <div
                v-for="item in items"
                :key="item.id"
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50"
            >
                <div class="flex items-center space-x-4">
                    <img
                        v-if="item.image"
                        :src="`/storage/${item.image}`"
                        :alt="item.name"
                        class="w-16 h-16 object-cover rounded-lg"
                    />
                    <div
                        v-else
                        class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center"
                    >
                        <i class="pi pi-image text-gray-400"></i>
                    </div>
                    <div>
                        <h4 class="font-medium">{{ item.name }}</h4>
                        <p class="text-sm text-gray-600">
                            {{ item.description }}
                        </p>
                        <div class="flex items-center space-x-4 mt-1">
                            <div class="flex items-center space-x-2">
                                <span
                                    v-if="item.cash_price > 0"
                                    class="text-sm font-medium text-green-600"
                                >
                                    ₱{{ formatCash(item.cash_price) }}
                                </span>
                                <span v-else class="text-sm text-red-500">
                                    No price set
                                </span>
                            </div>
                            <span
                                :class="[
                                    'text-xs px-2 py-1 rounded-full',
                                    item.is_active
                                        ? 'bg-green-100 text-green-800'
                                        : 'bg-red-100 text-red-800',
                                ]"
                            >
                                {{ item.is_active ? "Active" : "Inactive" }}
                            </span>
                            <span class="text-xs text-gray-500">
                                Stock:
                                <span
                                    :class="
                                        item.stock === null
                                            ? 'text-green-600'
                                            : ''
                                    "
                                >
                                    {{
                                        item.stock === null
                                            ? "Unlimited"
                                            : item.stock
                                    }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2 justify-end sm:justify-start">
                    <button
                        @click="editItem(item)"
                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                    >
                        <i class="pi pi-pencil"></i>
                    </button>
                    <button
                        @click="deleteItem(item)"
                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    >
                        <i class="pi pi-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Empty Items State -->
        <div v-else class="text-center py-12">
            <i class="pi pi-box text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-600 mb-2">No Items Yet</h3>
            <p class="text-gray-500 mb-4">
                Start by adding your first item to the shop
            </p>
            <button
                @click="openCreate"
                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors"
            >
                <i class="pi pi-plus mr-2"></i>
                Add First Item
            </button>
        </div>

        <!-- Create/Edit Item Dialog -->
        <div
            v-if="showCreateItem || showEditItem"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div
                class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto"
            >
                <h3 class="text-xl font-bold text-gray-800 mb-4">
                    {{ showEditItem ? "Edit Item" : "Add New Item" }}
                </h3>
                <form @submit.prevent="saveItem">
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Item Name *
                            </label>
                            <input
                                v-model="itemForm.name"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Enter item name"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Description
                            </label>
                            <textarea
                                v-model="itemForm.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Describe the item..."
                            ></textarea>
                        </div>

                        <!-- Pricing Section -->
                        <div class="space-y-4 border-t pt-4">
                            <h4 class="text-md font-semibold text-gray-800">
                                Pricing
                            </h4>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                >
                                    Cash Price *
                                </label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-2 text-gray-500"
                                        >₱</span
                                    >
                                    <input
                                        v-model.number="itemForm.cash_price"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        required
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="0.00"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Stock Management Section -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Stock Management
                            </label>
                            <div class="flex items-center gap-3 mb-3">
                                <button
                                    type="button"
                                    @click="toggleUnlimitedStock"
                                    :class="
                                        itemForm.unlimited_stock
                                            ? 'bg-green-500 hover:bg-green-600 text-white'
                                            : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
                                    "
                                    class="px-4 py-2 rounded-lg transition-colors flex items-center gap-2 font-medium"
                                >
                                    <i
                                        :class="
                                            itemForm.unlimited_stock
                                                ? 'pi pi-infinity'
                                                : 'pi pi-box'
                                        "
                                    ></i>
                                    {{
                                        itemForm.unlimited_stock
                                            ? "Unlimited"
                                            : "Limited"
                                    }}
                                </button>
                                <span class="text-sm text-gray-500">
                                    {{
                                        itemForm.unlimited_stock
                                            ? "This item will never run out of stock"
                                            : "Set a specific quantity available"
                                    }}
                                </span>
                            </div>

                            <div
                                v-if="!itemForm.unlimited_stock"
                                class="flex items-center gap-3"
                            >
                                <div class="flex-1">
                                    <input
                                        v-model.number="itemForm.stock"
                                        type="number"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                        placeholder="Enter stock quantity"
                                    />
                                </div>
                                <div
                                    class="text-sm text-gray-500 whitespace-nowrap"
                                >
                                    items available
                                </div>
                            </div>

                            <div
                                class="mt-2 p-3 rounded-lg"
                                :class="
                                    itemForm.unlimited_stock
                                        ? 'bg-green-50 border border-green-200'
                                        : 'bg-blue-50 border border-blue-200'
                                "
                            >
                                <div class="flex items-center gap-2">
                                    <i
                                        :class="
                                            itemForm.unlimited_stock
                                                ? 'pi pi-infinity text-green-600'
                                                : 'pi pi-box text-blue-600'
                                        "
                                    ></i>
                                    <span
                                        class="text-sm font-medium"
                                        :class="
                                            itemForm.unlimited_stock
                                                ? 'text-green-700'
                                                : 'text-blue-700'
                                        "
                                    >
                                        {{ getStockStatusText() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Image
                            </label>
                            <input
                                ref="imageInput"
                                type="file"
                                accept="image/*"
                                @change="handleImageUpload"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </div>

                        <div class="flex items-center">
                            <input
                                v-model="itemForm.is_active"
                                type="checkbox"
                                id="is_active"
                                class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                            />
                            <label
                                for="is_active"
                                class="ml-2 block text-sm text-gray-700"
                            >
                                Active (visible to customers)
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button
                            type="button"
                            @click="closeItemDialog"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="savingItem"
                            class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50"
                        >
                            <i
                                v-if="savingItem"
                                class="pi pi-spin pi-spinner mr-2"
                            ></i>
                            {{ showEditItem ? "Update" : "Create" }} Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "ItemsTab",
    props: {
        shop: { type: Object, required: true },
        items: { type: Array, default: () => [] },
    },
    // Tells the parent to re-fetch items after a change
    emits: ["items-changed"],
    data() {
        return {
            showCreateItem: false,
            showEditItem: false,
            editingItem: null,
            savingItem: false,
            itemForm: {
                name: "",
                description: "",
                price: 0,
                cash_price: 0,
                stock: 0,
                unlimited_stock: false,
                is_active: true,
                image: null,
            },
        };
    },
    watch: {
        "itemForm.unlimited_stock"(newVal) {
            if (newVal) {
                this.itemForm.stock = 0;
            } else if (!this.itemForm.stock) {
                this.itemForm.stock = 1;
            }
        },
    },
    methods: {
        openCreate() {
            this.showCreateItem = true;
        },

        async saveItem() {
            try {
                this.savingItem = true;
                const formData = new FormData();

                formData.append("name", this.itemForm.name);
                formData.append("description", this.itemForm.description || "");
                formData.append("cash_price", this.itemForm.cash_price || 0);
                formData.append(
                    "is_active",
                    this.itemForm.is_active ? "1" : "0",
                );
                formData.append(
                    "unlimited_stock",
                    this.itemForm.unlimited_stock ? "1" : "0",
                );

                if (!this.itemForm.unlimited_stock) {
                    formData.append("stock", this.itemForm.stock || 0);
                }

                if (this.itemForm.image) {
                    formData.append("image", this.itemForm.image);
                }

                let response;
                if (this.showEditItem) {
                    formData.append("_method", "PUT");
                    response = await axios.post(
                        `/api/shops/${this.shop.id}/items/${this.editingItem.id}`,
                        formData,
                        {
                            headers: { "Content-Type": "multipart/form-data" },
                        },
                    );
                } else {
                    response = await axios.post(
                        `/api/shops/${this.shop.id}/items`,
                        formData,
                        {
                            headers: { "Content-Type": "multipart/form-data" },
                        },
                    );
                }

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: response.data.message,
                    });

                    this.closeItemDialog();
                    this.$emit("items-changed");
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message || "Failed to save item",
                });
            } finally {
                this.savingItem = false;
            }
        },

        editItem(item) {
            this.editingItem = item;
            this.itemForm = {
                name: item.name,
                description: item.description || "",
                price: item.price || 0,
                cash_price: item.cash_price || 0,
                stock: item.stock || 0,
                unlimited_stock: item.stock === null,
                is_active: item.is_active,
                image: null,
            };
            this.showEditItem = true;
        },

        async deleteItem(item) {
            if (!confirm(`Are you sure you want to delete "${item.name}"?`)) {
                return;
            }

            try {
                const response = await axios.delete(
                    `/api/shops/${this.shop.id}/items/${item.id}`,
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Item deleted successfully",
                    });

                    this.$emit("items-changed");
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to delete item",
                });
            }
        },

        closeItemDialog() {
            this.showCreateItem = false;
            this.showEditItem = false;
            this.editingItem = null;
            this.itemForm = {
                name: "",
                description: "",
                price: 0,
                cash_price: 0,
                stock: 0,
                unlimited_stock: false,
                is_active: true,
                image: null,
            };
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = "";
            }
        },

        toggleUnlimitedStock() {
            this.itemForm.unlimited_stock = !this.itemForm.unlimited_stock;
            if (!this.itemForm.unlimited_stock && !this.itemForm.stock) {
                this.itemForm.stock = 1;
            }
        },

        getStockStatusText() {
            if (this.itemForm.unlimited_stock) {
                return "Unlimited stock - customers can always purchase this item";
            } else {
                const stock = this.itemForm.stock || 0;
                if (stock === 0) {
                    return "Out of stock - customers cannot purchase this item";
                } else if (stock <= 5) {
                    return `Low stock - only ${stock} items remaining`;
                } else {
                    return `${stock} items available for purchase`;
                }
            }
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    this.$toast?.add({
                        severity: "error",
                        summary: "Error",
                        detail: "Image file must be smaller than 2MB",
                    });
                    event.target.value = "";
                    return;
                }
                this.itemForm.image = file;
            }
        },

        formatCash(amount) {
            return parseFloat(amount || 0).toFixed(2);
        },
    },
};
</script>
