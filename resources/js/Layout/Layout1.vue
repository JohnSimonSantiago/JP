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

            <!-- User info section with expanded stats -->
            <div class="flex items-center gap-4">
                <div class="text-sm text-gray-600">
                    Welcome back
                    <span class="font-medium text-gray-800">{{
                        user.name || "User"
                    }}</span
                    >!
                </div>

                <!-- User Stats Cards -->
                <div class="flex items-center gap-3">
                    <!-- Cash Display -->
                    <div
                        class="flex items-center gap-1 bg-green-50 px-2 py-1 rounded-lg border border-green-200"
                    >
                        <span class="text-xs font-semibold text-green-700">
                            ₱{{ formatCash(user.cash || 0) }}
                        </span>
                    </div>

                    <!-- Stars Display -->
                    <div
                        class="flex items-center gap-1 bg-yellow-50 px-2 py-1 rounded-lg border border-yellow-200"
                    >
                        <i class="pi pi-star text-yellow-600 text-xs"></i>
                        <span class="text-xs font-semibold text-yellow-700">
                            {{ formatPoints(user.stars || 0) }}
                        </span>
                    </div>

                    <!-- Points Display -->
                    <div
                        class="flex items-center gap-1 bg-blue-50 px-2 py-1 rounded-lg border border-blue-200"
                    >
                        <i class="pi pi-prime text-blue-600 text-xs"></i>
                        <span class="text-xs font-semibold text-blue-700">
                            {{ formatPoints(user.points || 0) }}
                        </span>
                    </div>

                    <!-- Rank Display -->
                    <div
                        class="flex items-center gap-1 bg-purple-50 px-2 py-1 rounded-lg border border-purple-200"
                    >
                        <i class="pi pi-trophy text-purple-600 text-xs"></i>
                        <span class="text-xs font-semibold text-purple-700">
                            {{ userRank || "Loading..." }}
                        </span>
                    </div>
                </div>

                <!-- Notification Bell -->
                <div class="relative">
                    <button
                        @click="toggleNotifications"
                        class="relative p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-full transition-all duration-200"
                        :class="{
                            'text-orange-600 bg-orange-50':
                                totalNotificationsCount > 0,
                            'animate-bounce': hasNewNotifications,
                        }"
                    >
                        <i class="pi pi-bell text-lg"></i>

                        <!-- Notification Badge -->
                        <span
                            v-if="totalNotificationsCount > 0"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-semibold animate-pulse"
                        >
                            {{
                                totalNotificationsCount > 99
                                    ? "99+"
                                    : totalNotificationsCount
                            }}
                        </span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div
                        v-if="showNotifications"
                        class="absolute right-0 top-full mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-[32rem] overflow-hidden"
                        @click.stop
                    >
                        <!-- Header -->
                        <div
                            class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg"
                        >
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-800">
                                    Notifications
                                </h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500">
                                        {{ totalNotificationsCount }} total
                                    </span>
                                    <button
                                        @click="refreshNotifications"
                                        class="text-xs text-blue-600 hover:text-blue-800"
                                        :disabled="loading"
                                    >
                                        <i
                                            class="pi pi-refresh"
                                            :class="{ 'pi-spin': loading }"
                                        ></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notification Categories Tabs -->
                        <div class="flex border-b border-gray-200">
                            <button
                                v-for="tab in notificationTabs"
                                :key="tab.key"
                                @click="activeNotificationTab = tab.key"
                                class="flex-1 px-3 py-2 text-xs font-medium border-b-2 transition-colors"
                                :class="[
                                    activeNotificationTab === tab.key
                                        ? 'border-blue-500 text-blue-600 bg-blue-50'
                                        : 'border-transparent text-gray-500 hover:text-gray-700',
                                ]"
                            >
                                <i :class="tab.icon" class="mr-1"></i>
                                {{ tab.label }} ({{ tab.count }})
                            </button>
                        </div>

                        <!-- Notification List -->
                        <div class="max-h-64 overflow-y-auto">
                            <!-- Loading State -->
                            <div v-if="loading" class="px-4 py-6 text-center">
                                <i
                                    class="pi pi-spin pi-spinner text-blue-500 text-xl mb-2"
                                ></i>
                                <p class="text-sm text-gray-500">
                                    Loading notifications...
                                </p>
                            </div>

                            <!-- Error State -->
                            <div
                                v-else-if="notificationError"
                                class="px-4 py-6 text-center"
                            >
                                <i
                                    class="pi pi-exclamation-triangle text-red-500 text-xl mb-2"
                                ></i>
                                <p class="text-sm text-red-600">
                                    {{ notificationError }}
                                </p>
                                <button
                                    @click="refreshNotifications"
                                    class="mt-2 text-xs text-blue-600 hover:text-blue-800"
                                >
                                    Try Again
                                </button>
                            </div>

                            <!-- No notifications -->
                            <div
                                v-else-if="currentNotifications.length === 0"
                                class="px-4 py-6 text-center text-gray-500"
                            >
                                <i
                                    class="pi pi-check-circle text-2xl text-green-500 mb-2"
                                ></i>
                                <p class="text-sm">
                                    All caught up! No
                                    {{ activeNotificationTab }} notifications.
                                </p>
                            </div>

                            <!-- Shop Orders -->
                            <div
                                v-for="order in currentNotifications"
                                :key="`order-${order.id}`"
                                v-if="activeNotificationTab === 'orders'"
                                class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                                @click="handleNotificationClick('order', order)"
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0"
                                    >
                                        <i
                                            class="pi pi-shopping-cart text-orange-600 text-xs"
                                        ></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="text-sm font-medium text-gray-900 truncate"
                                        >
                                            {{
                                                order.shop_item?.name || "Item"
                                            }}
                                        </p>
                                        <p
                                            class="text-xs text-gray-500 truncate"
                                        >
                                            by
                                            {{ order.user?.name || "Unknown" }}
                                        </p>
                                        <div
                                            class="flex items-center gap-2 mt-1"
                                        >
                                            <span
                                                class="text-xs text-orange-600 font-medium"
                                            >
                                                Qty: {{ order.quantity }}
                                            </span>
                                            <span class="text-xs text-gray-400"
                                                >•</span
                                            >
                                            <span class="text-xs text-gray-500">
                                                {{
                                                    formatTimeAgo(
                                                        order.created_at
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Trade Requests -->
                            <div
                                v-for="trade in currentNotifications"
                                :key="`trade-${trade.id}`"
                                v-if="activeNotificationTab === 'trades'"
                                class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                                @click="handleNotificationClick('trade', trade)"
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0"
                                    >
                                        <i
                                            class="pi pi-arrow-right-arrow-left text-blue-600 text-xs"
                                        ></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="text-sm font-medium text-gray-900 truncate"
                                        >
                                            Trade Request
                                        </p>
                                        <p
                                            class="text-xs text-gray-500 truncate"
                                        >
                                            from
                                            {{
                                                trade.sender?.name || "Unknown"
                                            }}
                                        </p>
                                        <div
                                            class="flex items-center gap-2 mt-1"
                                        >
                                            <span
                                                class="text-xs text-blue-600 font-medium"
                                            >
                                                {{ trade.send_amount }} →
                                                {{ trade.receive_amount }}
                                                points
                                            </span>
                                            <span class="text-xs text-gray-400"
                                                >•</span
                                            >
                                            <span class="text-xs text-gray-500">
                                                {{
                                                    formatTimeAgo(
                                                        trade.created_at
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bet Requests -->
                            <div
                                v-for="bet in currentNotifications"
                                :key="`bet-${bet.id}`"
                                v-if="activeNotificationTab === 'bets'"
                                class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                                @click="handleNotificationClick('bet', bet)"
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0"
                                    >
                                        <i
                                            class="pi pi-ticket text-green-600 text-xs"
                                        ></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="text-sm font-medium text-gray-900 truncate"
                                        >
                                            Bet Challenge
                                        </p>
                                        <p
                                            class="text-xs text-gray-500 truncate"
                                        >
                                            from
                                            {{ bet.creator?.name || "Unknown" }}
                                        </p>
                                        <div
                                            class="flex items-center gap-2 mt-1"
                                        >
                                            <span
                                                class="text-xs text-green-600 font-medium"
                                            >
                                                {{ bet.stars_amount }} stars
                                            </span>
                                            <span class="text-xs text-gray-400"
                                                >•</span
                                            >
                                            <span class="text-xs text-gray-500">
                                                {{
                                                    formatTimeAgo(
                                                        bet.created_at
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Referee Requests -->
                            <div
                                v-for="referee in currentNotifications"
                                :key="`referee-${referee.id}`"
                                v-if="activeNotificationTab === 'referee'"
                                class="px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                                @click="
                                    handleNotificationClick('referee', referee)
                                "
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0"
                                    >
                                        <i
                                            class="pi pi-balance-scale text-purple-600 text-xs"
                                        ></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p
                                            class="text-sm font-medium text-gray-900 truncate"
                                        >
                                            Referee Request
                                        </p>
                                        <p
                                            class="text-xs text-gray-500 truncate"
                                        >
                                            {{ referee.creator?.name }} vs
                                            {{ referee.opponent?.name }}
                                        </p>
                                        <div
                                            class="flex items-center gap-2 mt-1"
                                        >
                                            <span
                                                class="text-xs text-purple-600 font-medium"
                                            >
                                                {{ referee.stars_amount }} stars
                                            </span>
                                            <span class="text-xs text-gray-400"
                                                >•</span
                                            >
                                            <span class="text-xs text-gray-500">
                                                {{
                                                    formatTimeAgo(
                                                        referee.created_at
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            v-if="currentNotifications.length > 0"
                            class="px-4 py-3 border-t border-gray-200 bg-gray-50 rounded-b-lg"
                        >
                            <button
                                @click="navigateToNotificationType"
                                class="w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors"
                            >
                                View all {{ activeNotificationTab }} →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Profile Section -->
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
                            {{
                                user.role === "admin"
                                    ? "Administrator"
                                    : user.role === "shop_owner"
                                    ? "Shop Owner"
                                    : "Member"
                            }}
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

                        <!-- Rest of sidebar links... (keeping existing) -->
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

                        <!-- Trade with notification indicator -->
                        <router-link
                            active-class="bg-blue-50 text-blue-700 border-r-2 border-blue-600"
                            to="/trade"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 group relative"
                        >
                            <i
                                class="pi pi-arrow-right-arrow-left text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">Trade</span>
                            <div
                                v-if="totalTradeNotifications > 0"
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-semibold"
                            >
                                {{
                                    totalTradeNotifications > 9
                                        ? "9+"
                                        : totalTradeNotifications
                                }}
                            </div>
                        </router-link>

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

                        <!-- Matches/Bets with notification indicator -->
                        <router-link
                            active-class="bg-blue-50 text-blue-700 border-r-2 border-blue-600"
                            to="/bet"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 group relative"
                        >
                            <i
                                class="pi pi-ticket text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">Matches</span>
                            <div
                                v-if="totalBetNotifications > 0"
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-semibold"
                            >
                                {{
                                    totalBetNotifications > 9
                                        ? "9+"
                                        : totalBetNotifications
                                }}
                            </div>
                        </router-link>

                        <router-link
                            active-class="bg-blue-50 text-blue-700 border-r-2 border-blue-600"
                            to="/shops"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 group"
                        >
                            <i
                                class="pi pi-money-bill text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">Shops</span>
                        </router-link>

                        <router-link
                            active-class="bg-blue-50 text-blue-700 border-r-2 border-blue-600"
                            to="/point-shop"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 group"
                        >
                            <i
                                class="pi pi-prime text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">Point Shop</span>
                        </router-link>

                        <!-- My Shop (Shop Owners & Admins Only) -->
                        <router-link
                            v-if="isShopOwnerOrAdmin"
                            active-class="bg-purple-50 text-purple-700 border-r-2 border-purple-600"
                            to="/my-shop"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-700 transition-all duration-200 group relative"
                        >
                            <i
                                class="pi pi-shopping-bag text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium">My Shop</span>
                            <span
                                class="ml-auto px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded-full"
                            >
                                {{ user.role === "admin" ? "Admin" : "Owner" }}
                            </span>
                            <div
                                v-if="pendingOrdersCount > 0"
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-semibold"
                            >
                                {{
                                    pendingOrdersCount > 9
                                        ? "9+"
                                        : pendingOrdersCount
                                }}
                            </div>
                        </router-link>

                        <!-- Admin Point Pricing (Admins Only) -->
                        <router-link
                            v-if="isAdmin"
                            active-class="bg-orange-50 text-orange-700 border-r-2 border-orange-600"
                            to="/admin/point-pricing"
                            class="flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg hover:bg-orange-50 hover:text-orange-700 transition-all duration-200 group"
                        >
                            <i
                                class="pi pi-tags text-lg group-hover:scale-110 transition-transform"
                            ></i>
                            <span class="font-medium"
                                >Point Shop Dashboard</span
                            >
                            <span
                                class="ml-auto px-2 py-0.5 text-xs bg-orange-100 text-orange-700 rounded-full"
                            >
                                Admin
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
                stars: 0,
                cash: 0.0,
                name: "",
                profile_image: null,
                is_premium: false,
                role: "user",
            },
            userRank: null,

            // Notification system
            showNotifications: false,
            loading: false,
            notificationError: null,
            hasNewNotifications: false,

            // Notification tabs
            activeNotificationTab: "orders",

            // Individual notification data
            pendingOrders: [],
            tradeRequests: [],
            tradeSent: [], // Add sent trades
            betRequests: [],
            betsSent: [], // Add sent bets
            refereeRequests: [],

            // Notification counts
            pendingOrdersCount: 0,
            tradeRequestsCount: 0,
            tradeSentCount: 0, // Add sent count
            betRequestsCount: 0,
            betsSentCount: 0, // Add sent bets count
            refereeRequestsCount: 0,

            // Polling
            notificationPollingInterval: null,
        };
    },
    computed: {
        isShopOwnerOrAdmin() {
            return (
                this.user.role === "shop_owner" || this.user.role === "admin"
            );
        },

        // Check if user has any shop (both shop_owners and admins can have shops)
        hasShop() {
            return (
                this.user.role === "shop_owner" || this.user.role === "admin"
            );
        },

        isAdmin() {
            return this.user.role === "admin";
        },

        totalNotificationsCount() {
            return (
                this.pendingOrdersCount +
                this.tradeRequestsCount +
                this.tradeSentCount +
                this.betRequestsCount +
                this.betsSentCount +
                this.refereeRequestsCount
            );
        },

        // Total trade notifications (sent + received)
        totalTradeNotifications() {
            return this.tradeRequestsCount + this.tradeSentCount;
        },

        // Total bet notifications (incoming + sent + referee)
        totalBetNotifications() {
            return (
                this.betRequestsCount +
                this.betsSentCount +
                this.refereeRequestsCount
            );
        },

        notificationTabs() {
            const tabs = [
                {
                    key: "trades",
                    label: "Trades",
                    icon: "pi pi-arrow-right-arrow-left",
                    count: this.tradeRequestsCount,
                },
                {
                    key: "bets",
                    label: "Bets",
                    icon: "pi pi-ticket",
                    count: this.betRequestsCount,
                },
                {
                    key: "referee",
                    label: "Referee",
                    icon: "pi pi-balance-scale",
                    count: this.refereeRequestsCount,
                },
            ];

            // Only show orders tab for shop owners/admins
            if (this.isShopOwnerOrAdmin) {
                tabs.unshift({
                    key: "orders",
                    label: "Orders",
                    icon: "pi pi-shopping-cart",
                    count: this.pendingOrdersCount,
                });
            }

            return tabs;
        },

        currentNotifications() {
            switch (this.activeNotificationTab) {
                case "orders":
                    return this.pendingOrders.slice(0, 5);
                case "trades":
                    return this.tradeRequests.slice(0, 5);
                case "bets":
                    return this.betRequests.slice(0, 5);
                case "referee":
                    return this.refereeRequests.slice(0, 5);
                default:
                    return [];
            }
        },
    },

    methods: {
        formatPoints(points) {
            return (points || 0).toLocaleString();
        },

        formatCash(cash) {
            return parseFloat(cash || 0).toFixed(2);
        },

        formatTimeAgo(dateString) {
            const now = new Date();
            const date = new Date(dateString);
            const diffInMinutes = Math.floor((now - date) / (1000 * 60));

            if (diffInMinutes < 1) return "Just now";
            if (diffInMinutes < 60) return `${diffInMinutes}m ago`;

            const diffInHours = Math.floor(diffInMinutes / 60);
            if (diffInHours < 24) return `${diffInHours}h ago`;

            const diffInDays = Math.floor(diffInHours / 24);
            return `${diffInDays}d ago`;
        },

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
                if (!this.setupAxiosToken()) {
                    this.$router.push("/");
                    return;
                }

                const response = await axios.get("/api/user/profile");

                if (response.data.success) {
                    this.user = { ...this.user, ...response.data.user };

                    await this.fetchUserRank();
                    await this.fetchAllNotifications();
                    this.startNotificationPolling();
                } else {
                    throw new Error("Failed to fetch user data");
                }
            } catch (error) {
                console.error("Auth check failed:", error);
                localStorage.removeItem("auth-token");
                localStorage.removeItem("user");
                this.$router.push("/");
            }
        },

        async fetchUserRank() {
            try {
                const response = await axios.get("/api/leaderboard");
                if (response.data.success) {
                    const users = response.data.users;
                    const userIndex = users.findIndex(
                        (user) => user.id === this.user.id
                    );
                    this.userRank =
                        userIndex !== -1 ? `#${userIndex + 1}` : "Unranked";
                } else {
                    this.userRank = "N/A";
                }
            } catch (error) {
                console.error("Failed to fetch user rank:", error);
                this.userRank = "Error";
            }
        },

        async fetchAllNotifications() {
            this.loading = true;
            this.notificationError = null;

            try {
                const promises = [];

                // Fetch shop orders (only for users with shops)
                if (this.hasShop) {
                    promises.push(this.fetchPendingOrders());
                }

                // Fetch trade requests
                promises.push(this.fetchTradeRequests());

                // Fetch bet requests and referee requests
                promises.push(this.fetchBetRequests());

                await Promise.all(promises);
            } catch (error) {
                console.error("Failed to fetch notifications:", error);
                this.notificationError = "Failed to load notifications";
            } finally {
                this.loading = false;
            }
        },

        async fetchPendingOrders() {
            try {
                let response;

                // Both admins and shop owners can have shops, so check for both
                if (this.user.role === "admin") {
                    // Admin: Try to get their own shop's orders first, then all orders
                    try {
                        const shopResponse = await axios.get(
                            "/api/shops/dashboard/my-shop"
                        );
                        if (
                            shopResponse.data.success &&
                            shopResponse.data.shop
                        ) {
                            // Admin has their own shop - get shop-specific orders
                            const shopId = shopResponse.data.shop.id;
                            response = await axios.get(
                                `/api/shops/${shopId}/items/purchases/pending`
                            );
                        } else {
                            // Admin doesn't have their own shop - get all pending orders
                            response = await axios.get(
                                "/api/shop-items/purchases/all-pending"
                            );
                        }
                    } catch (adminError) {
                        // Fallback to all pending orders
                        response = await axios.get(
                            "/api/shop-items/purchases/all-pending"
                        );
                    }
                } else if (this.user.role === "shop_owner") {
                    // Shop owner sees only their shop's pending orders
                    const shopResponse = await axios.get(
                        "/api/shops/dashboard/my-shop"
                    );
                    if (shopResponse.data.success && shopResponse.data.shop) {
                        const shopId = shopResponse.data.shop.id;
                        response = await axios.get(
                            `/api/shops/${shopId}/items/purchases/pending`
                        );
                    } else {
                        return;
                    }
                }

                if (response && response.data.success) {
                    this.pendingOrders = response.data.purchases || [];
                    this.pendingOrdersCount = this.pendingOrders.length;
                } else {
                    console.warn(
                        "No valid response for pending orders:",
                        response?.data
                    );
                }
            } catch (error) {
                console.error("Failed to fetch pending orders:", error);
                this.pendingOrders = [];
                this.pendingOrdersCount = 0;
            }
        },

        async fetchTradeRequests() {
            try {
                const response = await axios.get("/api/trades");

                if (response.data.success) {
                    // Get all trades and filter by user involvement and status
                    const allTrades = response.data.trades || [];

                    // Incoming trades (received and pending)
                    this.tradeRequests = allTrades.filter(
                        (trade) =>
                            trade.receiver_id === this.user.id &&
                            trade.status === "pending"
                    );
                    this.tradeRequestsCount = this.tradeRequests.length;

                    // Sent trades (sent and still pending)
                    this.tradeSent = allTrades.filter(
                        (trade) =>
                            trade.sender_id === this.user.id &&
                            trade.status === "pending"
                    );
                    this.tradeSentCount = this.tradeSent.length;
                }
            } catch (error) {
                console.error("Failed to fetch trade requests:", error);
                this.tradeRequests = [];
                this.tradeSent = [];
                this.tradeRequestsCount = 0;
                this.tradeSentCount = 0;
            }
        },

        async fetchBetRequests() {
            try {
                const response = await axios.get("/api/bets");

                if (response.data.success) {
                    // Incoming bet requests
                    this.betRequests = response.data.incoming_bets || [];
                    this.betRequestsCount = this.betRequests.length;

                    // Sent bets (my pending bets where I'm the creator)
                    const myBets = response.data.my_bets || [];
                    this.betsSent = myBets.filter(
                        (bet) =>
                            bet.creator_id === this.user.id &&
                            bet.status === "pending"
                    );
                    this.betsSentCount = this.betsSent.length;

                    // Referee requests
                    this.refereeRequests = response.data.referee_bets || [];
                    this.refereeRequestsCount = this.refereeRequests.length;
                }
            } catch (error) {
                console.error("Failed to fetch bet requests:", error);
                this.betRequests = [];
                this.betsSent = [];
                this.refereeRequests = [];
                this.betRequestsCount = 0;
                this.betsSentCount = 0;
                this.refereeRequestsCount = 0;
            }
        },

        async refreshNotifications() {
            await this.fetchAllNotifications();
        },

        startNotificationPolling() {
            // Poll every 30 seconds
            this.notificationPollingInterval = setInterval(() => {
                this.fetchAllNotifications();
            }, 30000);
        },

        stopNotificationPolling() {
            if (this.notificationPollingInterval) {
                clearInterval(this.notificationPollingInterval);
                this.notificationPollingInterval = null;
            }
        },

        toggleNotifications() {
            this.showNotifications = !this.showNotifications;
            if (this.showNotifications) {
                // Set default active tab based on what notifications exist
                if (this.hasShop && this.pendingOrdersCount > 0) {
                    this.activeNotificationTab = "orders";
                } else if (this.tradeRequestsCount > 0) {
                    this.activeNotificationTab = "trades";
                } else if (this.betRequestsCount > 0) {
                    this.activeNotificationTab = "bets";
                } else if (this.refereeRequestsCount > 0) {
                    this.activeNotificationTab = "referee";
                } else {
                    // Default to first available tab
                    this.activeNotificationTab =
                        this.notificationTabs[0]?.key || "trades";
                }
            }
        },

        handleNotificationClick(type, item) {
            this.showNotifications = false;

            switch (type) {
                case "order":
                    this.$router.push("/my-shop?tab=orders");
                    break;
                case "trade":
                    this.$router.push("/trade?tab=incoming");
                    break;
                case "bet":
                    this.$router.push("/bet?tab=incoming");
                    break;
                case "referee":
                    this.$router.push("/bet?tab=referee");
                    break;
            }
        },

        navigateToNotificationType() {
            this.handleNotificationClick(this.activeNotificationTab, null);
        },

        handleClickOutside(event) {
            if (!event.target.closest(".relative")) {
                this.showNotifications = false;
            }
        },

        async logout() {
            try {
                this.setupAxiosToken();
                await axios.post("/api/logout");
            } catch (error) {
                console.error("Logout API call failed:", error);
            } finally {
                this.stopNotificationPolling();
                localStorage.removeItem("auth-token");
                localStorage.removeItem("user");
                delete axios.defaults.headers.common["Authorization"];
                this.$router.push("/");
            }
        },
    },

    async mounted() {
        await this.checkAuth();
        document.addEventListener("click", this.handleClickOutside);
    },

    beforeUnmount() {
        this.stopNotificationPolling();
        document.removeEventListener("click", this.handleClickOutside);
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

/* Smooth transitions for profile images and stat cards */
img {
    transition: all 0.2s ease-in-out;
}

img:hover {
    transform: scale(1.05);
}

/* Hover effects for stat cards */
.bg-green-50:hover,
.bg-yellow-50:hover,
.bg-blue-50:hover,
.bg-purple-50:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Notification dropdown animations */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Pulse animation for notification badge */
@keyframes pulse {
    0%,
    100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.animate-pulse {
    animation: pulse 2s infinite;
}

/* Bounce animation for new notifications */
@keyframes bounce {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

.animate-bounce {
    animation: bounce 1s infinite;
}
</style>
