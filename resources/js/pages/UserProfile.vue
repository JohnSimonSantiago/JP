<template>
    <Layout>
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto">
                <!-- Back Button -->
                <div class="mb-6">
                    <button
                        @click="goBack"
                        class="flex items-center gap-2 text-blue-600 hover:text-blue-700 transition-colors"
                    >
                        <i class="pi pi-arrow-left"></i>
                        Back to Leaderboards
                    </button>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="text-center py-12">
                    <i
                        class="pi pi-spin pi-spinner text-blue-500 text-3xl mb-4"
                    ></i>
                    <p class="text-gray-600">Loading profile...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="text-center py-12">
                    <i
                        class="pi pi-exclamation-triangle text-red-500 text-3xl mb-4"
                    ></i>
                    <h3 class="text-lg font-medium text-gray-600 mb-2">
                        Profile Not Found
                    </h3>
                    <p class="text-gray-500 mb-4">{{ error }}</p>
                    <button
                        @click="goBack"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                    >
                        Go Back
                    </button>
                </div>

                <!-- Profile Content -->
                <template v-else-if="user">
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
                                <!-- Premium Badge -->
                                <div
                                    v-if="user.is_premium"
                                    class="absolute -bottom-2 -right-2 w-8 h-8 bg-yellow-400 rounded-full border-2 border-white flex items-center justify-center shadow-lg"
                                >
                                    <i
                                        class="pi pi-star text-white text-sm"
                                    ></i>
                                </div>
                            </div>

                            <!-- User Info -->
                            <div class="text-center md:text-left flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <h1
                                        class="text-3xl font-bold text-gray-800"
                                    >
                                        {{ user.name }}
                                    </h1>
                                    <span
                                        v-if="user.role === 'admin'"
                                        class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm font-medium"
                                    >
                                        Admin
                                    </span>
                                    <span
                                        v-else-if="user.role === 'shop_owner'"
                                        class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-sm font-medium"
                                    >
                                        Shop Owner
                                    </span>
                                </div>

                                <!-- Bio Display (if exists and public) -->
                                <div
                                    v-if="
                                        user.hasOwnProperty('bio') && user.bio
                                    "
                                    class="mb-4"
                                >
                                    <p
                                        class="text-gray-600 italic text-sm leading-relaxed max-w-md"
                                    >
                                        "{{ user.bio }}"
                                    </p>
                                </div>

                                <div
                                    class="flex flex-col md:flex-row gap-4 mb-4"
                                >
                                    <div
                                        class="bg-blue-50 px-4 py-2 rounded-lg"
                                    >
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
                                    <div
                                        class="bg-green-50 px-4 py-2 rounded-lg"
                                    >
                                        <div
                                            class="text-sm text-green-600 font-medium"
                                        >
                                            Stars
                                        </div>
                                        <div
                                            class="text-2xl font-bold text-green-700"
                                        >
                                            {{
                                                user.stars?.toLocaleString() ||
                                                0
                                            }}
                                        </div>
                                    </div>
                                    <div
                                        v-if="user.age"
                                        class="bg-purple-50 px-4 py-2 rounded-lg"
                                    >
                                        <div
                                            class="text-sm text-purple-600 font-medium"
                                        >
                                            Age
                                        </div>
                                        <div
                                            class="text-2xl font-bold text-purple-700"
                                        >
                                            {{ user.age }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Membership Status -->
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center gap-2">
                                        <div :class="membershipStatus.class">
                                            <i
                                                :class="membershipStatus.icon"
                                            ></i>
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

                    <!-- Public Profile Information -->
                    <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                        <h2
                            class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2"
                        >
                            <i class="pi pi-user text-blue-500"></i>
                            Public Profile
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Bio (only if public and exists) -->
                            <div
                                v-if="user.hasOwnProperty('bio')"
                                class="p-4 bg-gray-50 rounded-lg md:col-span-2"
                            >
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1"
                                    >Bio</label
                                >
                                <div class="text-lg font-medium text-gray-800">
                                    {{ user.bio || "No bio provided" }}
                                </div>
                            </div>
                            <div
                                v-else
                                class="p-4 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 md:col-span-2"
                            >
                                <label
                                    class="block text-sm font-medium text-gray-400 mb-1"
                                    >Bio</label
                                >
                                <div
                                    class="flex items-center gap-2 text-gray-500"
                                >
                                    <i class="pi pi-eye-slash"></i>
                                    <span>Private information</span>
                                </div>
                            </div>

                            <!-- Username -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1"
                                    >Username</label
                                >
                                <div class="text-lg font-medium text-gray-800">
                                    {{ user.name }}
                                </div>
                            </div>

                            <!-- User ID -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1"
                                    >User ID</label
                                >
                                <div class="flex items-center gap-2">
                                    <div
                                        class="text-lg font-medium text-gray-800 font-mono"
                                    >
                                        #{{ user.id }}
                                    </div>
                                    <button
                                        @click="copyToClipboard(user.id)"
                                        class="text-blue-600 hover:text-blue-700 p-1 rounded transition-colors"
                                        title="Copy User ID"
                                    >
                                        <i class="pi pi-copy text-sm"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Gender (only if public or not set) -->
                            <div
                                v-if="user.hasOwnProperty('gender')"
                                class="p-4 bg-gray-50 rounded-lg"
                            >
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1"
                                    >Gender</label
                                >
                                <div class="text-lg font-medium text-gray-800">
                                    {{
                                        formatGender(user.gender) ||
                                        "Not specified"
                                    }}
                                </div>
                            </div>
                            <div
                                v-else
                                class="p-4 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300"
                            >
                                <label
                                    class="block text-sm font-medium text-gray-400 mb-1"
                                    >Gender</label
                                >
                                <div
                                    class="flex items-center gap-2 text-gray-500"
                                >
                                    <i class="pi pi-eye-slash"></i>
                                    <span>Private information</span>
                                </div>
                            </div>

                            <!-- Birthday (only if public or not set) -->
                            <div
                                v-if="user.hasOwnProperty('birthday')"
                                class="p-4 bg-gray-50 rounded-lg"
                            >
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1"
                                    >Birthday</label
                                >
                                <div class="text-lg font-medium text-gray-800">
                                    {{
                                        formatBirthday(user.birthday) ||
                                        "Not shared"
                                    }}
                                </div>
                            </div>
                            <div
                                v-else
                                class="p-4 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300"
                            >
                                <label
                                    class="block text-sm font-medium text-gray-400 mb-1"
                                    >Birthday</label
                                >
                                <div
                                    class="flex items-center gap-2 text-gray-500"
                                >
                                    <i class="pi pi-eye-slash"></i>
                                    <span>Private information</span>
                                </div>
                            </div>

                            <!-- Age (if birthday is public and set) -->
                            <div
                                v-if="user.age"
                                class="p-4 bg-gray-50 rounded-lg"
                            >
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1"
                                    >Age</label
                                >
                                <div class="text-lg font-medium text-gray-800">
                                    {{ user.age }} years old
                                </div>
                            </div>

                            <!-- Account Level -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1"
                                    >Account Level</label
                                >
                                <div class="text-lg font-medium text-gray-800">
                                    Level {{ user.level }}
                                </div>
                            </div>

                            <!-- Member Since -->
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1"
                                    >Member Since</label
                                >
                                <div class="text-lg font-medium text-gray-800">
                                    {{ formatDate(user.member_since) }}
                                </div>
                            </div>

                            <!-- Address (only if public) - Show as full width if present -->
                            <div
                                v-if="user.hasOwnProperty('address')"
                                class="p-4 bg-gray-50 rounded-lg md:col-span-2"
                            >
                                <label
                                    class="block text-sm font-medium text-gray-600 mb-1"
                                    >Address</label
                                >
                                <div class="text-lg font-medium text-gray-800">
                                    {{ user.address || "Not provided" }}
                                </div>
                            </div>
                            <div
                                v-else
                                class="p-4 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 md:col-span-2"
                            >
                                <label
                                    class="block text-sm font-medium text-gray-400 mb-1"
                                    >Address</label
                                >
                                <div
                                    class="flex items-center gap-2 text-gray-500"
                                >
                                    <i class="pi pi-eye-slash"></i>
                                    <span>Private information</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats & Achievements -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2
                            class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2"
                        >
                            <i class="pi pi-chart-bar text-green-500"></i>
                            Stats & Achievements
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Current Ranking -->
                            <div
                                class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl"
                            >
                                <i
                                    class="pi pi-trophy text-blue-600 text-3xl mb-3"
                                ></i>
                                <div class="text-2xl font-bold text-blue-800">
                                    {{ userRank || "N/A" }}
                                </div>
                                <div class="text-sm text-blue-600">
                                    Current Rank
                                </div>
                            </div>

                            <!-- Total Stars -->
                            <div
                                class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl"
                            >
                                <i
                                    class="pi pi-star text-green-600 text-3xl mb-3"
                                ></i>
                                <div class="text-2xl font-bold text-green-800">
                                    {{ user.stars?.toLocaleString() || 0 }}
                                </div>
                                <div class="text-sm text-green-600">
                                    Total Stars
                                </div>
                            </div>

                            <!-- Account Status -->
                            <div
                                class="text-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl"
                            >
                                <i
                                    :class="
                                        user.is_premium
                                            ? 'pi pi-crown text-yellow-600'
                                            : 'pi pi-user text-purple-600'
                                    "
                                    class="text-3xl mb-3"
                                ></i>
                                <div
                                    class="text-2xl font-bold"
                                    :class="
                                        user.is_premium
                                            ? 'text-yellow-800'
                                            : 'text-purple-800'
                                    "
                                >
                                    {{ user.is_premium ? "Premium" : "Free" }}
                                </div>
                                <div
                                    class="text-sm"
                                    :class="
                                        user.is_premium
                                            ? 'text-yellow-600'
                                            : 'text-purple-600'
                                    "
                                >
                                    Account Type
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </Layout>
</template>

<script>
export default {
    props: {
        userId: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            user: null,
            membership: null,
            loading: true,
            error: null,
            userRank: null,
        };
    },
    computed: {
        membershipStatus() {
            if (
                !this.user?.is_premium ||
                !this.membership ||
                this.membership.status !== "approved"
            ) {
                return {
                    text: "Free Member",
                    class: "w-3 h-3 bg-gray-400 rounded-full",
                    icon: "pi pi-circle-fill",
                    textClass: "text-gray-600",
                };
            }

            const now = new Date();
            const endDate = new Date(this.membership.end_date);
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
    },
    methods: {
        async fetchUserProfile() {
            try {
                this.loading = true;
                this.error = null;

                const response = await axios.get(
                    `/api/users/${this.userId}/profile`
                );

                if (response.data.success) {
                    this.user = response.data.user;
                    this.membership = response.data.membership;

                    // Get user's rank in leaderboard
                    await this.fetchUserRank();
                } else {
                    this.error =
                        response.data.message || "Failed to load profile";
                }
            } catch (error) {
                console.error("Error fetching user profile:", error);
                this.error =
                    error.response?.data?.message ||
                    "User not found or profile unavailable";
            } finally {
                this.loading = false;
            }
        },

        async fetchUserRank() {
            try {
                // Get leaderboard to find user's rank
                const response = await axios.get("/api/leaderboard");
                if (response.data.success) {
                    const users = response.data.users;
                    const userIndex = users.findIndex(
                        (u) => u.id == this.userId
                    );
                    if (userIndex !== -1) {
                        this.userRank = `#${userIndex + 1}`;
                    }
                }
            } catch (error) {
                console.error("Error fetching user rank:", error);
            }
        },

        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text.toString());
                this.$toast?.add({
                    severity: "success",
                    summary: "Copied!",
                    detail: `User ID ${text} copied to clipboard`,
                    life: 2000,
                });
            } catch (error) {
                console.error("Failed to copy:", error);
                // Fallback for browsers that don't support clipboard API
                this.fallbackCopyTextToClipboard(text.toString());
            }
        },

        fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.top = "0";
            textArea.style.left = "0";
            textArea.style.position = "fixed";

            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand("copy");
                if (successful) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Copied!",
                        detail: `User ID ${text} copied to clipboard`,
                        life: 2000,
                    });
                } else {
                    throw new Error("Copy command failed");
                }
            } catch (err) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Copy Failed",
                    detail: "Could not copy User ID to clipboard",
                });
            }

            document.body.removeChild(textArea);
        },

        goBack() {
            // Go back to previous page or leaderboards
            if (window.history.length > 1) {
                this.$router.go(-1);
            } else {
                this.$router.push("/leaderboards");
            }
        },

        formatGender(gender) {
            if (!gender) return null;

            const genderMap = {
                male: "Male",
                female: "Female",
                other: "Other",
                prefer_not_to_say: "Prefer not to say",
            };

            return genderMap[gender] || gender;
        },

        formatBirthday(birthday) {
            if (!birthday) return null;

            return new Date(birthday).toLocaleDateString("en-US", {
                year: "numeric",
                month: "long",
                day: "numeric",
            });
        },

        formatDate(dateString) {
            if (!dateString) return "Unknown";

            return new Date(dateString).toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
            });
        },
    },
    async mounted() {
        await this.fetchUserProfile();
    },
    watch: {
        // Watch for route changes if the same component is reused
        userId(newUserId) {
            if (newUserId) {
                this.fetchUserProfile();
            }
        },
    },
};
</script>
