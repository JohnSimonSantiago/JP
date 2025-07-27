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
                <div class="text-sm text-gray-600">Welcome back!</div>
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center"
                    >
                        <i class="pi pi-user text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">
                            Level 1
                        </div>
                        <div class="text-xs text-gray-500">0 Points</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex pt-16">
            <!-- Left Sidebar -->
            <div class="w-64 bg-white shadow-lg h-screen fixed left-0 top-16">
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
        return {};
    },
    methods: {
        checkAuth() {
            axios.get("/checkUser").then(({ data }) => {
                if (!data) {
                    this.$router.push("/");
                }
            });
        },
        logout() {
            axios.post("/logout").then(() => {
                this.$router.push("/");
            });
        },
    },
    mounted() {
        this.checkAuth();
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
</style>
