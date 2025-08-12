<template>
    <Layout>
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
                    <div class="text-center">
                        <h1
                            class="text-4xl font-bold text-gray-800 mb-2 flex items-center justify-center gap-3"
                        >
                            <i class="pi pi-trophy text-yellow-500"></i>
                            Leaderboards
                        </h1>
                        <p class="text-gray-600">
                            Top players ranked by points
                        </p>
                    </div>
                </div>

                <!-- Top 3 Podium -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- 2nd Place -->
                    <div v-if="topUsers[1]" class="order-2 md:order-1">
                        <div
                            class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl p-6 text-center relative overflow-hidden"
                        >
                            <!-- Silver Badge -->
                            <div class="absolute top-4 right-4">
                                <div
                                    class="w-12 h-12 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center shadow-lg"
                                >
                                    <span class="text-white font-bold text-lg"
                                        >2</span
                                    >
                                </div>
                            </div>

                            <!-- Profile Image -->
                            <div class="relative mb-4">
                                <div
                                    class="w-20 h-20 mx-auto rounded-full overflow-hidden border-4 border-gray-400 shadow-lg"
                                >
                                    <img
                                        v-if="topUsers[1].profile_image"
                                        :src="`/storage/profiles/${topUsers[1].profile_image}`"
                                        :alt="topUsers[1].name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                                    >
                                        <i
                                            class="pi pi-user text-white text-2xl"
                                        ></i>
                                    </div>
                                </div>
                                <!-- Premium Badge -->
                                <div
                                    v-if="topUsers[1].is_premium"
                                    class="absolute -bottom-1 -right-1 w-6 h-6 bg-yellow-400 rounded-full border-2 border-white flex items-center justify-center"
                                >
                                    <i
                                        class="pi pi-star text-white text-xs"
                                    ></i>
                                </div>
                            </div>

                            <h3 class="text-xl font-bold text-gray-800 mb-1">
                                {{ topUsers[1].name }}
                            </h3>
                            <p class="text-2xl font-bold text-gray-600">
                                {{ formatPoints(topUsers[1].points) }}
                            </p>
                            <p class="text-sm text-gray-500">
                                Level {{ topUsers[1].level }}
                            </p>
                        </div>
                    </div>

                    <!-- 1st Place -->
                    <div v-if="topUsers[0]" class="order-1 md:order-2">
                        <div
                            class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-xl p-6 text-center relative overflow-hidden transform md:scale-105"
                        >
                            <!-- Gold Crown -->
                            <div class="absolute top-4 right-4">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center shadow-lg"
                                >
                                    <i
                                        class="pi pi-crown text-white text-xl"
                                    ></i>
                                </div>
                            </div>

                            <!-- Profile Image -->
                            <div class="relative mb-4">
                                <div
                                    class="w-24 h-24 mx-auto rounded-full overflow-hidden border-4 border-yellow-400 shadow-lg"
                                >
                                    <img
                                        v-if="topUsers[0].profile_image"
                                        :src="`/storage/profiles/${topUsers[0].profile_image}`"
                                        :alt="topUsers[0].name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                                    >
                                        <i
                                            class="pi pi-user text-white text-3xl"
                                        ></i>
                                    </div>
                                </div>
                                <!-- Premium Badge -->
                                <div
                                    v-if="topUsers[0].is_premium"
                                    class="absolute -bottom-1 -right-1 w-6 h-6 bg-yellow-400 rounded-full border-2 border-white flex items-center justify-center"
                                >
                                    <i
                                        class="pi pi-star text-white text-xs"
                                    ></i>
                                </div>
                            </div>

                            <h3 class="text-2xl font-bold text-gray-800 mb-1">
                                {{ topUsers[0].name }}
                            </h3>
                            <p class="text-3xl font-bold text-yellow-700">
                                {{ formatPoints(topUsers[0].points) }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Level {{ topUsers[0].level }}
                            </p>
                            <div class="mt-2">
                                <span
                                    class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-medium"
                                >
                                    🏆 Champion
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 3rd Place -->
                    <div v-if="topUsers[2]" class="order-3">
                        <div
                            class="bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-xl p-6 text-center relative overflow-hidden"
                        >
                            <!-- Bronze Badge -->
                            <div class="absolute top-4 right-4">
                                <div
                                    class="w-12 h-12 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-full flex items-center justify-center shadow-lg"
                                >
                                    <span class="text-white font-bold text-lg"
                                        >3</span
                                    >
                                </div>
                            </div>

                            <!-- Profile Image -->
                            <div class="relative mb-4">
                                <div
                                    class="w-20 h-20 mx-auto rounded-full overflow-hidden border-4 border-yellow-600 shadow-lg"
                                >
                                    <img
                                        v-if="topUsers[2].profile_image"
                                        :src="`/storage/profiles/${topUsers[2].profile_image}`"
                                        :alt="topUsers[2].name"
                                        class="w-full h-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                                    >
                                        <i
                                            class="pi pi-user text-white text-2xl"
                                        ></i>
                                    </div>
                                </div>
                                <!-- Premium Badge -->
                                <div
                                    v-if="topUsers[2].is_premium"
                                    class="absolute -bottom-1 -right-1 w-6 h-6 bg-yellow-400 rounded-full border-2 border-white flex items-center justify-center"
                                >
                                    <i
                                        class="pi pi-star text-white text-xs"
                                    ></i>
                                </div>
                            </div>

                            <h3 class="text-xl font-bold text-white mb-1">
                                {{ topUsers[2].name }}
                            </h3>
                            <p class="text-2xl font-bold text-yellow-200">
                                {{ formatPoints(topUsers[2].points) }}
                            </p>
                            <p class="text-sm text-yellow-100">
                                Level {{ topUsers[2].level }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Full Leaderboard Table -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2
                            class="text-xl font-bold text-gray-800 flex items-center gap-2"
                        >
                            <i class="pi pi-chart-bar text-blue-500"></i>
                            Full Rankings
                        </h2>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="p-8 text-center">
                        <i
                            class="pi pi-spin pi-spinner text-blue-500 text-2xl mb-4"
                        ></i>
                        <p class="text-gray-600">Loading leaderboard...</p>
                    </div>

                    <!-- Leaderboard Table -->
                    <div v-else-if="users.length > 0" class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Rank
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Player
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Level
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Points
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                    >
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr
                                    v-for="(user, index) in users"
                                    :key="user.id"
                                    class="hover:bg-gray-50 transition-colors"
                                    :class="{
                                        'bg-gradient-to-r from-yellow-50 to-yellow-100':
                                            index === 0,
                                        'bg-gradient-to-r from-gray-50 to-gray-100':
                                            index === 1,
                                        'bg-gradient-to-r from-yellow-100 to-orange-100':
                                            index === 2,
                                    }"
                                >
                                    <!-- Rank -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                v-if="index === 0"
                                                class="w-8 h-8 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center shadow-md"
                                            >
                                                <i
                                                    class="pi pi-crown text-white text-sm"
                                                ></i>
                                            </div>
                                            <div
                                                v-else-if="index === 1"
                                                class="w-8 h-8 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center shadow-md"
                                            >
                                                <span
                                                    class="text-white font-bold text-sm"
                                                    >2</span
                                                >
                                            </div>
                                            <div
                                                v-else-if="index === 2"
                                                class="w-8 h-8 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-full flex items-center justify-center shadow-md"
                                            >
                                                <span
                                                    class="text-white font-bold text-sm"
                                                    >3</span
                                                >
                                            </div>
                                            <div
                                                v-else
                                                class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center"
                                            >
                                                <span
                                                    class="text-gray-700 font-medium text-sm"
                                                    >{{ index + 1 }}</span
                                                >
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Player -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 relative">
                                                <div
                                                    class="h-10 w-10 rounded-full overflow-hidden border-2 border-gray-200"
                                                >
                                                    <img
                                                        v-if="
                                                            user.profile_image
                                                        "
                                                        :src="`/storage/profiles/${user.profile_image}`"
                                                        :alt="user.name"
                                                        class="h-full w-full object-cover"
                                                    />
                                                    <div
                                                        v-else
                                                        class="h-full w-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                                                    >
                                                        <i
                                                            class="pi pi-user text-white text-sm"
                                                        ></i>
                                                    </div>
                                                </div>
                                                <!-- Premium Badge -->
                                                <div
                                                    v-if="user.is_premium"
                                                    class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full border border-white flex items-center justify-center"
                                                >
                                                    <i
                                                        class="pi pi-star text-white text-xs"
                                                    ></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-sm font-medium text-gray-900"
                                                >
                                                    {{ user.name }}
                                                </div>
                                                <div
                                                    v-if="user.is_premium"
                                                    class="text-xs text-yellow-600 font-medium"
                                                >
                                                    Premium Member
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Level -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                                        >
                                            Level {{ user.level }}
                                        </span>
                                    </td>

                                    <!-- Points -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm font-bold text-gray-900"
                                        >
                                            {{ formatPoints(user.points) }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            points
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="index === 0"
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"
                                        >
                                            🏆 Champion
                                        </span>
                                        <span
                                            v-else-if="index === 1"
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"
                                        >
                                            🥈 Runner-up
                                        </span>
                                        <span
                                            v-else-if="index === 2"
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"
                                        >
                                            🥉 Third Place
                                        </span>
                                        <span
                                            v-else-if="index < 10"
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                                        >
                                            Top 10
                                        </span>
                                        <span
                                            v-else
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800"
                                        >
                                            Player
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-8 text-center">
                        <i class="pi pi-users text-gray-300 text-4xl mb-4"></i>
                        <p class="text-gray-600">No players found</p>
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
            users: [],
            loading: true,
        };
    },
    computed: {
        topUsers() {
            return this.users.slice(0, 3);
        },
    },
    methods: {
        async fetchLeaderboard() {
            try {
                this.loading = true;
                const response = await axios.get("/api/leaderboard");

                if (response.data.success) {
                    this.users = response.data.users;
                } else {
                    throw new Error("Failed to fetch leaderboard");
                }
            } catch (error) {
                console.error("Error fetching leaderboard:", error);
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load leaderboard",
                });
            } finally {
                this.loading = false;
            }
        },

        formatPoints(points) {
            return points.toLocaleString();
        },
    },
    mounted() {
        this.fetchLeaderboard();
    },
};
</script>
