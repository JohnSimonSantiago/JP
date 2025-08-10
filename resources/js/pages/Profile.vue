<template>
    <Layout>
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Profile Header -->
                <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
                    <div
                        class="flex flex-col md:flex-row items-center md:items-start gap-6"
                    >
                        <!-- Profile Picture -->
                        <div class="relative">
                            <div
                                class="w-32 h-32 rounded-full overflow-hidden border-4 border-blue-500 shadow-lg"
                            >
                                <img
                                    v-if="user.profile_image"
                                    :src="`/storage/profiles/${user.profile_image}`"
                                    :alt="user.name"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                                >
                                    <i
                                        class="pi pi-user text-white text-4xl"
                                    ></i>
                                </div>
                            </div>
                            <!-- Upload button -->
                            <button
                                @click="triggerFileUpload"
                                class="absolute bottom-0 right-0 bg-blue-500 hover:bg-blue-600 text-white rounded-full p-2 shadow-lg transition-colors"
                            >
                                <i class="pi pi-camera text-sm"></i>
                            </button>
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/*"
                                @change="uploadProfileImage"
                                class="hidden"
                            />
                        </div>

                        <!-- User Info -->
                        <div class="text-center md:text-left flex-1">
                            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                                {{ user.name }}
                            </h1>
                            <div class="flex flex-col md:flex-row gap-4 mb-4">
                                <div class="bg-blue-50 px-4 py-2 rounded-lg">
                                    <div
                                        class="text-sm text-blue-600 font-medium"
                                    >
                                        Level
                                    </div>
                                    <div
                                        class="text-2xl font-bold text-blue-700"
                                    >
                                        {{ user.level }}
                                    </div>
                                </div>
                                <div class="bg-green-50 px-4 py-2 rounded-lg">
                                    <div
                                        class="text-sm text-green-600 font-medium"
                                    >
                                        Points
                                    </div>
                                    <div
                                        class="text-2xl font-bold text-green-700"
                                    >
                                        {{ user.points.toLocaleString() }}
                                    </div>
                                </div>
                            </div>

                            <!-- Membership Status -->
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-2">
                                    <div :class="membershipStatus.class">
                                        <i :class="membershipStatus.icon"></i>
                                    </div>
                                    <span
                                        :class="membershipStatus.textClass"
                                        class="font-medium"
                                    >
                                        {{ membershipStatus.text }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Membership Card - Full Width -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2
                        class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2"
                    >
                        <i class="pi pi-crown text-yellow-500"></i>
                        Membership Status
                    </h2>

                    <div
                        v-if="
                            activeMembership &&
                            activeMembership.status === 'approved'
                        "
                        class="space-y-4"
                    >
                        <!-- Circular Progress -->
                        <div class="flex justify-center">
                            <div class="relative w-40 h-40">
                                <svg
                                    class="w-40 h-40 transform -rotate-90"
                                    viewBox="0 0 144 144"
                                >
                                    <!-- Background circle -->
                                    <circle
                                        cx="72"
                                        cy="72"
                                        r="60"
                                        stroke="#e5e7eb"
                                        stroke-width="8"
                                        fill="none"
                                    />
                                    <!-- Progress circle -->
                                    <circle
                                        cx="72"
                                        cy="72"
                                        r="60"
                                        :stroke="membershipProgress.color"
                                        stroke-width="8"
                                        fill="none"
                                        stroke-linecap="round"
                                        :stroke-dasharray="
                                            membershipProgress.circumference
                                        "
                                        :stroke-dashoffset="
                                            membershipProgress.offset
                                        "
                                        class="transition-all duration-500 ease-in-out"
                                    />
                                </svg>
                                <!-- Center content -->
                                <div
                                    class="absolute inset-0 flex flex-col items-center justify-center"
                                >
                                    <div
                                        class="text-2xl font-bold text-gray-800"
                                    >
                                        {{ membershipProgress.daysLeft }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        days left
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Membership Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div
                                class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                            >
                                <span class="text-gray-600">Type</span>
                                <span class="font-medium capitalize">{{
                                    activeMembership.type
                                }}</span>
                            </div>
                            <div
                                class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                            >
                                <span class="text-gray-600">Started</span>
                                <span class="font-medium">{{
                                    formatDate(activeMembership.start_date)
                                }}</span>
                            </div>
                            <div
                                class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                            >
                                <span class="text-gray-600">Expires</span>
                                <span class="font-medium">{{
                                    formatDate(activeMembership.end_date)
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Membership -->
                    <div
                        v-else-if="
                            activeMembership &&
                            activeMembership.status === 'pending'
                        "
                        class="text-center py-8"
                    >
                        <i
                            class="pi pi-clock text-yellow-500 text-4xl mb-4"
                        ></i>
                        <h3 class="text-lg font-medium text-gray-600 mb-2">
                            Membership Pending Approval
                        </h3>
                        <p class="text-gray-500 mb-4">
                            Your {{ activeMembership.type }} membership is
                            waiting for approval.
                        </p>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Status</span>
                                <span
                                    class="font-medium capitalize bg-yellow-100 text-yellow-800 px-2 py-1 rounded"
                                >
                                    {{ activeMembership.status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Rejected Membership -->
                    <div
                        v-else-if="
                            activeMembership &&
                            activeMembership.status === 'rejected'
                        "
                        class="text-center py-8"
                    >
                        <i
                            class="pi pi-times-circle text-red-500 text-4xl mb-4"
                        ></i>
                        <h3 class="text-lg font-medium text-gray-600 mb-2">
                            Membership Application Rejected
                        </h3>
                        <p class="text-gray-500 mb-4">
                            Your membership application was not approved. Please
                            contact support for more information.
                        </p>
                        <button
                            class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-6 py-2 rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all"
                        >
                            Apply Again
                        </button>
                    </div>

                    <!-- No Membership -->
                    <div v-else class="text-center py-8">
                        <i class="pi pi-crown text-gray-300 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-600 mb-2">
                            No Active Membership
                        </h3>
                        <p class="text-gray-500 mb-4">
                            Upgrade to premium to unlock exclusive features!
                        </p>
                        <button
                            class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-6 py-2 rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all"
                        >
                            Upgrade Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
export default {
    data() {
        return {
            user: {
                id: null,
                name: "",
                level: 1,
                points: 0,
                is_premium: false,
                profile_image: null,
            },
            activeMembership: null,
            loading: true,
        };
    },
    computed: {
        membershipStatus() {
            if (
                !this.user.is_premium ||
                !this.activeMembership ||
                this.activeMembership.status !== "approved"
            ) {
                return {
                    text: "Free Member",
                    class: "w-3 h-3 bg-gray-400 rounded-full",
                    icon: "pi pi-circle-fill",
                    textClass: "text-gray-600",
                };
            }

            const now = new Date();
            const endDate = new Date(this.activeMembership.end_date);
            const daysLeft = Math.ceil((endDate - now) / (1000 * 60 * 60 * 24));

            if (daysLeft <= 3) {
                return {
                    text: "Premium (Expiring Soon)",
                    class: "w-3 h-3 bg-red-400 rounded-full animate-pulse",
                    icon: "pi pi-circle-fill",
                    textClass: "text-red-600",
                };
            }

            return {
                text: "Premium Member",
                class: "w-3 h-3 bg-green-400 rounded-full",
                icon: "pi pi-circle-fill",
                textClass: "text-green-600",
            };
        },
        membershipProgress() {
            if (
                !this.activeMembership ||
                this.activeMembership.status !== "approved"
            )
                return null;

            const startDate = new Date(this.activeMembership.start_date);
            const endDate = new Date(this.activeMembership.end_date);
            const now = new Date();

            const totalDays = Math.ceil(
                (endDate - startDate) / (1000 * 60 * 60 * 24)
            );
            const daysLeft = Math.max(
                0,
                Math.ceil((endDate - now) / (1000 * 60 * 60 * 24))
            );
            const daysUsed = totalDays - daysLeft;

            const percentage = (daysUsed / totalDays) * 100;
            const circumference = 2 * Math.PI * 60; // radius is 60
            const offset = circumference - (percentage / 100) * circumference;

            let color = "#10b981"; // green
            if (daysLeft <= 7) color = "#f59e0b"; // yellow
            if (daysLeft <= 3) color = "#ef4444"; // red

            return {
                daysLeft,
                percentage,
                circumference,
                offset,
                color,
            };
        },
    },
    methods: {
        async fetchUserData() {
            try {
                this.loading = true;
                const response = await axios.get("/api/user/profile");
                this.user = response.data.user;
                this.activeMembership = response.data.membership;
            } catch (error) {
                console.error("Error fetching user data:", error);
                this.$toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load profile data",
                });
            } finally {
                this.loading = false;
            }
        },
        triggerFileUpload() {
            this.$refs.fileInput.click();
        },
        async uploadProfileImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append("profile_image", file);

            try {
                const response = await axios.post(
                    "/api/user/upload-profile-image",
                    formData,
                    {
                        headers: {
                            "Content-Type": "multipart/form-data",
                        },
                    }
                );

                this.user.profile_image = response.data.filename;
                this.$toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Profile image updated successfully!",
                });
            } catch (error) {
                console.error("Error uploading image:", error);
                this.$toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to upload profile image",
                });
            }
        },
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
            });
        },
    },
    mounted() {
        this.fetchUserData();
    },
};
</script>
