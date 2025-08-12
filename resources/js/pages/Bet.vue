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
                                <i class="pi pi-trophy text-yellow-500"></i>
                                Betting Arena
                            </h1>
                            <p class="text-gray-600">
                                Challenge others and prove your worth
                            </p>
                        </div>
                        <button
                            @click="showCreateDialog = true"
                            class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all flex items-center gap-2 shadow-lg"
                        >
                            <i class="pi pi-plus"></i>
                            Create New Bet
                        </button>
                    </div>
                </div>

                <!-- Bet Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white rounded-xl p-6 shadow-lg">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center"
                            >
                                <i class="pi pi-send text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">My Bets</p>
                                <p class="text-2xl font-bold text-gray-800">
                                    {{ myBets.length }}
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
                                    Incoming Bets
                                </p>
                                <p class="text-2xl font-bold text-gray-800">
                                    {{ incomingBets.length }}
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
                                <p class="text-sm text-gray-600">
                                    Ongoing Bets
                                </p>
                                <p class="text-2xl font-bold text-gray-800">
                                    {{ ongoingBets.length }}
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
                                @click="activeTab = 'incoming'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                    activeTab === 'incoming'
                                        ? 'border-blue-500 text-blue-600 bg-blue-50'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                ]"
                            >
                                <i class="pi pi-inbox mr-2"></i>
                                Incoming Bets ({{ incomingBets.length }})
                            </button>
                            <button
                                @click="activeTab = 'my-bets'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                    activeTab === 'my-bets'
                                        ? 'border-blue-500 text-blue-600 bg-blue-50'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                ]"
                            >
                                <i class="pi pi-send mr-2"></i>
                                My Bets ({{ myBets.length }})
                            </button>
                            <button
                                @click="activeTab = 'ongoing'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                    activeTab === 'ongoing'
                                        ? 'border-blue-500 text-blue-600 bg-blue-50'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                ]"
                            >
                                <i class="pi pi-clock mr-2"></i>
                                Ongoing Bets ({{ ongoingBets.length }})
                            </button>
                            <button
                                @click="activeTab = 'referee'"
                                :class="[
                                    'px-6 py-4 text-sm font-medium border-b-2 transition-colors',
                                    activeTab === 'referee'
                                        ? 'border-blue-500 text-blue-600 bg-blue-50'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                ]"
                            >
                                <i class="pi pi-balance-scale mr-2"></i>
                                Referee Requests ({{ refereeBets.length }})
                            </button>
                        </nav>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loading" class="p-8 text-center">
                        <i
                            class="pi pi-spin pi-spinner text-blue-500 text-2xl mb-4"
                        ></i>
                        <p class="text-gray-600">Loading bets...</p>
                    </div>

                    <!-- Incoming Bets Tab -->
                    <div v-else-if="activeTab === 'incoming'" class="p-6">
                        <div
                            v-if="incomingBets.length === 0"
                            class="text-center py-12"
                        >
                            <i
                                class="pi pi-inbox text-gray-300 text-5xl mb-4"
                            ></i>
                            <h3 class="text-lg font-medium text-gray-600 mb-2">
                                No Incoming Bets
                            </h3>
                            <p class="text-gray-500">
                                You haven't received any bet challenges yet.
                            </p>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="bet in incomingBets"
                                :key="bet.id"
                                class="border rounded-lg p-6 hover:shadow-md transition-shadow border-yellow-200 bg-yellow-50"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <!-- Creator Profile -->
                                        <div class="relative">
                                            <div
                                                class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-200"
                                            >
                                                <img
                                                    v-if="
                                                        bet.creator
                                                            .profile_image
                                                    "
                                                    :src="`/storage/profiles/${bet.creator.profile_image}`"
                                                    :alt="bet.creator.name"
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
                                                v-if="bet.creator.is_premium"
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
                                                {{ bet.creator.name }}
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                Challenges you to a bet
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Referee:
                                                {{ bet.referee.name }} •
                                                {{ formatDate(bet.created_at) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Bet Amount & Actions -->
                                    <div class="flex items-center gap-3">
                                        <div class="text-right mr-4">
                                            <div
                                                class="text-lg font-bold text-yellow-600"
                                            >
                                                {{
                                                    formatPoints(
                                                        bet.points_amount
                                                    )
                                                }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                points
                                            </div>
                                        </div>

                                        <div class="flex gap-2">
                                            <button
                                                @click="acceptBet(bet.id)"
                                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                                                :disabled="
                                                    processingBet === bet.id
                                                "
                                            >
                                                <i class="pi pi-check"></i>
                                                Accept
                                            </button>
                                            <button
                                                @click="rejectBet(bet.id)"
                                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                                                :disabled="
                                                    processingBet === bet.id
                                                "
                                            >
                                                <i class="pi pi-times"></i>
                                                Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Bets Tab -->
                    <div v-else-if="activeTab === 'my-bets'" class="p-6">
                        <div
                            v-if="myBets.length === 0"
                            class="text-center py-12"
                        >
                            <i
                                class="pi pi-send text-gray-300 text-5xl mb-4"
                            ></i>
                            <h3 class="text-lg font-medium text-gray-600 mb-2">
                                No Bets Created
                            </h3>
                            <p class="text-gray-500">
                                You haven't created any bets yet.
                            </p>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="bet in myBets"
                                :key="bet.id"
                                class="border rounded-lg p-6 hover:shadow-md transition-shadow"
                                :class="{
                                    'border-yellow-200 bg-yellow-50':
                                        bet.status === 'pending',
                                    'border-blue-200 bg-blue-50':
                                        bet.status === 'accepted',
                                    'border-red-200 bg-red-50':
                                        bet.status === 'rejected' ||
                                        bet.status === 'cancelled',
                                    'border-green-200 bg-green-50':
                                        bet.status === 'completed' &&
                                        bet.winner_id === currentUser.id,
                                    'border-red-200 bg-red-50':
                                        bet.status === 'completed' &&
                                        bet.winner_id !== currentUser.id,
                                }"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <!-- Opponent/Creator Profile -->
                                        <div class="relative">
                                            <div
                                                class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-200"
                                            >
                                                <img
                                                    v-if="
                                                        bet.creator_id ===
                                                        currentUser.id
                                                            ? bet.opponent
                                                                  .profile_image
                                                            : bet.creator
                                                                  .profile_image
                                                    "
                                                    :src="`/storage/profiles/${
                                                        bet.creator_id ===
                                                        currentUser.id
                                                            ? bet.opponent
                                                                  .profile_image
                                                            : bet.creator
                                                                  .profile_image
                                                    }`"
                                                    :alt="
                                                        bet.creator_id ===
                                                        currentUser.id
                                                            ? bet.opponent.name
                                                            : bet.creator.name
                                                    "
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
                                                v-if="
                                                    bet.creator_id ===
                                                    currentUser.id
                                                        ? bet.opponent
                                                              .is_premium
                                                        : bet.creator.is_premium
                                                "
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
                                                    bet.creator_id ===
                                                    currentUser.id
                                                        ? bet.opponent.name
                                                        : bet.creator.name
                                                }}
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                {{
                                                    bet.creator_id ===
                                                    currentUser.id
                                                        ? "You challenged this user"
                                                        : "This user challenged you"
                                                }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Referee:
                                                {{ bet.referee.name }} •
                                                {{ formatDate(bet.created_at) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Bet Result & Actions -->
                                    <div class="flex items-center gap-3">
                                        <div class="text-right mr-4">
                                            <!-- Completed bet - show win/loss -->
                                            <div
                                                v-if="
                                                    bet.status === 'completed'
                                                "
                                            >
                                                <div
                                                    v-if="
                                                        bet.winner_id ===
                                                        currentUser.id
                                                    "
                                                    class="text-lg font-bold text-green-600"
                                                >
                                                    +{{
                                                        formatPoints(
                                                            bet.points_amount
                                                        )
                                                    }}
                                                </div>
                                                <div
                                                    v-else
                                                    class="text-lg font-bold text-red-600"
                                                >
                                                    -{{
                                                        formatPoints(
                                                            bet.points_amount
                                                        )
                                                    }}
                                                </div>
                                                <div
                                                    class="text-sm text-gray-500"
                                                >
                                                    points
                                                </div>
                                            </div>
                                            <!-- Pending/Other statuses - show stake amount -->
                                            <div v-else>
                                                <div
                                                    class="text-lg font-bold text-gray-800"
                                                >
                                                    {{
                                                        formatPoints(
                                                            bet.points_amount
                                                        )
                                                    }}
                                                </div>
                                                <div
                                                    class="text-sm text-gray-500"
                                                >
                                                    points
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <!-- Completed bet status -->
                                            <span
                                                v-if="
                                                    bet.status === 'completed'
                                                "
                                                :class="
                                                    bet.winner_id ===
                                                    currentUser.id
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-red-100 text-red-800'
                                                "
                                                class="px-3 py-1 rounded-full text-sm font-medium"
                                            >
                                                {{
                                                    bet.winner_id ===
                                                    currentUser.id
                                                        ? "Won"
                                                        : "Lost"
                                                }}
                                            </span>
                                            <!-- Other statuses -->
                                            <span
                                                v-else
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800':
                                                        bet.status ===
                                                        'pending',
                                                    'bg-blue-100 text-blue-800':
                                                        bet.status ===
                                                        'accepted',
                                                    'bg-red-100 text-red-800':
                                                        bet.status ===
                                                        'rejected',
                                                    'bg-gray-100 text-gray-800':
                                                        bet.status ===
                                                        'cancelled',
                                                }"
                                                class="px-3 py-1 rounded-full text-sm font-medium"
                                            >
                                                {{ getStatusText(bet.status) }}
                                            </span>
                                        </div>

                                        <div
                                            v-if="
                                                bet.status === 'pending' &&
                                                bet.creator_id ===
                                                    currentUser.id
                                            "
                                        >
                                            <button
                                                @click="cancelBet(bet.id)"
                                                class="text-red-600 hover:text-red-800 transition-colors p-2 hover:bg-red-50 rounded-lg"
                                                :disabled="
                                                    processingBet === bet.id
                                                "
                                                title="Cancel bet"
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

                    <!-- Ongoing Bets Tab -->
                    <div v-else-if="activeTab === 'ongoing'" class="p-6">
                        <div
                            v-if="ongoingBets.length === 0"
                            class="text-center py-12"
                        >
                            <i
                                class="pi pi-clock text-gray-300 text-5xl mb-4"
                            ></i>
                            <h3 class="text-lg font-medium text-gray-600 mb-2">
                                No Ongoing Bets
                            </h3>
                            <p class="text-gray-500">
                                No bets are currently waiting for referee
                                decision.
                            </p>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="bet in ongoingBets"
                                :key="bet.id"
                                class="border border-blue-200 bg-blue-50 rounded-lg p-6 hover:shadow-md transition-shadow"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <!-- Both participants -->
                                        <div class="flex items-center gap-2">
                                            <div class="relative">
                                                <div
                                                    class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-200"
                                                >
                                                    <img
                                                        v-if="
                                                            bet.creator
                                                                .profile_image
                                                        "
                                                        :src="`/storage/profiles/${bet.creator.profile_image}`"
                                                        :alt="bet.creator.name"
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
                                            </div>
                                            <span class="text-sm font-medium">{{
                                                bet.creator.name
                                            }}</span>
                                            <span class="text-gray-400"
                                                >vs</span
                                            >
                                            <span class="text-sm font-medium">{{
                                                bet.opponent.name
                                            }}</span>
                                            <div class="relative">
                                                <div
                                                    class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-200"
                                                >
                                                    <img
                                                        v-if="
                                                            bet.opponent
                                                                .profile_image
                                                        "
                                                        :src="`/storage/profiles/${bet.opponent.profile_image}`"
                                                        :alt="bet.opponent.name"
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
                                            </div>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-600">
                                                Waiting for referee decision
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Referee:
                                                {{ bet.referee.name }} •
                                                Accepted
                                                {{
                                                    formatDate(bet.accepted_at)
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Bet Amount -->
                                    <div class="text-right">
                                        <div
                                            class="text-lg font-bold text-blue-600"
                                        >
                                            {{
                                                formatPoints(bet.points_amount)
                                            }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            points at stake
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Referee Tab -->
                    <div v-else-if="activeTab === 'referee'" class="p-6">
                        <div
                            v-if="refereeBets.length === 0"
                            class="text-center py-12"
                        >
                            <i
                                class="pi pi-balance-scale text-gray-300 text-5xl mb-4"
                            ></i>
                            <h3 class="text-lg font-medium text-gray-600 mb-2">
                                No Referee Requests
                            </h3>
                            <p class="text-gray-500">
                                No bets are waiting for your judgment.
                            </p>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="bet in refereeBets"
                                :key="bet.id"
                                class="border border-purple-200 bg-purple-50 rounded-lg p-6 hover:shadow-md transition-shadow"
                            >
                                <div
                                    class="flex items-center justify-between mb-4"
                                >
                                    <div class="flex items-center gap-4">
                                        <!-- Both participants -->
                                        <div class="flex items-center gap-2">
                                            <div class="relative">
                                                <div
                                                    class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-200"
                                                >
                                                    <img
                                                        v-if="
                                                            bet.creator
                                                                .profile_image
                                                        "
                                                        :src="`/storage/profiles/${bet.creator.profile_image}`"
                                                        :alt="bet.creator.name"
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
                                            </div>
                                            <span class="text-sm font-medium">{{
                                                bet.creator.name
                                            }}</span>
                                            <span class="text-gray-400"
                                                >vs</span
                                            >
                                            <span class="text-sm font-medium">{{
                                                bet.opponent.name
                                            }}</span>
                                            <div class="relative">
                                                <div
                                                    class="w-10 h-10 rounded-full overflow-hidden border-2 border-gray-200"
                                                >
                                                    <img
                                                        v-if="
                                                            bet.opponent
                                                                .profile_image
                                                        "
                                                        :src="`/storage/profiles/${bet.opponent.profile_image}`"
                                                        :alt="bet.opponent.name"
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
                                            </div>
                                        </div>

                                        <div>
                                            <p class="text-sm text-gray-600">
                                                Waiting for your judgment
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Accepted
                                                {{
                                                    formatDate(bet.accepted_at)
                                                }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Bet Amount -->
                                    <div class="text-right">
                                        <div
                                            class="text-lg font-bold text-purple-600"
                                        >
                                            {{
                                                formatPoints(bet.points_amount)
                                            }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            points at stake
                                        </div>
                                    </div>
                                </div>

                                <!-- Declare Winner Buttons -->
                                <div class="flex gap-3 mt-4">
                                    <button
                                        @click="
                                            declareWinner(
                                                bet.id,
                                                bet.creator.id
                                            )
                                        "
                                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm flex-1"
                                        :disabled="processingBet === bet.id"
                                    >
                                        <i class="pi pi-crown mr-2"></i
                                        >{{ bet.creator.name }} Wins
                                    </button>
                                    <button
                                        @click="
                                            declareWinner(
                                                bet.id,
                                                bet.opponent.id
                                            )
                                        "
                                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm flex-1"
                                        :disabled="processingBet === bet.id"
                                    >
                                        <i class="pi pi-crown mr-2"></i
                                        >{{ bet.opponent.name }} Wins
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Bet Dialog -->
        <div
            v-if="showCreateDialog"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            @click.self="showCreateDialog = false"
        >
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-800">
                        Create New Bet
                    </h3>
                    <button
                        @click="closeCreateDialog"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <i class="pi pi-times text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="createBet" class="space-y-6">
                    <!-- Opponent Selection -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Challenge User
                        </label>
                        <div class="relative">
                            <input
                                v-model="opponentSearchQuery"
                                @input="searchOpponents"
                                @focus="showOpponentDropdown = true"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Search opponent..."
                                autocomplete="off"
                            />
                            <i
                                class="pi pi-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                            ></i>

                            <!-- Opponent Dropdown -->
                            <div
                                v-if="
                                    showOpponentDropdown &&
                                    (opponentSearchResults.length > 0 ||
                                        opponentSearchQuery.length > 0)
                                "
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                            >
                                <div
                                    v-if="searchingOpponents"
                                    class="p-3 text-center text-gray-500"
                                >
                                    <i class="pi pi-spin pi-spinner mr-2"></i
                                    >Searching...
                                </div>
                                <div
                                    v-else-if="
                                        opponentSearchResults.length === 0 &&
                                        opponentSearchQuery.length > 0
                                    "
                                    class="p-3 text-center text-gray-500"
                                >
                                    No users found
                                </div>
                                <div
                                    v-for="user in opponentSearchResults"
                                    :key="user.id"
                                    @click="selectOpponent(user)"
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

                        <!-- Selected Opponent Display -->
                        <div
                            v-if="selectedOpponent"
                            class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200"
                        >
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div
                                        class="w-10 h-10 rounded-full overflow-hidden border-2 border-blue-300"
                                    >
                                        <img
                                            v-if="
                                                selectedOpponent.profile_image
                                            "
                                            :src="`/storage/profiles/${selectedOpponent.profile_image}`"
                                            :alt="selectedOpponent.name"
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
                                        v-if="selectedOpponent.is_premium"
                                        class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full border border-white flex items-center justify-center"
                                    >
                                        <i
                                            class="pi pi-star text-white text-xs"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">
                                        {{ selectedOpponent.name }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Level {{ selectedOpponent.level }} •
                                        {{
                                            formatPoints(
                                                selectedOpponent.points
                                            )
                                        }}
                                        points
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="clearSelectedOpponent"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <i class="pi pi-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Referee Selection -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Choose Referee
                        </label>
                        <div class="relative">
                            <input
                                v-model="refereeSearchQuery"
                                @input="searchReferees"
                                @focus="showRefereeDropdown = true"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Search referee..."
                                autocomplete="off"
                            />
                            <i
                                class="pi pi-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                            ></i>

                            <!-- Referee Dropdown -->
                            <div
                                v-if="
                                    showRefereeDropdown &&
                                    (refereeSearchResults.length > 0 ||
                                        refereeSearchQuery.length > 0)
                                "
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                            >
                                <div
                                    v-if="searchingReferees"
                                    class="p-3 text-center text-gray-500"
                                >
                                    <i class="pi pi-spin pi-spinner mr-2"></i
                                    >Searching...
                                </div>
                                <div
                                    v-else-if="
                                        refereeSearchResults.length === 0 &&
                                        refereeSearchQuery.length > 0
                                    "
                                    class="p-3 text-center text-gray-500"
                                >
                                    No users found
                                </div>
                                <div
                                    v-for="user in refereeSearchResults"
                                    :key="user.id"
                                    @click="selectReferee(user)"
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

                        <!-- Selected Referee Display -->
                        <div
                            v-if="selectedReferee"
                            class="mt-3 p-3 bg-green-50 rounded-lg border border-green-200"
                        >
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div
                                        class="w-10 h-10 rounded-full overflow-hidden border-2 border-green-300"
                                    >
                                        <img
                                            v-if="selectedReferee.profile_image"
                                            :src="`/storage/profiles/${selectedReferee.profile_image}`"
                                            :alt="selectedReferee.name"
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
                                        v-if="selectedReferee.is_premium"
                                        class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-400 rounded-full border border-white flex items-center justify-center"
                                    >
                                        <i
                                            class="pi pi-star text-white text-xs"
                                        ></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">
                                        {{ selectedReferee.name }}
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        Level {{ selectedReferee.level }} •
                                        {{
                                            formatPoints(selectedReferee.points)
                                        }}
                                        points
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="clearSelectedReferee"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <i class="pi pi-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Points Amount -->
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                        >
                            Points to Bet
                        </label>
                        <input
                            v-model.number="newBet.points_amount"
                            type="number"
                            min="1"
                            :max="currentUser.points"
                            required
                            placeholder="How many points to bet?"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        />
                        <p class="text-sm text-gray-500 mt-1">
                            You have
                            {{ formatPoints(currentUser.points || 0) }} points
                            available
                        </p>
                    </div>

                    <!-- Error Messages -->
                    <div
                        v-if="betErrors.length > 0"
                        class="bg-red-50 border border-red-200 rounded-lg p-3"
                    >
                        <ul class="text-sm text-red-600 space-y-1">
                            <li v-for="error in betErrors" :key="error">
                                <i class="pi pi-exclamation-triangle mr-1"></i>
                                {{ error }}
                            </li>
                        </ul>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeCreateDialog"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="
                                loading ||
                                !betSummary.isValid ||
                                betErrors.length > 0
                            "
                            class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <i
                                v-if="loading"
                                class="pi pi-spin pi-spinner mr-2"
                            ></i>
                            Create Bet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Layout>
</template>

<script>
export default {
    data() {
        return {
            activeTab: "incoming",
            loading: false,
            showCreateDialog: false,
            newBet: {
                points_amount: "",
            },
            myBets: [],
            incomingBets: [],
            refereeBets: [],
            ongoingBets: [],
            currentUser: {
                id: null,
                points: 0,
            },
            processingBet: null,

            // Opponent search
            opponentSearchQuery: "",
            opponentSearchResults: [],
            selectedOpponent: null,
            showOpponentDropdown: false,
            searchingOpponents: false,
            opponentSearchTimeout: null,

            // Referee search
            refereeSearchQuery: "",
            refereeSearchResults: [],
            selectedReferee: null,
            showRefereeDropdown: false,
            searchingReferees: false,
            refereeSearchTimeout: null,
        };
    },
    computed: {
        betSummary() {
            return {
                isValid:
                    this.selectedOpponent &&
                    this.selectedReferee &&
                    this.newBet.points_amount > 0,
                hasOpponent: !!this.selectedOpponent,
                hasReferee: !!this.selectedReferee,
                hasPoints: this.newBet.points_amount > 0,
            };
        },

        betErrors() {
            const errors = [];

            if (!this.selectedOpponent) {
                errors.push("Please select an opponent");
            }

            if (!this.selectedReferee) {
                errors.push("Please select a referee");
            }

            if (
                this.selectedOpponent &&
                this.selectedReferee &&
                this.selectedOpponent.id === this.selectedReferee.id
            ) {
                errors.push("Opponent and referee must be different users");
            }

            if (!this.newBet.points_amount || this.newBet.points_amount <= 0) {
                errors.push("Please enter a valid points amount");
            }

            if (this.newBet.points_amount > (this.currentUser.points || 0)) {
                errors.push("You don't have enough points");
            }

            if (
                this.selectedOpponent &&
                this.newBet.points_amount > this.selectedOpponent.points
            ) {
                errors.push("Opponent doesn't have enough points");
            }

            return errors;
        },
    },
    methods: {
        // Search methods for opponents
        searchOpponents() {
            clearTimeout(this.opponentSearchTimeout);

            if (this.opponentSearchQuery.length < 2) {
                this.opponentSearchResults = [];
                return;
            }

            this.opponentSearchTimeout = setTimeout(async () => {
                try {
                    this.searchingOpponents = true;
                    const response = await axios.get(
                        `/api/users/search?q=${encodeURIComponent(
                            this.opponentSearchQuery
                        )}`
                    );

                    if (response.data.success) {
                        this.opponentSearchResults = response.data.users.filter(
                            (user) => user.id !== this.currentUser.id
                        );
                    }
                } catch (error) {
                    console.error("Error searching opponents:", error);
                    this.opponentSearchResults = [];
                } finally {
                    this.searchingOpponents = false;
                }
            }, 300);
        },

        selectOpponent(user) {
            this.selectedOpponent = user;
            this.opponentSearchQuery = user.name;
            this.showOpponentDropdown = false;
            this.opponentSearchResults = [];
        },

        clearSelectedOpponent() {
            this.selectedOpponent = null;
            this.opponentSearchQuery = "";
            this.opponentSearchResults = [];
        },

        // Search methods for referees
        searchReferees() {
            clearTimeout(this.refereeSearchTimeout);

            if (this.refereeSearchQuery.length < 2) {
                this.refereeSearchResults = [];
                return;
            }

            this.refereeSearchTimeout = setTimeout(async () => {
                try {
                    this.searchingReferees = true;
                    const response = await axios.get(
                        `/api/users/search?q=${encodeURIComponent(
                            this.refereeSearchQuery
                        )}`
                    );

                    if (response.data.success) {
                        this.refereeSearchResults = response.data.users.filter(
                            (user) =>
                                user.id !== this.currentUser.id &&
                                (!this.selectedOpponent ||
                                    user.id !== this.selectedOpponent.id)
                        );
                    }
                } catch (error) {
                    console.error("Error searching referees:", error);
                    this.refereeSearchResults = [];
                } finally {
                    this.searchingReferees = false;
                }
            }, 300);
        },

        selectReferee(user) {
            this.selectedReferee = user;
            this.refereeSearchQuery = user.name;
            this.showRefereeDropdown = false;
            this.refereeSearchResults = [];
        },

        clearSelectedReferee() {
            this.selectedReferee = null;
            this.refereeSearchQuery = "";
            this.refereeSearchResults = [];
        },

        async fetchBets() {
            try {
                this.loading = true;
                const response = await axios.get("/api/bets");
                if (response.data.success) {
                    this.myBets = response.data.my_bets || [];
                    this.incomingBets = response.data.incoming_bets || [];
                    this.refereeBets = response.data.referee_bets || [];
                    this.ongoingBets = response.data.ongoing_bets || [];
                }
            } catch (error) {
                console.error("Error fetching bets:", error);
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to load bets",
                });
            } finally {
                this.loading = false;
            }
        },

        async createBet() {
            if (this.betErrors.length > 0) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: this.betErrors[0],
                });
                return;
            }

            try {
                this.loading = true;
                const response = await axios.post("/api/bets", {
                    opponent_id: this.selectedOpponent.id,
                    referee_id: this.selectedReferee.id,
                    points_amount: this.newBet.points_amount,
                });

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Bet created successfully",
                    });

                    this.closeCreateDialog();
                    await this.fetchBets();

                    // Update current user points
                    const userResponse = await axios.get("/api/user");
                    if (userResponse.data.success) {
                        this.currentUser = userResponse.data.user;
                    }
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message || "Failed to create bet",
                });
            } finally {
                this.loading = false;
            }
        },

        async acceptBet(betId) {
            try {
                this.processingBet = betId;
                const response = await axios.post(`/api/bets/${betId}/accept`);
                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Bet accepted",
                    });
                    await this.fetchBets();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail:
                        error.response?.data?.message || "Failed to accept bet",
                });
            } finally {
                this.processingBet = null;
            }
        },

        async rejectBet(betId) {
            try {
                this.processingBet = betId;
                const response = await axios.post(`/api/bets/${betId}/reject`);
                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Bet rejected",
                    });
                    await this.fetchBets();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to reject bet",
                });
            } finally {
                this.processingBet = null;
            }
        },

        async cancelBet(betId) {
            try {
                this.processingBet = betId;
                const response = await axios.post(`/api/bets/${betId}/cancel`);
                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Bet cancelled",
                    });
                    await this.fetchBets();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to cancel bet",
                });
            } finally {
                this.processingBet = null;
            }
        },

        async declareWinner(betId, winnerId) {
            try {
                this.processingBet = betId;
                const response = await axios.post(
                    `/api/bets/${betId}/declare-winner`,
                    {
                        winner_id: winnerId,
                    }
                );

                if (response.data.success) {
                    this.$toast?.add({
                        severity: "success",
                        summary: "Success",
                        detail: "Winner declared successfully",
                    });
                    await this.fetchBets();
                }
            } catch (error) {
                this.$toast?.add({
                    severity: "error",
                    summary: "Error",
                    detail: "Failed to declare winner",
                });
            } finally {
                this.processingBet = null;
            }
        },

        closeCreateDialog() {
            this.showCreateDialog = false;
            this.clearSelectedOpponent();
            this.clearSelectedReferee();
            this.newBet = {
                points_amount: "",
            };
        },

        getStatusText(status) {
            const texts = {
                pending: "Pending",
                accepted: "Accepted",
                rejected: "Rejected",
                completed: "Completed",
                cancelled: "Cancelled",
            };
            return texts[status] || status;
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

    async mounted() {
        // Get current user info
        try {
            const userResponse = await axios.get("/api/user");
            if (userResponse.data.success) {
                this.currentUser = userResponse.data.user;
            }
        } catch (error) {
            console.error("Error fetching current user:", error);
        }

        await this.fetchBets();

        // Close dropdowns when clicking outside
        document.addEventListener("click", (e) => {
            if (!this.$el?.contains(e.target)) {
                this.showOpponentDropdown = false;
                this.showRefereeDropdown = false;
            }
        });
    },
};
</script>
