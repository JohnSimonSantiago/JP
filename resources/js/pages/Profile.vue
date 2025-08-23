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
                            <div class="flex items-center gap-3 mb-3">
                                <h1 class="text-3xl font-bold text-gray-800">
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

                            <!-- REMOVED AGE FROM TOP STATS -->
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
                                        Stars
                                    </div>
                                    <div
                                        class="text-2xl font-bold text-green-700"
                                    >
                                        {{ user.stars?.toLocaleString() || 0 }}
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

                <!-- Profile Information -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2
                            class="text-xl font-bold text-gray-800 flex items-center gap-2"
                        >
                            <i class="pi pi-user text-blue-500"></i>
                            Profile Information
                        </h2>

                        <div class="flex gap-2">
                            <button
                                v-if="!isEditing"
                                @click="toggleEdit"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                            >
                                <i class="pi pi-pencil"></i>
                                Edit Profile
                            </button>

                            <template v-else>
                                <button
                                    @click="saveProfile"
                                    :disabled="updating"
                                    class="bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                                >
                                    <i
                                        v-if="updating"
                                        class="pi pi-spin pi-spinner"
                                    ></i>
                                    <i v-else class="pi pi-save"></i>
                                    {{ updating ? "Saving..." : "Save" }}
                                </button>
                                <button
                                    @click="cancelEdit"
                                    :disabled="updating"
                                    class="bg-gray-500 hover:bg-gray-600 disabled:bg-gray-300 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                                >
                                    <i class="pi pi-times"></i>
                                    Cancel
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- View Mode -->
                    <div
                        v-if="!isEditing"
                        class="grid grid-cols-1 md:grid-cols-2 gap-6"
                    >
                        <!-- REMOVED USERNAME FROM VIEW MODE -->

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

                        <!-- Gender -->
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-1">
                                <label
                                    class="block text-sm font-medium text-gray-600"
                                    >Gender</label
                                >
                                <i
                                    v-if="privacySettings.gender === 'private'"
                                    class="pi pi-eye-slash text-gray-400 text-sm"
                                    title="Private - only you can see this"
                                ></i>
                                <i
                                    v-else
                                    class="pi pi-eye text-gray-400 text-sm"
                                    title="Public - others can see this"
                                ></i>
                            </div>
                            <div class="text-lg font-medium text-gray-800">
                                {{
                                    formatGender(user.gender) || "Not specified"
                                }}
                            </div>
                        </div>

                        <!-- Birthday -->
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-1">
                                <label
                                    class="block text-sm font-medium text-gray-600"
                                    >Birthday</label
                                >
                                <i
                                    v-if="
                                        privacySettings.birthday === 'private'
                                    "
                                    class="pi pi-eye-slash text-gray-400 text-sm"
                                    title="Private - only you can see this"
                                ></i>
                                <i
                                    v-else
                                    class="pi pi-eye text-gray-400 text-sm"
                                    title="Public - others can see this"
                                ></i>
                            </div>
                            <div class="text-lg font-medium text-gray-800">
                                {{ formatBirthday(user.birthday) || "Not set" }}
                            </div>
                        </div>

                        <!-- Age (if birthday is set) -->
                        <div
                            v-if="calculatedAge"
                            class="p-4 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center justify-between mb-1">
                                <label
                                    class="block text-sm font-medium text-gray-600"
                                    >Age</label
                                >
                                <i
                                    v-if="
                                        privacySettings.birthday === 'private'
                                    "
                                    class="pi pi-eye-slash text-gray-400 text-sm"
                                    title="Private - only you can see this"
                                ></i>
                                <i
                                    v-else
                                    class="pi pi-eye text-gray-400 text-sm"
                                    title="Public - others can see this"
                                ></i>
                            </div>
                            <div class="text-lg font-medium text-gray-800">
                                {{ calculatedAge }} years old
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="p-4 bg-gray-50 rounded-lg md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label
                                    class="block text-sm font-medium text-gray-600"
                                    >Address</label
                                >
                                <i
                                    v-if="privacySettings.address === 'private'"
                                    class="pi pi-eye-slash text-gray-400 text-sm"
                                    title="Private - only you can see this"
                                ></i>
                                <i
                                    v-else
                                    class="pi pi-eye text-gray-400 text-sm"
                                    title="Public - others can see this"
                                ></i>
                            </div>
                            <div class="text-lg font-medium text-gray-800">
                                {{ user.address || "Not provided" }}
                            </div>
                        </div>

                        <!-- Account Info -->
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1"
                                >Account Level</label
                            >
                            <div class="text-lg font-medium text-gray-800">
                                Level {{ user.level }}
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 rounded-lg">
                            <label
                                class="block text-sm font-medium text-gray-600 mb-1"
                                >Member Since</label
                            >
                            <div class="text-lg font-medium text-gray-800">
                                {{ formatDate(user.created_at) }}
                            </div>
                        </div>
                    </div>

                    <!-- Edit Mode -->
                    <form
                        v-else
                        @submit.prevent="saveProfile"
                        class="space-y-6"
                    >
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Username - KEPT IN EDIT MODE -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Username <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="profileForm.name"
                                    type="text"
                                    required
                                    :class="[
                                        'w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors',
                                        validationErrors.name
                                            ? 'border-red-300 bg-red-50'
                                            : 'border-gray-300',
                                    ]"
                                    placeholder="Enter your username"
                                />
                                <p
                                    v-if="validationErrors.name"
                                    class="text-sm text-red-600 mt-1"
                                >
                                    {{ validationErrors.name[0] }}
                                </p>
                                <p v-else class="text-sm text-gray-500 mt-1">
                                    Username must be unique
                                </p>
                            </div>

                            <!-- Gender with Privacy -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Gender</label
                                >
                                <select
                                    v-model="profileForm.gender"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-2"
                                >
                                    <option value="" disabled>
                                        Select Gender
                                    </option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>

                                <!-- Privacy Toggle -->
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-600"
                                        >Visibility:</span
                                    >
                                    <label
                                        class="flex items-center gap-1 cursor-pointer"
                                    >
                                        <input
                                            type="radio"
                                            :value="'public'"
                                            v-model="
                                                profileForm.privacy_settings
                                                    .gender
                                            "
                                            class="text-blue-600"
                                        />
                                        <i class="pi pi-eye text-green-600"></i>
                                        <span class="text-green-600"
                                            >Public</span
                                        >
                                    </label>
                                    <label
                                        class="flex items-center gap-1 cursor-pointer"
                                    >
                                        <input
                                            type="radio"
                                            :value="'private'"
                                            v-model="
                                                profileForm.privacy_settings
                                                    .gender
                                            "
                                            class="text-blue-600"
                                        />
                                        <i
                                            class="pi pi-eye-slash text-red-600"
                                        ></i>
                                        <span class="text-red-600"
                                            >Private</span
                                        >
                                    </label>
                                </div>
                            </div>

                            <!-- Birthday with Privacy -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Birthday</label
                                >
                                <input
                                    v-model="profileForm.birthday"
                                    type="date"
                                    :max="maxDate"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-2"
                                />
                                <!-- Privacy Toggle -->
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-600"
                                        >Visibility:</span
                                    >
                                    <label
                                        class="flex items-center gap-1 cursor-pointer"
                                    >
                                        <input
                                            type="radio"
                                            :value="'public'"
                                            v-model="
                                                profileForm.privacy_settings
                                                    .birthday
                                            "
                                            class="text-blue-600"
                                        />
                                        <i class="pi pi-eye text-green-600"></i>
                                        <span class="text-green-600"
                                            >Public</span
                                        >
                                    </label>
                                    <label
                                        class="flex items-center gap-1 cursor-pointer"
                                    >
                                        <input
                                            type="radio"
                                            :value="'private'"
                                            v-model="
                                                profileForm.privacy_settings
                                                    .birthday
                                            "
                                            class="text-blue-600"
                                        />
                                        <i
                                            class="pi pi-eye-slash text-red-600"
                                        ></i>
                                        <span class="text-red-600"
                                            >Private</span
                                        >
                                    </label>
                                </div>
                                <p class="text-sm text-gray-500 mt-1">
                                    Age will be calculated automatically
                                </p>
                            </div>

                            <!-- Age Display (calculated from birthday in edit mode) -->
                            <div v-if="profileForm.birthday">
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Age</label
                                >
                                <input
                                    :value="
                                        calculateAgeFromDate(
                                            profileForm.birthday
                                        ) + ' years old'
                                    "
                                    type="text"
                                    disabled
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                                />
                                <p class="text-sm text-gray-500 mt-1">
                                    Calculated from birthday
                                </p>
                            </div>

                            <!-- Level (Display Only) -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                    >Level</label
                                >
                                <input
                                    :value="user.level"
                                    type="number"
                                    disabled
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed"
                                />
                                <p class="text-sm text-gray-500 mt-1">
                                    Level cannot be changed
                                </p>
                            </div>
                        </div>

                        <!-- Address with Privacy -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Address</label
                            >
                            <textarea
                                v-model="profileForm.address"
                                rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-2"
                                placeholder="Enter your full address"
                            ></textarea>
                            <!-- Privacy Toggle -->
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-600">Visibility:</span>
                                <label
                                    class="flex items-center gap-1 cursor-pointer"
                                >
                                    <input
                                        type="radio"
                                        :value="'public'"
                                        v-model="
                                            profileForm.privacy_settings.address
                                        "
                                        class="text-blue-600"
                                    />
                                    <i class="pi pi-eye text-green-600"></i>
                                    <span class="text-green-600">Public</span>
                                </label>
                                <label
                                    class="flex items-center gap-1 cursor-pointer"
                                >
                                    <input
                                        type="radio"
                                        :value="'private'"
                                        v-model="
                                            profileForm.privacy_settings.address
                                        "
                                        class="text-blue-600"
                                    />
                                    <i class="pi pi-eye-slash text-red-600"></i>
                                    <span class="text-red-600">Private</span>
                                </label>
                            </div>
                        </div>

                        <!-- Privacy Info -->
                        <div
                            class="bg-blue-50 border border-blue-200 rounded-lg p-4"
                        >
                            <div class="flex items-start gap-2">
                                <i
                                    class="pi pi-info-circle text-blue-600 text-sm mt-0.5"
                                ></i>
                                <div class="text-sm text-blue-800">
                                    <p class="font-medium mb-1">
                                        Privacy Settings
                                    </p>
                                    <p>
                                        <strong>Public:</strong> Other users can
                                        see this information on your profile
                                    </p>
                                    <p>
                                        <strong>Private:</strong> Only you can
                                        see this information
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Membership Card -->
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

                    <!-- No Membership / Other States -->
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
                stars: 0,
                points: 0,
                is_premium: false,
                profile_image: null,
                gender: null,
                birthday: null,
                address: null,
                privacy_settings: {},
                role: "user",
                created_at: null,
            },
            profileForm: {
                name: "",
                gender: "",
                birthday: "",
                address: "",
                privacy_settings: {
                    gender: "public",
                    birthday: "public",
                    address: "private",
                },
            },
            activeMembership: null,
            loading: true,
            updating: false,
            isEditing: false,
            validationErrors: {},
        };
    },
    computed: {
        privacySettings() {
            return (
                this.user.privacy_settings || {
                    gender: "public",
                    birthday: "public",
                    address: "private",
                }
            );
        },
        calculatedAge() {
            if (!this.user.birthday) return null;

            const today = new Date();
            const birthDate = new Date(this.user.birthday);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (
                monthDiff < 0 ||
                (monthDiff === 0 && today.getDate() < birthDate.getDate())
            ) {
                age--;
            }

            return age;
        },
        maxDate() {
            // Set max date to today (can't have future birthdays)
            return new Date().toISOString().split("T")[0];
        },
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
            const circumference = 2 * Math.PI * 60;
            const offset = circumference - (percentage / 100) * circumference;

            let color = "#10b981"; // green
            if (daysLeft <= 7) color = "#f59e0b"; // yellow
            if (daysLeft <= 3) color = "#ef4444"; // red

            return { daysLeft, percentage, circumference, offset, color };
        },
    },
    methods: {
        async fetchUserData() {
            try {
                this.loading = true;
                const response = await axios.get("/api/user/profile");
                this.user = response.data.user;
                this.activeMembership = response.data.membership;

                // Initialize form with user data and privacy settings
                this.profileForm = {
                    name: this.user.name || "",
                    gender: this.user.gender || "",
                    birthday: this.user.birthday || "",
                    address: this.user.address || "",
                    privacy_settings: this.user.privacy_settings || {
                        gender: "public",
                        birthday: "public",
                        address: "private",
                    },
                };
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
        toggleEdit() {
            this.isEditing = true;
            this.validationErrors = {};
            // Reset form with current user data
            this.profileForm = {
                name: this.user.name || "",
                gender: this.user.gender || "",
                birthday: this.user.birthday || "",
                address: this.user.address || "",
                privacy_settings: this.user.privacy_settings || {
                    gender: "public",
                    birthday: "public",
                    address: "private",
                },
            };
        },
        cancelEdit() {
            this.isEditing = false;
            this.validationErrors = {};
            // Reset form to original values
            this.profileForm = {
                name: this.user.name || "",
                gender: this.user.gender || "",
                birthday: this.user.birthday || "",
                address: this.user.address || "",
                privacy_settings: this.user.privacy_settings || {
                    gender: "public",
                    birthday: "public",
                    address: "private",
                },
            };
        },
        async saveProfile() {
            try {
                this.updating = true;
                this.validationErrors = {};

                console.log("Sending profile data:", this.profileForm); // Debug log

                const response = await axios.put(
                    "/api/user/profile",
                    this.profileForm
                );

                console.log("Profile update response:", response.data); // Debug log

                // Update local user data with response
                Object.assign(this.user, response.data.user);

                this.isEditing = false;
                this.$toast.add({
                    severity: "success",
                    summary: "Success",
                    detail: "Profile updated successfully!",
                });
            } catch (error) {
                console.error("Error updating profile:", error);

                // Handle validation errors
                if (
                    error.response?.status === 422 &&
                    error.response.data.errors
                ) {
                    this.validationErrors = error.response.data.errors;
                }

                this.$toast.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to update profile",
                });
            } finally {
                this.updating = false;
            }
        },
        async copyToClipboard(text) {
            try {
                await navigator.clipboard.writeText(text.toString());
                this.$toast.add({
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
                    this.$toast.add({
                        severity: "success",
                        summary: "Copied!",
                        detail: `User ID ${text} copied to clipboard`,
                        life: 2000,
                    });
                } else {
                    throw new Error("Copy command failed");
                }
            } catch (err) {
                this.$toast.add({
                    severity: "error",
                    summary: "Copy Failed",
                    detail: "Could not copy User ID to clipboard",
                });
            }

            document.body.removeChild(textArea);
        },

        calculateAgeFromDate(dateString) {
            if (!dateString) return null;

            const today = new Date();
            const birthDate = new Date(dateString);
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (
                monthDiff < 0 ||
                (monthDiff === 0 && today.getDate() < birthDate.getDate())
            ) {
                age--;
            }

            return age;
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
                        headers: { "Content-Type": "multipart/form-data" },
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
            if (!dateString) return "Unknown";

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
