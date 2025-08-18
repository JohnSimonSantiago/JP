<template lang="html">
    <div class="h-screen bg-gray-50">
        <!-- Top Header -->
        <div
            class="bg-white shadow-md z-50 flex items-center justify-between px-6 py-3 fixed w-full h-16"
        >
            <div class="flex items-center gap-3">
                <img
                    src="../../../public/runnrs.jpg"
                    alt="Logo"
                    class="w-10 h-10 rounded-lg"
                />
                <span class="text-xl font-bold text-gray-800">JP Gaming</span>
            </div>

            <!-- User info section with level and points -->
            <div class="flex items-center gap-4">
                <div class="text-sm text-gray-600">
                    Welcome back
                    <span class="font-medium text-gray-800">{{
                        user.name || "User"
                    }}</span
                    >!
                </div>
                <div class="flex items-center gap-3">
                    <!-- Profile Image or Default Avatar -->
                    <div class="relative">
                        <img
                            v-if="user.profile_image"
                            :src="`/storage/profiles/${user.profile_image}`"
                            :alt="user.name"
                            class="w-8 h-8 rounded-full object-cover border-2 border-blue-200"
                        />
                        <div
                            v-else
                            class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center border-2 border-blue-200"
                        >
                            <i class="pi pi-user text-white text-sm"></i>
                        </div>
                        <!-- Premium Badge -->
                        <div
                            v-if="user.is_premium"
                            class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full border border-white flex items-center justify-center"
                            title="Premium Member"
                        >
                            <i class="pi pi-star text-white text-xs"></i>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">
                            Level {{ user.level || 1 }}
                            <span
                                v-if="user.is_premium"
                                class="ml-1 px-1.5 py-0.5 text-xs bg-yellow-100 text-yellow-800 rounded-full"
                            >
                                Premium
                            </span>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ formatPoints(user.points || 0) }} Points
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex pt-16">
            <!-- Left Sidebar -->
            <div
                class="w-64 bg-white shadow-lg h-screen fixed left-0 top-16 sidebar"
            >
                <div class="p-6">
                    <nav class="space-y-2">
                        <!-- Dashboard -->
                        <router-link
                            active-class="bg-blue-50 text-blue-700 border-r-2 border-blue-600"
                            to="/dashboard"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 group"
                        >
                            <i
                                class="pi pi-th-large text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">Dashboard</span>
                        </router-link>

                        <!-- Leaderboards -->
                        <router-link
                            active-class="bg-blue-50 text-blue-700 border-r-2 border-blue-600"
                            to="/leaderboards"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 group"
                        >
                            <i
                                class="pi pi-chart-bar text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">Leaderboards</span>
                        </router-link>

                        <!-- Trade -->
                        <router-link
                            active-class="bg-blue-50 text-blue-700 border-r-2 border-blue-600"
                            to="/trade"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 group"
                        >
                            <i
                                class="pi pi-arrow-right-arrow-left text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">Trade</span>
                        </router-link>

                        <!-- Profile -->
                        <router-link
                            active-class="bg-blue-50 text-blue-700 border-r-2 border-blue-600"
                            to="/profile"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 group"
                        >
                            <i
                                class="pi pi-user text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">Profile</span>
                        </router-link>

                        <!-- Matches -->
                        <router-link
                            active-class="bg-blue-50 text-blue-700 border-r-2 border-blue-600"
                            to="/bet"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 group"
                        >
                            <i
                                class="pi pi-ticket text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">Matches</span>
                        </router-link>

                        <!-- Shops (Public) -->
                        <router-link
                            active-class="bg-blue-50 text-blue-700 border-r-2 border-blue-600"
                            to="/shops"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 group"
                        >
                            <i
                                class="pi pi-shopping-bag text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">Shops</span>
                        </router-link>

                        <!-- My Shop (Shop Owners & Admins Only) -->
                        <router-link
                            v-if="isShopOwnerOrAdmin"
                            active-class="bg-purple-50 text-purple-700 border-r-2 border-purple-600"
                            to="/my-shop"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-all duration-200 group"
                        >
                            <i
                                class="pi pi-shop text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">My Shop</span>
                            <!-- Shop Owner Badge -->
                            <span
                                class="ml-auto px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded-full"
                            >
                                {{ user.role === "admin" ? "Admin" : "Owner" }}
                            </span>
                        </router-link>

                        <!-- Divider -->
                        <div class="border-t border-gray-200 my-4"></div>

                        <!-- Logout -->
                        <button
                            @click="logout"
                            class="w-full flex items-center gap-3 px-4 py-3 text-red-600 rounded-lg hover:bg-red-50 transition-all duration-200 group"
                        >
                            <i
                                class="pi pi-sign-out text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">Logout</span>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 ml-64 p-6 overflow-y-auto">
                <slot />
            </div>
        </div>
    </div>
</template>

<script>
import Button from "primevue/button";

export default {
    components: {
        Button,
    },
    data() {
        return {
            user: {
                level: 1,
                points: 0,
                name: "",
                profile_image: null,
                is_premium: false,
                role: "user", // Default role
            },
        };
    },
    computed: {
        // Check if user is shop owner or admin
        isShopOwnerOrAdmin() {
            return (
                this.user.role === "shop_owner" || this.user.role === "admin"
            );
        },
    },
    methods: {
        // Format points with comma separators
        formatPoints(points) {
            return points.toLocaleString();
        },

        // Set up axios with token
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

        async checkAuth() {
            try {
                // Set up token first
                if (!this.setupAxiosToken()) {
                    this.$router.push("/");
                    return;
                }

                // Use the new API endpoint with token authentication
                const response = await axios.get("/api/user/profile");

                if (response.data.success) {
                    this.user = {
                        ...this.user,
                        ...response.data.user,
                    };

                    console.log("Layout: User data loaded:", this.user);
                } else {
                    throw new Error("Failed to fetch user data");
                }
            } catch (error) {
                console.error("Auth check failed:", error);

                // Clear invalid token and redirect to login
                localStorage.removeItem("auth-token");
                localStorage.removeItem("user");
                this.$router.push("/");
            }
        },

        async logout() {
            try {
                this.setupAxiosToken();

                // Call the API logout endpoint
                await axios.post("/api/logout");
            } catch (error) {
                console.error("Logout API call failed:", error);
                // Continue with local cleanup even if API call fails
            } finally {
                // Always clear local storage and redirect
                localStorage.removeItem("auth-token");
                localStorage.removeItem("user");
                delete axios.defaults.headers.common["Authorization"];
                this.$router.push("/");
            }
        },
    },
    async mounted() {
        await this.checkAuth();
    },
};
</script>

<style scoped>
/* Custom scrollbar for sidebar */
.sidebar::-webkit-scrollbar {
    width: 4px;
}

.sidebar::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.sidebar::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Smooth transitions for profile images */
img {
    transition: all 0.2s ease-in-out;
}

img:hover {
    transform: scale(1.05);
}
</style>
