<template>
    <Layout>
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Loading State -->
            <div
                v-if="loading"
                class="min-h-screen bg-gray-50 flex items-center justify-center"
            >
                <div class="text-center">
                    <div
                        class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"
                    ></div>
                    <p class="text-gray-600">Loading gallery...</p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="images.length === 0">
                <div class="max-w-6xl mx-auto">
                    <div class="mb-8 flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                                Image Gallery Dashboard
                            </h1>
                            <p class="text-gray-600">No images available</p>
                        </div>
                        <button
                            v-if="isAdmin"
                            @click="showAdminPanel = !showAdminPanel"
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            Admin Panel
                        </button>
                    </div>

                    <!-- Admin Panel -->
                    <div
                        v-if="isAdmin && showAdminPanel"
                        class="bg-white rounded-lg shadow-lg p-6 mb-8"
                    >
                        <h2 class="text-xl font-semibold mb-4">Admin Panel</h2>
                        <button
                            @click="showImageForm = true"
                            class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                ></path>
                            </svg>
                            Add New Image
                        </button>
                    </div>

                    <div class="bg-white rounded-lg shadow-lg p-12 text-center">
                        <p class="text-gray-500 text-lg">
                            No images to display.
                            <span v-if="isAdmin"
                                >Add some images using the admin panel
                                above.</span
                            >
                            <span v-else>Please check back later.</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main Gallery -->
            <div v-else class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="mb-8 flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800 mb-2">
                            Image Gallery Dashboard
                        </h1>
                        <p class="text-gray-600">
                            Explore our collection of stunning images
                        </p>
                    </div>
                    <button
                        v-if="isAdmin"
                        @click="showAdminPanel = !showAdminPanel"
                        class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                    >
                        Admin Panel
                    </button>
                </div>

                <!-- Admin Panel -->
                <div
                    v-if="isAdmin && showAdminPanel"
                    class="bg-white rounded-lg shadow-lg p-6 mb-8"
                >
                    <h2 class="text-xl font-semibold mb-4">Admin Panel</h2>
                    <div class="flex gap-3">
                        <button
                            @click="showImageForm = true"
                            class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors flex items-center gap-2"
                        >
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                ></path>
                            </svg>
                            Add New Image
                        </button>
                        <button
                            @click="fetchImages"
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            Refresh Gallery
                        </button>
                    </div>
                </div>

                <!-- Carousel Section -->
                <div class="mb-12">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-4">
                        Featured Images
                    </h2>
                    <div
                        class="relative bg-white rounded-lg shadow-lg overflow-hidden"
                    >
                        <!-- Main carousel image -->
                        <div class="relative h-96 overflow-hidden">
                            <img
                                :src="images[currentImageIndex].image_url"
                                :alt="images[currentImageIndex].title"
                                class="w-full h-full object-cover transition-all duration-500"
                            />

                            <!-- Admin controls overlay -->
                            <div
                                v-if="isAdmin"
                                class="absolute top-4 right-4 flex gap-2"
                            >
                                <button
                                    @click="
                                        handleEditImage(
                                            images[currentImageIndex]
                                        )
                                    "
                                    class="bg-yellow-500 bg-opacity-80 text-white p-2 rounded-full hover:bg-opacity-100 transition-all"
                                    title="Edit Image"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        ></path>
                                    </svg>
                                </button>
                                <button
                                    @click="
                                        handleDeleteImage(
                                            images[currentImageIndex].id
                                        )
                                    "
                                    class="bg-red-500 bg-opacity-80 text-white p-2 rounded-full hover:bg-opacity-100 transition-all"
                                    title="Delete Image"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                        ></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Carousel controls -->
                            <button
                                @click="prevImage"
                                class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all"
                            >
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 19l-7-7 7-7"
                                    ></path>
                                </svg>
                            </button>
                            <button
                                @click="nextImage"
                                class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition-all"
                            >
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5l7 7-7 7"
                                    ></path>
                                </svg>
                            </button>

                            <!-- Image info overlay -->
                            <div
                                class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent text-white p-6"
                            >
                                <h3 class="text-xl font-semibold mb-1">
                                    {{ images[currentImageIndex].title }}
                                </h3>
                                <p class="text-sm opacity-90">
                                    {{ images[currentImageIndex].description }}
                                </p>
                            </div>
                        </div>

                        <!-- Carousel dots -->
                        <div
                            class="flex justify-center space-x-2 py-4 bg-white"
                        >
                            <button
                                v-for="(image, index) in images"
                                :key="index"
                                @click="goToImage(index)"
                                :class="{
                                    'bg-blue-500': index === currentImageIndex,
                                    'bg-gray-300 hover:bg-gray-400':
                                        index !== currentImageIndex,
                                }"
                                class="w-3 h-3 rounded-full transition-all"
                            />
                        </div>
                    </div>
                </div>

                <!-- Image List Section -->
                <div>
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                        All Images
                    </h2>
                    <div class="space-y-4">
                        <div
                            v-for="(image, index) in images"
                            :key="image.id"
                            @click="goToImage(index)"
                            :class="{
                                'ring-2 ring-blue-500':
                                    index === currentImageIndex,
                            }"
                            class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow cursor-pointer"
                        >
                            <div class="flex">
                                <!-- Small image -->
                                <div class="w-48 h-32 flex-shrink-0">
                                    <img
                                        :src="image.image_url"
                                        :alt="image.title"
                                        class="w-full h-full object-cover"
                                    />
                                </div>

                                <!-- Description -->
                                <div class="flex-1 p-6">
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div class="flex-1">
                                            <h3
                                                class="text-lg font-semibold text-gray-800 mb-2"
                                            >
                                                {{ image.title }}
                                            </h3>
                                            <p
                                                class="text-gray-600 leading-relaxed"
                                            >
                                                {{ image.description }}
                                            </p>
                                            <div
                                                class="mt-3 flex items-center text-sm text-gray-500"
                                            >
                                                <span
                                                    class="bg-gray-100 px-2 py-1 rounded-md"
                                                >
                                                    Image {{ index + 1 }} of
                                                    {{ images.length }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Admin controls -->
                                        <div
                                            v-if="isAdmin"
                                            class="flex gap-2 ml-4"
                                        >
                                            <button
                                                @click.stop="
                                                    handleEditImage(image)
                                                "
                                                class="bg-yellow-500 text-white p-2 rounded-lg hover:bg-yellow-600 transition-colors"
                                                title="Edit Image"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                    ></path>
                                                </svg>
                                            </button>
                                            <button
                                                @click.stop="
                                                    handleDeleteImage(image.id)
                                                "
                                                class="bg-red-500 text-white p-2 rounded-lg hover:bg-red-600 transition-colors"
                                                title="Delete Image"
                                            >
                                                <svg
                                                    class="w-4 h-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                    ></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Form Modal -->
            <div
                v-if="showImageForm"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">
                            {{ editingImage ? "Edit Image" : "Add New Image" }}
                        </h3>
                        <button
                            @click="resetForm"
                            class="text-gray-500 hover:text-gray-700"
                        >
                            <svg
                                class="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                ></path>
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="handleImageSubmit">
                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Title</label
                            >
                            <input
                                v-model="formData.title"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                        </div>

                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Description</label
                            >
                            <textarea
                                v-model="formData.description"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                rows="3"
                                required
                            ></textarea>
                        </div>

                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Upload Image</label
                            >
                            <input
                                ref="imageInput"
                                type="file"
                                accept="image/*"
                                @change="handleImageUpload"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                :required="!editingImage"
                            />
                            <p class="text-xs text-gray-500 mt-1">
                                Max 2MB, JPG/PNG/GIF supported
                                <span v-if="editingImage">
                                    • Leave empty to keep current image</span
                                >
                            </p>

                            <!-- Image preview -->
                            <div v-if="imagePreview" class="mt-3">
                                <img
                                    :src="imagePreview"
                                    alt="Preview"
                                    class="w-32 h-32 object-cover rounded-lg border"
                                />
                            </div>
                        </div>

                        <div class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Display Order</label
                            >
                            <input
                                v-model.number="formData.display_order"
                                type="number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                min="0"
                            />
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="submit"
                                class="flex-1 bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition-colors flex items-center justify-center gap-2"
                            >
                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"
                                    ></path>
                                </svg>
                                {{ editingImage ? "Update" : "Save" }}
                            </button>
                            <button
                                type="button"
                                @click="resetForm"
                                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition-colors"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
export default {
    name: "GalleryDashboard",
    data() {
        return {
            currentImageIndex: 0,
            images: [],
            loading: true,
            user: null,
            showAdminPanel: false,
            showImageForm: false,
            editingImage: null,
            imagePreview: null,
            formData: {
                title: "",
                description: "",
                display_order: 0,
                imageFile: null,
            },
        };
    },
    computed: {
        // Simple admin check like MyShop.vue
        isAdmin() {
            return this.user && this.user.role === "admin";
        },
    },
    async mounted() {
        await this.fetchUserData(); // Fetch user data first
        await this.fetchImages();
        console.log("Component mounted. isAdmin:", this.isAdmin);
    },
    methods: {
        // Simple user data fetch like MyShop.vue
        async fetchUserData() {
            try {
                const user = JSON.parse(localStorage.getItem("user") || "null");
                this.user = user;
                console.log("User data loaded:", this.user);
                console.log("User role:", this.user?.role);
            } catch (error) {
                console.error("Error getting user data:", error);
                this.user = null;
            }
        },

        async fetchImages() {
            try {
                this.loading = true;
                console.log("=== GALLERY FETCH DEBUG ===");
                console.log("Fetching images from /api/gallery...");

                const response = await axios.get("/api/gallery");

                console.log("Gallery response status:", response.status);

                if (response.data) {
                    const data = response.data;
                    console.log("Gallery data received:", data);
                    this.images = data.images || [];
                    console.log("Images set to:", this.images.length, "items");
                } else {
                    console.error("No data in response");
                    this.images = [];
                }
                console.log("=== END GALLERY FETCH ===");
            } catch (error) {
                console.error("Error fetching images:", error);
                this.images = [];
            } finally {
                this.loading = false;
                console.log("Loading set to false");
            }
        },

        handleImageUpload(event) {
            const file = event.target.files[0];
            if (file) {
                // Validate file size (2MB limit)
                if (file.size > 2 * 1024 * 1024) {
                    alert("Image file must be smaller than 2MB");
                    event.target.value = "";
                    return;
                }

                // Validate file type
                if (!file.type.startsWith("image/")) {
                    alert("Please select a valid image file");
                    event.target.value = "";
                    return;
                }

                this.formData.imageFile = file;

                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        async handleImageSubmit() {
            try {
                let imageUrl = null;

                // If we have a new image file, upload it first
                if (this.formData.imageFile) {
                    const uploadFormData = new FormData();
                    uploadFormData.append("image", this.formData.imageFile);

                    console.log("Uploading image file...");
                    const uploadResponse = await axios.post(
                        "/api/gallery/upload",
                        uploadFormData,
                        {
                            headers: {
                                "Content-Type": "multipart/form-data",
                            },
                        }
                    );

                    if (uploadResponse.data.success) {
                        imageUrl = uploadResponse.data.data.image_url;
                        console.log("Image uploaded successfully:", imageUrl);
                    } else {
                        throw new Error(
                            uploadResponse.data.message ||
                                "Failed to upload image"
                        );
                    }
                }

                // Prepare gallery data
                const galleryData = {
                    title: this.formData.title,
                    description: this.formData.description,
                    display_order: this.formData.display_order || 0,
                };

                // Include image URL if we uploaded a new image, or keep existing for edits
                if (imageUrl) {
                    galleryData.image_url = imageUrl;
                } else if (this.editingImage) {
                    galleryData.image_url = this.editingImage.image_url;
                }

                // Create or update gallery entry
                const method = this.editingImage ? "PUT" : "POST";
                const url = this.editingImage
                    ? `/api/gallery/${this.editingImage.id}`
                    : "/api/gallery";

                console.log("Saving gallery entry...", galleryData);
                const response = await axios({
                    method,
                    url,
                    data: galleryData,
                });

                if (response.data.success) {
                    await this.fetchImages();
                    this.resetForm();
                    alert(
                        this.editingImage
                            ? "Image updated successfully!"
                            : "Image added successfully!"
                    );
                } else {
                    alert(response.data.message || "Failed to save image");
                }
            } catch (error) {
                console.error("Error saving image:", error);
                alert(
                    `Error saving image: ${
                        error.response?.data?.message || error.message
                    }`
                );
            }
        },

        async handleDeleteImage(imageId) {
            if (!window.confirm("Are you sure you want to delete this image?"))
                return;

            try {
                const response = await axios.delete(`/api/gallery/${imageId}`);

                if (response.data.success) {
                    await this.fetchImages();
                    alert("Image deleted successfully!");
                    // Reset current index if needed
                    if (this.currentImageIndex >= this.images.length - 1) {
                        this.currentImageIndex = Math.max(
                            0,
                            this.images.length - 2
                        );
                    }
                } else {
                    alert(response.data.message || "Failed to delete image");
                }
            } catch (error) {
                console.error("Error deleting image:", error);
                alert("Error deleting image");
            }
        },

        resetForm() {
            this.formData = {
                title: "",
                description: "",
                display_order: 0,
                imageFile: null,
            };
            this.editingImage = null;
            this.showImageForm = false;
            this.imagePreview = null;

            // Clear file input
            if (this.$refs.imageInput) {
                this.$refs.imageInput.value = "";
            }
        },

        handleEditImage(image) {
            this.formData = {
                title: image.title,
                description: image.description,
                display_order: image.display_order,
                imageFile: null, // No file selected initially
            };
            this.editingImage = image;
            this.showImageForm = true;
            this.imagePreview = image.image_url; // Show current image as preview
        },

        nextImage() {
            this.currentImageIndex =
                (this.currentImageIndex + 1) % this.images.length;
        },

        prevImage() {
            this.currentImageIndex =
                (this.currentImageIndex - 1 + this.images.length) %
                this.images.length;
        },

        goToImage(index) {
            this.currentImageIndex = index;
        },
    },
};
</script>
