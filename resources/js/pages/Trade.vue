<template>
    <Layout>
        <div class="min-h-screen bg-gray-50 p-6">
            <div class="max-w-6xl mx-auto space-y-6">
                <!-- Header -->
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <div
                        class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
                    >
                        <div>
                            <h1
                                class="text-4xl font-bold text-gray-800 mb-2 flex items-center gap-3"
                            >
                                <i
                                    class="pi pi-arrow-right-arrow-left text-blue-500"
                                ></i>
                                Point Trading
                            </h1>
                            <p class="text-gray-600">
                                Trade points with other players
                            </p>
                        </div>
                        <button
                            @click="showCreateTradeModal = true"
                            class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all flex items-center gap-2 shadow-lg"
                        >
                            <i class="pi pi-plus"></i>
                            Create Trade Offer
                        </button>
                    </div>
                </div>

                <!-- Trade Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white rounded-xl p-6 shadow-lg">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                            >
                                <i class="pi pi-send text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Sent Trades</p>
                                <p class="text-2xl font-bold text-gray-800">
                                    {{ sentTrades.length }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-lg">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center"
                            >
                                <i
                                    class="pi pi-inbox text-green-600 text-xl"
                                ></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">
                                    Received Trades
                                </p>
                                <p class="text-2xl font-bold text-gray-800">
                                    {{ receivedTrades.length }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-lg">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center"
                            >
                                <i
                                    class="pi pi-clock text-yellow-600 text-xl"
                                ></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Pending</p>
                                <p class="text-2xl font-bold text-gray-800">
                                    {{ pendingTrades.length }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-lg">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center"
                            >
                                <i
                                    class="pi pi-wallet text-purple-600 text-xl"
                                ></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Your Points</p>
                                <p class="text-2xl font-bold text-gray-800">
                                    {{ formatPoints(currentUser.points || 0) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="border-b border-gray-200">
                        <nav class="flex">
                            <button
                                @click="activeTab = 'received'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                    activeTab === 'received'
                                        ? 'border-blue-500 text-blue-600 bg-blue-50'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                ]"
                            >
                                <i class="pi pi-inbox mr-2"></i>
                                Received Trades ({{ receivedTrades.length }})
                            </button>
                            <button
                                @click="activeTab = 'sent'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                    activeTab === 'sent'
                                        ? 'border-blue-500 text-blue-600 bg-blue-50'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                ]"
                            >
                                <i class="pi pi-send mr-2"></i>
                                Sent Trades ({{ sentTrades.length }})
                            </button>
                        </nav>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="p-8 text-center">
                        <i
                            class="pi pi-spin pi-spinner text-blue-500 text-2xl mb-4"
                        ></i>
                        <p class="text-gray-600">Loading trades...</p>
                    </div>

                    <!-- Received Trades Tab -->
                    <div v-else-if="activeTab === 'received'" class="p-6">
                        <div
                            v-if="receivedTrades.length === 0"
                            class="text-center py-12"
                        >
                            <i
                                class="pi pi-inbox text-gray-300 text-5xl mb-4"
                            ></i>
                            <h3 class="text-lg font-medium text-gray-600 mb-2">
                                No Trade Offers
                            </h3>
                            <p class="text-gray-500">
                                You haven't received any trade offers yet.
                            </p>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="trade in receivedTrades"
                                :key="trade.id"
                                class="border rounded-lg p-6 hover:shadow-md transition-shadow"
                                :class="{
                                    'border-yellow-200 bg-yellow-50':
                                        trade.status === 'pending',
                                    'border-green-200 bg-green-50':
                                        trade.status === 'accepted',
                                    'border-red-200 bg-red-50':
                                        trade.status === 'rejected',
                                }"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <!-- Sender Profile -->
                                        <div class="relative">
                                            <div
                                                class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-200"
                                            >
                                                <img
                                                    v-if="
                                                        trade.sender
                                                            .profile_image
                                                    "
                                                    :src="`/storage/profiles/${trade.sender.profile_image}`"
                                                    :alt="trade.sender.name"
                                                    class="w-full h-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                                                >
                                                    <i
                                                        class="pi pi-user text-white text-lg"
                                                    ></i>
                                                </div>
                                            </div>
                                            <div
                                                v-if="trade.sender.is_premium"
                                                class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full border border-white flex items-center justify-center"
                                            >
                                                <i
                                                    class="pi pi-star text-white text-xs"
                                                ></i>
                                            </div>
                                        </div>

                                        <div>
                                            <h4
                                                class="font-semibold text-gray-800"
                                            >
                                                {{
                                                    trade.sender.name ||
                                                    "Unknown User"
                                                }}
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                Wants to send you
                                                {{
                                                    formatPoints(
                                                        trade.send_amount
                                                    )
                                                }}
                                                points
                                                <span
                                                    v-if="
                                                        trade.receive_amount > 0
                                                    "
                                                >
                                                    and receive
                                                    {{
                                                        formatPoints(
                                                            trade.receive_amount
                                                        )
                                                    }}
                                                    points
                                                </span>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{
                                                    formatDate(trade.created_at)
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Trade Status & Actions -->
                                    <div class="flex items-center gap-3">
                                        <div class="text-right mr-4">
                                            <div
                                                v-if="trade.send_amount > 0"
                                                class="text-lg font-bold text-green-600"
                                            >
                                                +{{
                                                    formatPoints(
                                                        trade.send_amount
                                                    )
                                                }}
                                            </div>
                                            <div
                                                v-if="trade.receive_amount > 0"
                                                class="text-lg font-bold text-red-600"
                                            >
                                                -{{
                                                    formatPoints(
                                                        trade.receive_amount
                                                    )
                                                }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                points
                                            </div>
                                        </div>

                                        <div
                                            v-if="trade.status === 'pending'"
                                            class="flex gap-2"
                                        >
                                            <button
                                                @click="
                                                    confirmTradeAction(
                                                        trade.id,
                                                        'accepted',
                                                        `Accept trade offer from ${trade.sender.name}?`
                                                    )
                                                "
                                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                                                :disabled="
                                                    processingTrade === trade.id
                                                "
                                            >
                                                <i class="pi pi-check"></i>
                                                Accept
                                            </button>
                                            <button
                                                @click="
                                                    confirmTradeAction(
                                                        trade.id,
                                                        'rejected',
                                                        `Reject trade offer from ${trade.sender.name}?`
                                                    )
                                                "
                                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                                                :disabled="
                                                    processingTrade === trade.id
                                                "
                                            >
                                                <i class="pi pi-times"></i>
                                                Reject
                                            </button>
                                        </div>
                                        <div v-else class="text-right">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800':
                                                        trade.status ===
                                                        'accepted',
                                                    'bg-red-100 text-red-800':
                                                        trade.status ===
                                                        'rejected',
                                                }"
                                                class="px-3 py-1 rounded-full text-sm font-medium"
                                            >
                                                {{
                                                    trade.status
                                                        .charAt(0)
                                                        .toUpperCase() +
                                                    trade.status.slice(1)
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sent Trades Tab -->
                    <div v-else-if="activeTab === 'sent'" class="p-6">
                        <div
                            v-if="sentTrades.length === 0"
                            class="text-center py-12"
                        >
                            <i
                                class="pi pi-send text-gray-300 text-5xl mb-4"
                            ></i>
                            <h3 class="text-lg font-medium text-gray-600 mb-2">
                                No Sent Trades
                            </h3>
                            <p class="text-gray-500">
                                You haven't sent any trade offers yet.
                            </p>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="trade in sentTrades"
                                :key="trade.id"
                                class="border rounded-lg p-6 hover:shadow-md transition-shadow"
                                :class="{
                                    'border-yellow-200 bg-yellow-50':
                                        trade.status === 'pending',
                                    'border-green-200 bg-green-50':
                                        trade.status === 'accepted',
                                    'border-red-200 bg-red-50':
                                        trade.status === 'rejected',
                                }"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <!-- Receiver Profile -->
                                        <div class="relative">
                                            <div
                                                class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-200"
                                            >
                                                <img
                                                    v-if="
                                                        trade.receiver
                                                            .profile_image
                                                    "
                                                    :src="`/storage/profiles/${trade.receiver.profile_image}`"
                                                    :alt="trade.receiver.name"
                                                    class="w-full h-full object-cover"
                                                />
                                                <div
                                                    v-else
                                                    class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                                                >
                                                    <i
                                                        class="pi pi-user text-white text-lg"
                                                    ></i>
                                                </div>
                                            </div>
                                            <div
                                                v-if="trade.receiver.is_premium"
                                                class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full border border-white flex items-center justify-center"
                                            >
                                                <i
                                                    class="pi pi-star text-white text-xs"
                                                ></i>
                                            </div>
                                        </div>

                                        <div>
                                            <h4
                                                class="font-semibold text-gray-800"
                                            >
                                                {{
                                                    trade.receiver.name ||
                                                    "Unknown User"
                                                }}
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                You offered
                                                {{
                                                    formatPoints(
                                                        trade.send_amount
                                                    )
                                                }}
                                                points
                                                <span
                                                    v-if="
                                                        trade.receive_amount > 0
                                                    "
                                                >
                                                    for
                                                    {{
                                                        formatPoints(
                                                            trade.receive_amount
                                                        )
                                                    }}
                                                    points
                                                </span>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{
                                                    formatDate(trade.created_at)
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Trade Status -->
                                    <div class="flex items-center gap-3">
                                        <div class="text-right mr-4">
                                            <div
                                                v-if="trade.send_amount > 0"
                                                class="text-lg font-bold text-red-600"
                                            >
                                                -{{
                                                    formatPoints(
                                                        trade.send_amount
                                                    )
                                                }}
                                            </div>
                                            <div
                                                v-if="trade.receive_amount > 0"
                                                class="text-lg font-bold text-green-600"
                                            >
                                                +{{
                                                    formatPoints(
                                                        trade.receive_amount
                                                    )
                                                }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                points
                                            </div>
                                        </div>

                                        <div>
                                            <span
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800':
                                                        trade.status ===
                                                        'pending',
                                                    'bg-green-100 text-green-800':
                                                        trade.status ===
                                                        'accepted',
                                                    'bg-red-100 text-red-800':
                                                        trade.status ===
                                                        'rejected',
                                                }"
                                                class="px-3 py-1 rounded-full text-sm font-medium"
                                            >
                                                {{
                                                    trade.status
                                                        .charAt(0)
                                                        .toUpperCase() +
                                                    trade.status.slice(1)
                                                }}
                                            </span>
                                        </div>

                                        <div v-if="trade.status === 'pending'">
                                            <button
                                                @click="
                                                    confirmCancelTrade(
                                                        trade.id,
                                                        trade.receiver.name
                                                    )
                                                "
                                                class="text-red-600 hover:text-red-800 transition-colors p-2 hover:bg-red-50 rounded-lg"
                                                :disabled="
                                                    processingTrade === trade.id
                                                "
                                                title="Cancel trade"
                                            >
                                                <i
                                                    class="pi pi-trash text-lg"
                                                ></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Trade Modal -->
        <div
            v-if="showCreateTradeModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showCreateTradeModal = false"
        >
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">
                        Create Trade Offer
                    </h3>
                    <button
                        @click="closeCreateTradeModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <i class="pi pi-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="createTrade" class="space-y-6">
                    <!-- User Search -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Trade Partner</label
                        >
                        <div class="relative">
                            <input
                                v-model="userSearchQuery"
                                @input="searchUsers"
                                @focus="showUserDropdown = true"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Search username..."
                                autocomplete="off"
                            />
                            <i
                                class="pi pi-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                            ></i>

                            <!-- User Dropdown -->
                            <div
                                v-if="
                                    showUserDropdown &&
                                    (searchResults.length > 0 ||
                                        userSearchQuery.length > 0)
                                "
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                            >
                                <div
                                    v-if="searchingUsers"
                                    class="p-3 text-center text-gray-500"
                                >
                                    <i class="pi pi-spin pi-spinner mr-2"></i>
                                    Searching...
                                </div>
                                <div
                                    v-else-if="
                                        searchResults.length === 0 &&
                                        userSearchQuery.length > 0
                                    "
                                    class="p-3 text-center text-gray-500"
                                >
                                    No users found
                                </div>
                                <div
                                    v-for="user in searchResults"
                                    :key="user.id"
                                    @click="selectUser(user)"
                                    class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                >
                                    <div class="relative">
                                        <div
                                            class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-200"
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
                                                    class="pi pi-user text-white text-sm"
                                                ></i>
                                            </div>
                                        </div>
                                        <div
                                            v-if="user.is_premium"
                                            class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full border border-white flex items-center justify-center"
                                        >
                                            <i
                                                class="pi pi-star text-white text-xs"
                                            ></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">
                                            {{ user.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Level {{ user.level }} •
                                            {{
                                                formatPoints(user.points)
                                            }}
                                            points
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected User Display -->
                        <div
                            v-if="selectedUser"
                            class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200"
                        >
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div
                                        class="w-10 h-10 rounded-full overflow-hidden border-2 border-blue-300"
                                    >
                                        <img
                                            v-if="selectedUser.profile_image"
                                            :src="`/storage/profiles/${selectedUser.profile_image}`"
                                            :alt="selectedUser.name"
                                            class="w-full h-full object-cover"
                                        />
                                        <div
                                            v-else
                                            class="w-full h-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center"
                                        >
                                            <i
                                                class="pi pi-user text-white text-sm"
                                            ></i>
                                        </div>
                                    </div>
                                    <div
                                        v-if="selectedUser.is_premium"
                                        class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full border border-white flex items-center justify-center"
                                    >
                                        <i
                                            class="pi pi-star text-white text-xs"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">
                                        {{ selectedUser.name }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Level {{ selectedUser.level }} •
                                        {{
                                            formatPoints(selectedUser.points)
                                        }}
                                        points
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="clearSelectedUser"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <i class="pi pi-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Trade Details -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- You Send -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                <i class="pi pi-arrow-up text-red-500 mr-1"></i>
                                You Send
                            </label>
                            <input
                                v-model.number="newTrade.sendAmount"
                                type="number"
                                min="0"
                                :max="currentUser.points"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0"
                            />
                            <p class="text-xs text-gray-500 mt-1">
                                Max: {{ formatPoints(currentUser.points || 0) }}
                            </p>
                        </div>

                        <!-- You Receive -->
                        <!-- <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                <i
                                    class="pi pi-arrow-down text-green-500 mr-1"
                                ></i>
                                You Receive
                            </label>
                            <input
                                v-model.number="newTrade.receiveAmount"
                                type="number"
                                min="0"
                                :max="
                                    selectedUser ? selectedUser.points : 999999
                                "
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="0"
                            />
                            <p class="text-xs text-gray-500 mt-1">
                                Max:
                                {{
                                    selectedUser
                                        ? formatPoints(selectedUser.points)
                                        : "∞"
                                }}
                            </p>
                        </div> -->
                    </div>

                    <!-- Trade Summary -->
                    <div
                        v-if="tradeSummary.isValid"
                        class="bg-gray-50 rounded-lg p-4"
                    >
                        <h4 class="font-medium text-gray-800 mb-2">
                            Trade Summary
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Your balance:</span>
                                <span class="font-medium"
                                    >{{
                                        formatPoints(currentUser.points || 0)
                                    }}
                                    points</span
                                >
                            </div>
                            <div class="flex justify-between text-red-600">
                                <span>You send:</span>
                                <span class="font-medium"
                                    >-{{
                                        formatPoints(newTrade.sendAmount || 0)
                                    }}
                                    points</span
                                >
                            </div>
                            <div class="flex justify-between text-green-600">
                                <span>You receive:</span>
                                <span class="font-medium"
                                    >+{{
                                        formatPoints(
                                            newTrade.receiveAmount || 0
                                        )
                                    }}
                                    points</span
                                >
                            </div>
                            <div
                                class="border-t border-gray-200 pt-2 flex justify-between font-medium"
                            >
                                <span>New balance:</span>
                                <span
                                    :class="
                                        tradeSummary.netChange >= 0
                                            ? 'text-green-600'
                                            : 'text-red-600'
                                    "
                                >
                                    {{
                                        formatPoints(tradeSummary.newBalance)
                                    }}
                                    points ({{
                                        tradeSummary.netChange >= 0 ? "+" : ""
                                    }}{{
                                        formatPoints(tradeSummary.netChange)
                                    }})
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Error Messages -->
                    <div
                        v-if="tradeErrors.length > 0"
                        class="bg-red-50 border border-red-200 rounded-lg p-3"
                    >
                        <ul class="text-sm text-red-600 space-y-1">
                            <li v-for="error in tradeErrors" :key="error">
                                <i class="pi pi-exclamation-triangle mr-1"></i>
                                {{ error }}
                            </li>
                        </ul>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeCreateTradeModal"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="
                                creatingTrade ||
                                !tradeSummary.isValid ||
                                tradeErrors.length > 0
                            "
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <i
                                v-if="creatingTrade"
                                class="pi pi-spin pi-spinner mr-2"
                            ></i>
                            Send Trade Offer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Confirmation Dialog -->
        <div
            v-if="showConfirmDialog"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="cancelConfirmation"
        >
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div
                        class="w-12 h-12 rounded-full flex items-center justify-center"
                        :class="{
                            'bg-green-100': confirmAction.type === 'accept',
                            'bg-red-100':
                                confirmAction.type === 'reject' ||
                                confirmAction.type === 'cancel',
                        }"
                    >
                        <i
                            :class="{
                                'pi pi-check text-green-600':
                                    confirmAction.type === 'accept',
                                'pi pi-times text-red-600':
                                    confirmAction.type === 'reject',
                                'pi pi-trash text-red-600':
                                    confirmAction.type === 'cancel',
                            }"
                            class="text-xl"
                        ></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">
                            {{ confirmAction.title }}
                        </h3>
                        <p class="text-gray-600">{{ confirmAction.message }}</p>
                    </div>
                </div>

                <!-- Trade Details Preview -->
                <div
                    v-if="confirmAction.trade"
                    class="bg-gray-50 rounded-lg p-4 mb-6"
                >
                    <div class="space-y-2 text-sm">
                        <div
                            v-if="confirmAction.trade.send_amount > 0"
                            class="flex justify-between"
                        >
                            <span class="text-gray-600">
                                {{
                                    confirmAction.type === "accept"
                                        ? "You will receive:"
                                        : "You will lose:"
                                }}
                            </span>
                            <span
                                :class="
                                    confirmAction.type === 'accept'
                                        ? 'text-green-600 font-medium'
                                        : 'text-red-600 font-medium'
                                "
                            >
                                {{ confirmAction.type === "accept" ? "+" : "-"
                                }}{{
                                    formatPoints(
                                        confirmAction.trade.send_amount
                                    )
                                }}
                                points
                            </span>
                        </div>
                        <div
                            v-if="confirmAction.trade.receive_amount > 0"
                            class="flex justify-between"
                        >
                            <span class="text-gray-600">
                                {{
                                    confirmAction.type === "accept"
                                        ? "You will give:"
                                        : "They will keep:"
                                }}
                            </span>
                            <span
                                :class="
                                    confirmAction.type === 'accept'
                                        ? 'text-red-600 font-medium'
                                        : 'text-gray-600'
                                "
                            >
                                {{ confirmAction.type === "accept" ? "-" : ""
                                }}{{
                                    formatPoints(
                                        confirmAction.trade.receive_amount
                                    )
                                }}
                                points
                            </span>
                        </div>
                        <div
                            v-if="confirmAction.type === 'accept'"
                            class="border-t border-gray-200 pt-2 flex justify-between font-medium"
                        >
                            <span>Net change:</span>
                            <span
                                :class="
                                    confirmAction.trade.send_amount -
                                        confirmAction.trade.receive_amount >=
                                    0
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                "
                            >
                                {{
                                    confirmAction.trade.send_amount -
                                        confirmAction.trade.receive_amount >=
                                    0
                                        ? "+"
                                        : ""
                                }}{{
                                    formatPoints(
                                        confirmAction.trade.send_amount -
                                            confirmAction.trade.receive_amount
                                    )
                                }}
                                points
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button
                        @click="cancelConfirmation"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        :disabled="processingConfirmation"
                    >
                        Cancel
                    </button>
                    <button
                        @click="executeConfirmedAction"
                        :disabled="processingConfirmation"
                        class="flex-1 px-4 py-2 rounded-lg transition-colors text-white"
                        :class="{
                            'bg-green-500 hover:bg-green-600':
                                confirmAction.type === 'accept',
                            'bg-red-500 hover:bg-red-600':
                                confirmAction.type === 'reject' ||
                                confirmAction.type === 'cancel',
                        }"
                    >
                        <i
                            v-if="processingConfirmation"
                            class="pi pi-spin pi-spinner mr-2"
                        ></i>
                        {{ confirmAction.confirmText }}
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
export default {
    data() {
        return {
            activeTab: "received",
            loading: true,
            trades: [],
            currentUser: {},
            showCreateTradeModal: false,
            newTrade: {
                sendAmount: 0,
                receiveAmount: 0,
            },
            creatingTrade: false,
            processingTrade: null,

            // User search
            userSearchQuery: "",
            searchResults: [],
            selectedUser: null,
            showUserDropdown: false,
            searchingUsers: false,
            searchTimeout: null,

            // Confirmation dialog
            showConfirmDialog: false,
            confirmAction: {
                type: null, // 'accept', 'reject', 'cancel'
                tradeId: null,
                trade: null,
                title: "",
                message: "",
                confirmText: "",
            },
            processingConfirmation: false,
        };
    },
    computed: {
        receivedTrades() {
            return this.trades.filter(
                (trade) => trade.receiver_id === this.currentUser.id
            );
        },
        sentTrades() {
            return this.trades.filter(
                (trade) => trade.sender_id === this.currentUser.id
            );
        },
        pendingTrades() {
            return this.trades.filter((trade) => trade.status === "pending");
        },
        tradeSummary() {
            const sendAmount = this.newTrade.sendAmount || 0;
            const receiveAmount = this.newTrade.receiveAmount || 0;
            const currentBalance = this.currentUser.points || 0;
            const netChange = receiveAmount - sendAmount;
            const newBalance = currentBalance + netChange;

            return {
                netChange,
                newBalance,
                isValid:
                    this.selectedUser && (sendAmount > 0 || receiveAmount > 0),
            };
        },
        tradeErrors() {
            const errors = [];

            if (!this.selectedUser) {
                errors.push("Please select a trade partner");
            }

            if (
                this.selectedUser &&
                this.selectedUser.id === this.currentUser.id
            ) {
                errors.push("You cannot trade with yourself");
            }

            const sendAmount = this.newTrade.sendAmount || 0;
            const receiveAmount = this.newTrade.receiveAmount || 0;

            if (sendAmount === 0 && receiveAmount === 0) {
                errors.push("Trade must include points to send or receive");
            }

            if (sendAmount > (this.currentUser.points || 0)) {
                errors.push("You cannot send more points than you have");
            }

            if (this.selectedUser && receiveAmount > this.selectedUser.points) {
                errors.push("Partner does not have enough points to send");
            }

            return errors;
        },
    },
    methods: {
        async fetchTrades() {
            try {
                this.loading = true;
                console.log("Fetching trades...");

                const response = await axios.get("/api/trades");
                console.log("Trades response:", response.data);

                if (response.data.success) {
                    this.trades = response.data.trades || [];
                    this.currentUser = response.data.user || {};
                    console.log("Trades loaded:", this.trades.length);
                    console.log("Current user:", this.currentUser);
                } else {
                    throw new Error(
                        response.data.message || "Failed to fetch trades"
                    );
                }
            } catch (error) {
                console.error("Error fetching trades:", error);
                console.error("Error response:", error.response?.data);

                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to load trades",
                });

                // Set defaults to prevent crashes
                this.trades = [];
                this.currentUser = {};
            } finally {
                this.loading = false;
            }
        },

        // User search methods
        searchUsers() {
            clearTimeout(this.searchTimeout);

            if (this.userSearchQuery.length < 2) {
                this.searchResults = [];
                return;
            }

            this.searchTimeout = setTimeout(async () => {
                try {
                    this.searchingUsers = true;
                    const response = await axios.get(
                        `/api/users/search?q=${encodeURIComponent(
                            this.userSearchQuery
                        )}`
                    );

                    if (response.data.success) {
                        // Filter out current user
                        this.searchResults = response.data.users.filter(
                            (user) => user.id !== this.currentUser.id
                        );
                    }
                } catch (error) {
                    console.error("Error searching users:", error);
                    this.searchResults = [];
                } finally {
                    this.searchingUsers = false;
                }
            }, 300);
        },

        selectUser(user) {
            this.selectedUser = user;
            this.userSearchQuery = user.name;
            this.showUserDropdown = false;
            this.searchResults = [];
        },

        clearSelectedUser() {
            this.selectedUser = null;
            this.userSearchQuery = "";
            this.searchResults = [];
        },

        closeCreateTradeModal() {
            this.showCreateTradeModal = false;
            this.clearSelectedUser();
            this.newTrade = { sendAmount: 0, receiveAmount: 0 };
        },

        async createTrade() {
            if (!this.selectedUser || this.tradeErrors.length > 0) return;

            try {
                this.creatingTrade = true;
                const response = await axios.post("/api/trades", {
                    receiver_id: this.selectedUser.id,
                    send_amount: this.newTrade.sendAmount || 0,
                    receive_amount: this.newTrade.receiveAmount || 0,
                });

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Trade offer sent successfully!",
                    });

                    this.closeCreateTradeModal();
                    await this.fetchTrades();
                } else {
                    throw new Error(
                        response.data.message || "Failed to create trade"
                    );
                }
            } catch (error) {
                console.error("Error creating trade:", error);
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to create trade offer",
                });
            } finally {
                this.creatingTrade = false;
            }
        },

        // Confirmation dialog methods
        confirmTradeAction(tradeId, action, message) {
            const trade = this.trades.find((t) => t.id === tradeId);

            this.confirmAction = {
                type: action === "accepted" ? "accept" : "reject",
                tradeId: tradeId,
                trade: trade,
                title:
                    action === "accepted"
                        ? "Accept Trade Offer"
                        : "Reject Trade Offer",
                message: message,
                confirmText:
                    action === "accepted" ? "Accept Trade" : "Reject Trade",
            };

            this.showConfirmDialog = true;
        },

        confirmCancelTrade(tradeId, receiverName) {
            const trade = this.trades.find((t) => t.id === tradeId);

            this.confirmAction = {
                type: "cancel",
                tradeId: tradeId,
                trade: trade,
                title: "Cancel Trade Offer",
                message: `Cancel your trade offer to ${receiverName}?`,
                confirmText: "Cancel Trade",
            };

            this.showConfirmDialog = true;
        },

        async executeConfirmedAction() {
            try {
                this.processingConfirmation = true;

                if (this.confirmAction.type === "accept") {
                    await this.respondToTrade(
                        this.confirmAction.tradeId,
                        "accepted"
                    );
                } else if (this.confirmAction.type === "reject") {
                    await this.respondToTrade(
                        this.confirmAction.tradeId,
                        "rejected"
                    );
                } else if (this.confirmAction.type === "cancel") {
                    await this.cancelTrade(this.confirmAction.tradeId);
                }

                this.cancelConfirmation();
            } catch (error) {
                // Error handling is done in the individual methods
            } finally {
                this.processingConfirmation = false;
            }
        },

        cancelConfirmation() {
            this.showConfirmDialog = false;
            this.confirmAction = {
                type: null,
                tradeId: null,
                trade: null,
                title: "",
                message: "",
                confirmText: "",
            };
            this.processingConfirmation = false;
        },

        async respondToTrade(tradeId, status) {
            try {
                this.processingTrade = tradeId;
                const response = await axios.patch(`/api/trades/${tradeId}`, {
                    status,
                });

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: `Trade ${status} successfully!`,
                    });

                    await this.fetchTrades();
                } else {
                    throw new Error(
                        response.data.message || "Failed to respond to trade"
                    );
                }
            } catch (error) {
                console.error("Error responding to trade:", error);
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to respond to trade",
                });
            } finally {
                this.processingTrade = null;
            }
        },

        async cancelTrade(tradeId) {
            try {
                this.processingTrade = tradeId;
                const response = await axios.delete(`/api/trades/${tradeId}`);

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Trade cancelled successfully!",
                    });

                    await this.fetchTrades();
                } else {
                    throw new Error(
                        response.data.message || "Failed to cancel trade"
                    );
                }
            } catch (error) {
                console.error("Error cancelling trade:", error);
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message ||
                        "Failed to cancel trade",
                });
            } finally {
                this.processingTrade = null;
            }
        },

        formatPoints(points) {
            return (points || 0).toLocaleString();
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString("en-US", {
                year: "numeric",
                month: "short",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        },
    },
    mounted() {
        // Set up authentication token
        const token = localStorage.getItem("auth-token");
        if (token) {
            axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
        }

        this.fetchTrades();

        // Close dropdown when clicking outside
        document.addEventListener("click", (e) => {
            if (!this.$el?.contains(e.target)) {
                this.showUserDropdown = false;
            }
        });
    },
};
</script>
