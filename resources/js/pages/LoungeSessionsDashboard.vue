<template>
    <Layout>
        <div>
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        Lounge Sessions
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ activeSessions.length }} active session(s)
                    </p>
                </div>
                <button
                    @click="openCheckIn()"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                >
                    <i class="pi pi-plus"></i> Check In
                </button>
            </div>

            <!-- Loading / empty -->
            <div v-if="loadingSessions" class="text-center py-12 text-gray-400">
                <i class="pi pi-spin pi-spinner text-2xl"></i>
            </div>

            <div
                v-else-if="activeSessions.length === 0"
                class="text-center py-12 text-gray-400"
            >
                <i class="pi pi-home text-4xl mb-3"></i>
                <p class="text-sm">
                    No active sessions. Check someone in to get started.
                </p>
            </div>

            <!-- Cards: solo sessions + group cards -->
            <div
                v-else
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8"
            >
                <!-- ── Solo session card ── -->
                <div
                    v-for="session in soloSessions"
                    :key="session.id"
                    class="bg-white rounded-xl shadow-sm p-5"
                    :class="
                        isCountdownNegative(session)
                            ? 'border-2 border-red-300'
                            : 'border border-gray-100'
                    "
                >
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="font-semibold text-gray-800">
                                {{ session.customer_name }}
                            </p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span
                                    v-if="session.is_free"
                                    class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-medium"
                                >
                                    Level {{ session.user_level }} — Free
                                </span>
                                <span
                                    v-else-if="
                                        session.customer_type === 'walk_in'
                                    "
                                    class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"
                                >
                                    Walk-in
                                </span>
                                <span
                                    v-else
                                    class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full"
                                >
                                    Level {{ session.user_level }}
                                </span>
                            </div>
                        </div>
                        <i class="pi pi-user text-gray-300 text-2xl"></i>
                    </div>

                    <div
                        class="bg-gray-50 rounded-lg px-4 py-3 mb-3 text-center"
                    >
                        <p
                            class="text-2xl font-mono font-bold"
                            :class="
                                isCountdownNegative(session)
                                    ? 'text-red-600'
                                    : 'text-indigo-600'
                            "
                        >
                            {{ getElapsed(session) }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Checked in at
                            {{ formatTime(session.checked_in_at) }}
                        </p>
                    </div>

                    <p
                        v-if="session.billing_mode === 'consumable'"
                        class="text-xs mb-3 text-center"
                        :class="
                            isCountdownNegative(session)
                                ? 'text-red-500 font-medium'
                                : 'text-gray-400'
                        "
                    >
                        ⏳ Consumable time
                        {{
                            isCountdownNegative(session) ? "— over balance" : ""
                        }}
                    </p>

                    <p
                        v-else-if="!session.is_free"
                        class="text-xs text-gray-400 mb-3 text-center"
                    >
                        ⏱ 10-min grace period per hour applies
                    </p>

                    <div class="flex gap-2">
                        <button
                            @click="startGroupFrom(session)"
                            class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium py-2 rounded-lg text-sm transition-colors"
                        >
                            + Add to group
                        </button>
                        <button
                            @click="openCheckout(session)"
                            class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 font-medium py-2 rounded-lg text-sm transition-colors"
                        >
                            Check Out
                        </button>
                    </div>
                </div>

                <!-- ── Group card ── -->
                <div
                    v-for="(group, idx) in groupedSessions"
                    :key="group.group_id"
                    class="bg-white rounded-xl shadow-sm border border-indigo-100 p-5 md:col-span-2 lg:col-span-1"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-semibold"
                            >
                                Group {{ groupLabel(idx) }}
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ group.members.length }} people
                            </span>
                        </div>
                        <i class="pi pi-users text-indigo-300 text-2xl"></i>
                    </div>

                    <!-- Member rows with individual timers -->
                    <div class="space-y-2 mb-3">
                        <div
                            v-for="m in group.members"
                            :key="m.id"
                            class="flex items-center justify-between rounded-lg px-3 py-2"
                            :class="
                                isCountdownNegative(m)
                                    ? 'bg-red-50 border border-red-200'
                                    : 'bg-gray-50'
                            "
                        >
                            <div>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ m.customer_name }}
                                </p>
                                <span
                                    v-if="m.is_free"
                                    class="text-xs text-yellow-600 font-medium"
                                    >Lv{{ m.user_level }} — Free</span
                                >
                                <span
                                    v-else-if="m.customer_type === 'walk_in'"
                                    class="text-xs text-gray-400"
                                    >Walk-in</span
                                >
                                <span v-else class="text-xs text-blue-600"
                                    >Lv{{ m.user_level }}</span
                                >
                            </div>
                            <div class="flex items-center gap-2">
                                <p
                                    class="font-mono font-bold text-sm"
                                    :class="
                                        isCountdownNegative(m)
                                            ? 'text-red-600'
                                            : 'text-indigo-600'
                                    "
                                >
                                    {{ getElapsed(m) }}
                                </p>
                                <button
                                    @click="openCheckout(m, true)"
                                    title="Check out just this person"
                                    class="text-red-400 hover:text-red-600 text-xs px-2 py-1 rounded hover:bg-red-50 transition-colors"
                                >
                                    Out
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button
                            @click="addToExistingGroup(group.group_id)"
                            class="flex-1 bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium py-2 rounded-lg text-sm transition-colors"
                        >
                            + Add person
                        </button>
                        <button
                            @click="openGroupCheckout(group)"
                            class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 font-medium py-2 rounded-lg text-sm transition-colors"
                        >
                            Check Out All
                        </button>
                    </div>
                </div>
            </div>

            <!-- Session History -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-700">Session History</h3>
                    <input
                        v-model="historyDate"
                        type="date"
                        class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        @change="fetchHistory"
                    />
                </div>

                <div
                    v-if="loadingHistory"
                    class="text-center py-6 text-gray-400"
                >
                    <i class="pi pi-spin pi-spinner"></i>
                </div>

                <div
                    v-else-if="history.length === 0"
                    class="text-center py-6 text-gray-400 text-sm"
                >
                    No completed sessions for this date.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-gray-500 border-b border-gray-100"
                            >
                                <th class="pb-2 font-medium">Customer</th>
                                <th class="pb-2 font-medium">Type</th>
                                <th class="pb-2 font-medium">Check In</th>
                                <th class="pb-2 font-medium">Check Out</th>
                                <th class="pb-2 font-medium">Duration</th>
                                <th class="pb-2 font-medium">Bill</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="s in history"
                                :key="s.id"
                                class="border-b border-gray-50 hover:bg-gray-50"
                            >
                                <td class="py-2 font-medium text-gray-800">
                                    {{ s.customer_name }}
                                </td>
                                <td class="py-2">
                                    <span
                                        v-if="s.is_free"
                                        class="text-yellow-600 font-medium"
                                        >Free</span
                                    >
                                    <span
                                        v-else-if="
                                            s.customer_type === 'walk_in'
                                        "
                                        class="text-gray-500"
                                        >Walk-in</span
                                    >
                                    <span v-else class="text-blue-600"
                                        >Lv{{ s.user_level }}</span
                                    >
                                </td>
                                <td class="py-2 text-gray-500">
                                    {{ formatTime(s.checked_in_at) }}
                                </td>
                                <td class="py-2 text-gray-500">
                                    {{ formatTime(s.checked_out_at) }}
                                </td>
                                <td class="py-2 text-gray-500">
                                    {{ getDuration(s) }}
                                </td>
                                <td
                                    class="py-2 font-semibold"
                                    :class="
                                        s.is_free
                                            ? 'text-yellow-600'
                                            : 'text-gray-800'
                                    "
                                >
                                    {{
                                        s.is_free ? "Free" : "₱" + s.total_bill
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Check In Modal ── -->
            <div
                v-if="showCheckIn"
                class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6"
                >
                    <h3 class="text-lg font-bold text-gray-800 mb-1">
                        Check In Customer
                    </h3>
                    <p
                        v-if="checkInForm.group_id && !groupMode"
                        class="text-xs text-indigo-500 mb-4"
                    >
                        Adding to a group
                    </p>
                    <p v-else class="text-sm text-gray-400 mb-4">
                        Check in one person or a whole group.
                    </p>

                    <!-- Single vs Group toggle (hidden when joining an existing group) -->
                    <div
                        v-if="!checkInForm.group_id || groupMode"
                        class="grid grid-cols-2 gap-2 mb-4 bg-gray-100 p-1 rounded-lg"
                    >
                        <button
                            @click="setGroupMode(false)"
                            :class="
                                !groupMode
                                    ? 'bg-white shadow-sm text-indigo-600'
                                    : 'text-gray-500'
                            "
                            class="py-1.5 rounded-md text-sm font-medium transition-all"
                        >
                            Single
                        </button>
                        <button
                            @click="setGroupMode(true)"
                            :class="
                                groupMode
                                    ? 'bg-white shadow-sm text-indigo-600'
                                    : 'text-gray-500'
                            "
                            class="py-1.5 rounded-md text-sm font-medium transition-all"
                        >
                            Group
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Customer Type -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Customer Type</label
                            >
                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    @click="
                                        checkInForm.customer_type = 'walk_in';
                                        checkInForm.user_id = null;
                                        checkInForm.customer_name = '';
                                        selectedUser = null;
                                    "
                                    :class="
                                        checkInForm.customer_type === 'walk_in'
                                            ? 'border-indigo-400 bg-indigo-50 ring-2 ring-indigo-300'
                                            : 'border-gray-200'
                                    "
                                    class="cursor-pointer border rounded-lg p-3 text-center transition-all"
                                >
                                    <p class="text-sm font-medium">Walk-in</p>
                                    <p class="text-xs text-gray-400">
                                        Not a user
                                    </p>
                                </div>
                                <div
                                    @click="
                                        checkInForm.customer_type = 'member'
                                    "
                                    :class="
                                        checkInForm.customer_type === 'member'
                                            ? 'border-indigo-400 bg-indigo-50 ring-2 ring-indigo-300'
                                            : 'border-gray-200'
                                    "
                                    class="cursor-pointer border rounded-lg p-3 text-center transition-all"
                                >
                                    <p class="text-sm font-medium">Member</p>
                                    <p class="text-xs text-gray-400">
                                        Existing user
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Walk-in name -->
                        <div v-if="checkInForm.customer_type === 'walk_in'">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Full Name</label
                            >
                            <input
                                v-model="checkInForm.customer_name"
                                type="text"
                                placeholder="e.g. John Simon Santiago"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            />
                        </div>

                        <!-- Member search -->
                        <div v-if="checkInForm.customer_type === 'member'">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Search User</label
                            >
                            <input
                                v-model="userSearch"
                                type="text"
                                placeholder="Type a name..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                @input="searchUsers"
                            />
                            <div
                                v-if="userResults.length > 0"
                                class="border border-gray-200 rounded-lg mt-1 max-h-40 overflow-y-auto shadow-sm"
                            >
                                <div
                                    v-for="u in userResults"
                                    :key="u.id"
                                    @click="selectUser(u)"
                                    class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm flex items-center justify-between"
                                >
                                    <span>{{ u.name }}</span>
                                    <span class="text-xs text-gray-400"
                                        >Level {{ u.level }}</span
                                    >
                                </div>
                            </div>
                            <div
                                v-if="selectedUser"
                                class="mt-2 bg-indigo-50 border border-indigo-200 rounded-lg px-3 py-2 text-sm flex justify-between items-center"
                            >
                                <span class="font-medium text-indigo-800">{{
                                    selectedUser.name
                                }}</span>
                                <span class="text-xs text-indigo-500">
                                    Level {{ selectedUser.level }}
                                    {{
                                        selectedUser.level >= 2
                                            ? "— Free Access"
                                            : ""
                                    }}
                                </span>
                            </div>

                            <!-- Billing mode toggle (Level 1 members only) -->
                            <div
                                v-if="selectedUser && selectedUser.level === 1"
                                class="mt-3"
                            >
                                <label
                                    class="block text-xs font-medium text-gray-500 mb-1"
                                    >Billing</label
                                >
                                <div
                                    class="grid grid-cols-2 gap-2 bg-gray-100 p-1 rounded-lg"
                                >
                                    <button
                                        type="button"
                                        @click="
                                            checkInForm.billing_mode = 'hourly'
                                        "
                                        :class="
                                            checkInForm.billing_mode ===
                                            'hourly'
                                                ? 'bg-white shadow-sm text-indigo-600'
                                                : 'text-gray-500'
                                        "
                                        class="py-1.5 rounded-md text-sm font-medium transition-all"
                                    >
                                        Pay by Hour
                                    </button>
                                    <button
                                        type="button"
                                        @click="
                                            checkInForm.billing_mode =
                                                'consumable'
                                        "
                                        :class="
                                            checkInForm.billing_mode ===
                                            'consumable'
                                                ? 'bg-white shadow-sm text-indigo-600'
                                                : 'text-gray-500'
                                        "
                                        class="py-1.5 rounded-md text-sm font-medium transition-all"
                                    >
                                        Use Consumable Time
                                    </button>
                                </div>
                                <p
                                    class="text-xs mt-1"
                                    :class="
                                        (selectedUser.consumable_minutes ??
                                            0) <= 0
                                            ? 'text-red-500'
                                            : 'text-gray-400'
                                    "
                                >
                                    Balance:
                                    {{
                                        formatMinutesBalance(
                                            selectedUser.consumable_minutes,
                                        )
                                    }}
                                    <span
                                        v-if="
                                            (selectedUser.consumable_minutes ??
                                                0) <= 0
                                        "
                                        >— no time to use, buy time first</span
                                    >
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending group queue -->
                    <div v-if="pendingGroup.length > 0" class="mt-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">
                            In this group ({{ pendingGroup.length }}):
                        </p>
                        <div class="space-y-1">
                            <div
                                v-for="(p, i) in pendingGroup"
                                :key="i"
                                class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-1.5 text-sm"
                            >
                                <div class="flex items-center gap-2">
                                    <span>{{ p.customer_name }}</span>
                                    <span
                                        v-if="p.customer_type === 'walk_in'"
                                        class="text-xs bg-gray-200 text-gray-600 px-1.5 py-0.5 rounded-full"
                                    >
                                        Walk-in
                                    </span>
                                    <span
                                        v-else-if="
                                            p.billing_mode === 'consumable'
                                        "
                                        class="text-xs bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded-full"
                                    >
                                        Member · Consumable
                                    </span>
                                    <span
                                        v-else
                                        class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full"
                                    >
                                        Member · Hourly
                                    </span>
                                </div>
                                <button
                                    @click="pendingGroup.splice(i, 1)"
                                    class="text-gray-300 hover:text-red-400"
                                >
                                    <i class="pi pi-times text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="checkInError" class="text-red-500 text-sm mt-3">
                        {{ checkInError }}
                    </div>

                    <!-- Add another person (group mode only) -->
                    <button
                        v-if="groupMode || checkInForm.group_id"
                        @click="addPersonToQueue"
                        class="w-full mt-4 border border-dashed border-indigo-300 text-indigo-600 py-2 rounded-lg text-sm hover:bg-indigo-50 transition-colors"
                    >
                        + Add this person to group
                    </button>

                    <div class="flex gap-3 mt-4">
                        <button
                            @click="
                                showCheckIn = false;
                                resetCheckIn();
                            "
                            class="flex-1 border border-gray-200 text-gray-600 py-2 rounded-lg text-sm hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitCheckIn"
                            :disabled="checkingIn"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50"
                        >
                            {{
                                checkingIn
                                    ? "Checking in..."
                                    : checkInButtonLabel
                            }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Check Out Modal ── -->
            <div
                v-if="showCheckOut"
                class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6"
                >
                    <h3 class="text-lg font-bold text-gray-800 mb-1">
                        Check Out
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        {{ checkoutTitle }}
                    </p>

                    <div v-if="checkoutResult">
                        <!-- Per-person line items -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4 space-y-2">
                            <div
                                v-for="line in checkoutResult.line_items"
                                :key="line.id"
                                class="flex justify-between items-start text-sm"
                            >
                                <div>
                                    <p class="font-medium text-gray-700">
                                        {{ line.customer_name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ line.duration }}
                                        <span v-if="line.breakdown"
                                            >· {{ line.breakdown }}</span
                                        >
                                    </p>
                                </div>
                                <span
                                    class="font-semibold"
                                    :class="
                                        line.is_free
                                            ? 'text-yellow-600'
                                            : 'text-gray-800'
                                    "
                                >
                                    {{
                                        line.is_free ? "Free" : "₱" + line.bill
                                    }}
                                </span>
                            </div>

                            <div
                                class="border-t border-gray-200 mt-2 pt-3 flex justify-between items-center"
                            >
                                <span class="font-semibold text-gray-700"
                                    >Total</span
                                >
                                <span class="text-xl font-bold text-indigo-600"
                                    >₱{{ displayTotal }}</span
                                >
                            </div>
                        </div>

                        <!-- Override toggle (paid sessions only) -->
                        <div
                            v-if="checkoutResult.computed_total > 0"
                            class="mb-4"
                        >
                            <label
                                class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    v-model="overrideOn"
                                    class="rounded text-indigo-600 focus:ring-indigo-300"
                                />
                                Override total (waive minutes / discount)
                            </label>
                            <div
                                v-if="overrideOn"
                                class="mt-2 flex items-center gap-2"
                            >
                                <span class="text-gray-500">₱</span>
                                <input
                                    v-model.number="overrideTotal"
                                    type="number"
                                    min="0"
                                    :placeholder="checkoutResult.computed_total"
                                    class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                />
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-6 text-gray-400">
                        <i class="pi pi-spin pi-spinner text-xl"></i>
                    </div>

                    <button
                        @click="confirmCheckout"
                        :disabled="confirmingCheckout"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition-colors mt-2 disabled:opacity-50"
                    >
                        {{
                            confirmingCheckout
                                ? "Closing..."
                                : "Confirm & Close"
                        }}
                    </button>
                    <button
                        @click="
                            showCheckOut = false;
                            checkoutResult = null;
                        "
                        class="w-full mt-2 text-sm text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>

<script>
export default {
    name: "LoungeSessionsDashboard",
    data() {
        return {
            activeSessions: [],
            history: [],
            loadingSessions: true,
            loadingHistory: false,
            historyDate: new Date().toISOString().split("T")[0],
            tickInterval: null,
            tick: 0,

            // Check in
            showCheckIn: false,
            checkingIn: false,
            checkInError: null,
            checkInForm: {
                customer_name: "",
                customer_type: "walk_in",
                user_id: null,
                group_id: null,
                billing_mode: "hourly",
            },
            pendingGroup: [], // queued people to check in together
            groupMode: false, // true when building a group
            userSearch: "",
            userResults: [],
            selectedUser: null,
            searchTimeout: null,

            // Check out
            showCheckOut: false,
            checkoutSession: null, // for solo
            checkoutGroup: null, // for group
            checkoutSolo: false, // force-checkout one group member
            checkoutResult: null,
            confirmingCheckout: false,
            overrideOn: false,
            overrideTotal: null,
        };
    },

    computed: {
        // Sessions with no group_id
        soloSessions() {
            return this.activeSessions.filter((s) => !s.group_id);
        },
        // Grouped by group_id, ordered by when the group first formed (stable labels)
        groupedSessions() {
            const groups = {};
            this.activeSessions
                .filter((s) => s.group_id)
                .forEach((s) => {
                    if (!groups[s.group_id]) {
                        groups[s.group_id] = {
                            group_id: s.group_id,
                            members: [],
                            firstCheckIn: s.checked_in_at,
                        };
                    }
                    groups[s.group_id].members.push(s);
                    // Track the earliest check-in as the group's "birth time"
                    if (s.checked_in_at < groups[s.group_id].firstCheckIn) {
                        groups[s.group_id].firstCheckIn = s.checked_in_at;
                    }
                });
            // Sort by birth time so A is always the oldest group
            return Object.values(groups).sort(
                (a, b) => new Date(a.firstCheckIn) - new Date(b.firstCheckIn),
            );
        },
        checkInButtonLabel() {
            // Count queued people, plus the form only if a name is filled
            const formHasPerson = this.checkInForm.customer_name ? 1 : 0;
            const total = this.pendingGroup.length + formHasPerson;
            if (total <= 1) return "Check In";
            return `Check In ${total} people`;
        },
        checkoutTitle() {
            if (this.checkoutGroup) {
                return `${this.checkoutGroup.members.length} people`;
            }
            return this.checkoutSession?.customer_name || "";
        },
        displayTotal() {
            if (this.overrideOn && this.overrideTotal !== null) {
                return this.overrideTotal;
            }
            return this.checkoutResult?.computed_total ?? 0;
        },
    },

    async mounted() {
        await this.fetchSessions();
        await this.fetchHistory();
        this.tickInterval = setInterval(() => {
            this.tick++;
        }, 1000);
    },

    beforeUnmount() {
        clearInterval(this.tickInterval);
    },

    methods: {
        token() {
            return localStorage.getItem("auth-token");
        },
        headers() {
            return { Authorization: `Bearer ${this.token()}` };
        },

        // Generates a unique "table number"
        newGroupId() {
            return (
                "grp_" +
                Date.now() +
                "_" +
                Math.random().toString(36).slice(2, 7)
            );
        },

        groupLabel(idx) {
            return String.fromCharCode(65 + idx); // 0 -> A, 1 -> B...
        },

        async fetchSessions() {
            this.loadingSessions = true;
            try {
                const res = await axios.get("/api/lounge/active-sessions", {
                    headers: this.headers(),
                });
                if (res.data.success) this.activeSessions = res.data.sessions;
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingSessions = false;
            }
        },

        async fetchHistory() {
            this.loadingHistory = true;
            try {
                const res = await axios.get("/api/lounge/session-history", {
                    headers: this.headers(),
                    params: { date: this.historyDate },
                });
                if (res.data.success) this.history = res.data.sessions;
            } catch (e) {
                console.error(e);
            } finally {
                this.loadingHistory = false;
            }
        },

        getElapsed(session) {
            void this.tick;
            const elapsedSeconds = Math.floor(
                (Date.now() - new Date(session.checked_in_at).getTime()) / 1000,
            );

            if (session.billing_mode === "consumable") {
                const balanceSeconds =
                    (session.user?.consumable_minutes ?? 0) * 60;
                const remaining = balanceSeconds - elapsedSeconds;
                return this.formatSeconds(remaining, true);
            }

            return this.formatSeconds(elapsedSeconds, false);
        },

        formatSeconds(totalSeconds, showSign) {
            const sign = totalSeconds < 0 ? "-" : "";
            const abs = Math.abs(totalSeconds);
            const h = Math.floor(abs / 3600)
                .toString()
                .padStart(2, "0");
            const m = Math.floor((abs % 3600) / 60)
                .toString()
                .padStart(2, "0");
            const s = (abs % 60).toString().padStart(2, "0");
            return `${showSign ? sign : ""}${h}:${m}:${s}`;
        },

        isCountdownNegative(session) {
            if (session.billing_mode !== "consumable") return false;
            const elapsedSeconds = Math.floor(
                (Date.now() - new Date(session.checked_in_at).getTime()) / 1000,
            );
            const balanceSeconds = (session.user?.consumable_minutes ?? 0) * 60;
            return balanceSeconds - elapsedSeconds < 0;
        },

        formatTime(dt) {
            if (!dt) return "—";
            return new Date(dt).toLocaleTimeString("en-PH", {
                hour: "2-digit",
                minute: "2-digit",
            });
        },

        formatMinutesBalance(mins) {
            mins = mins ?? 0;
            const sign = mins < 0 ? "-" : "";
            const abs = Math.abs(mins);
            const h = Math.floor(abs / 60);
            const m = abs % 60;
            return `${sign}${h}h ${m}m`;
        },

        getDuration(session) {
            if (!session.checked_out_at) return "—";
            const mins = Math.floor(
                (new Date(session.checked_out_at) -
                    new Date(session.checked_in_at)) /
                    60000,
            );
            const h = Math.floor(mins / 60);
            const m = mins % 60;
            return h > 0 ? `${h}h ${m}m` : `${m}m`;
        },

        searchUsers() {
            clearTimeout(this.searchTimeout);
            if (this.userSearch.length < 2) {
                this.userResults = [];
                return;
            }
            this.searchTimeout = setTimeout(async () => {
                try {
                    const res = await axios.get("/api/users/search", {
                        headers: this.headers(),
                        params: { q: this.userSearch },
                    });
                    this.userResults = res.data.users || [];
                } catch (e) {
                    this.userResults = [];
                }
            }, 300);
        },

        selectUser(u) {
            this.selectedUser = u;
            this.checkInForm.user_id = u.id;
            this.checkInForm.customer_name = u.name;
            this.userResults = [];
            this.userSearch = u.name;

            // Default Level 1 members to consumable time if they have a balance
            if (u.level === 1 && (u.consumable_minutes ?? 0) > 0) {
                this.checkInForm.billing_mode = "consumable";
            } else {
                this.checkInForm.billing_mode = "hourly";
            }
        },

        // ── Check-in flows ──
        openCheckIn(groupId = null) {
            this.resetCheckIn();
            this.checkInForm.group_id = groupId;
            this.groupMode = !!groupId; // adding to existing group starts in group mode
            this.showCheckIn = true;
        },

        setGroupMode(on) {
            this.groupMode = on;
            this.checkInError = null;
            if (!on) {
                // Switching back to single clears any queued people
                this.pendingGroup = [];
                this.checkInForm.group_id = null;
            }
        },

        // From a solo card: convert it into a group by starting a fresh group_id,
        // then open check-in pre-stamped with that group so the next person joins it.
        async startGroupFrom(session) {
            const gid = this.newGroupId();
            try {
                await axios.post(
                    `/api/lounge/assign-group/${session.id}`,
                    { group_id: gid },
                    { headers: this.headers() },
                );
                await this.fetchSessions();
                this.openCheckIn(gid);
            } catch (e) {
                console.error(e);
            }
        },

        // From an existing group card
        addToExistingGroup(groupId) {
            this.openCheckIn(groupId);
        },

        // Clears the single-person form fields (keeps group_id + queue)
        clearPersonFields() {
            this.checkInForm.customer_name = "";
            this.checkInForm.customer_type = "walk_in";
            this.checkInForm.user_id = null;
            this.checkInForm.billing_mode = "hourly";
            this.userSearch = "";
            this.userResults = [];
            this.selectedUser = null;
        },

        // Push current form person into the queue, then clear fields for the next
        addPersonToQueue() {
            this.checkInError = null;
            if (!this.checkInForm.customer_name) {
                this.checkInError = "Enter a name before adding another.";
                return;
            }
            if (
                this.checkInForm.billing_mode === "consumable" &&
                (this.selectedUser?.consumable_minutes ?? 0) <= 0
            ) {
                this.checkInError =
                    "This member has no consumable time balance.";
                return;
            }
            // First time queuing turns this into a group
            if (!this.checkInForm.group_id) {
                this.checkInForm.group_id = this.newGroupId();
            }
            this.pendingGroup.push({
                customer_name: this.checkInForm.customer_name,
                customer_type: this.checkInForm.customer_type,
                user_id: this.checkInForm.user_id,
                billing_mode: this.checkInForm.billing_mode,
            });
            this.clearPersonFields();
        },

        resetCheckIn() {
            this.checkInForm = {
                customer_name: "",
                customer_type: "walk_in",
                user_id: null,
                group_id: null,
                billing_mode: "hourly",
            };
            this.pendingGroup = [];
            this.groupMode = false;
            this.userSearch = "";
            this.userResults = [];
            this.selectedUser = null;
            this.checkInError = null;
        },

        async submitCheckIn() {
            this.checkInError = null;

            if (
                this.checkInForm.customer_name &&
                this.checkInForm.billing_mode === "consumable" &&
                (this.selectedUser?.consumable_minutes ?? 0) <= 0
            ) {
                this.checkInError =
                    "This member has no consumable time balance.";
                return;
            }

            // Build the full list: queued people + the one currently in the form (if filled)
            const people = [...this.pendingGroup];
            if (this.checkInForm.customer_name) {
                people.push({
                    customer_name: this.checkInForm.customer_name,
                    customer_type: this.checkInForm.customer_type,
                    user_id: this.checkInForm.user_id,
                    billing_mode: this.checkInForm.billing_mode,
                });
            }

            if (people.length === 0) {
                this.checkInError = "Please enter at least one name.";
                return;
            }

            this.checkingIn = true;
            try {
                // Check each person in, all sharing the same group_id (may be null for solo)
                for (const person of people) {
                    await axios.post(
                        "/api/lounge/check-in",
                        { ...person, group_id: this.checkInForm.group_id },
                        { headers: this.headers() },
                    );
                }
                this.showCheckIn = false;
                this.resetCheckIn();
                await this.fetchSessions();
            } catch (e) {
                this.checkInError =
                    e.response?.data?.message || "Check-in failed.";
            } finally {
                this.checkingIn = false;
            }
        },

        // ── Checkout flows ──
        // soloOut = true forces just this person even if they're in a group
        async openCheckout(session, soloOut = false) {
            this.checkoutSession = session;
            this.checkoutGroup = null;
            this.checkoutResult = null;
            this.checkoutSolo = soloOut;
            this.overrideOn = false;
            this.overrideTotal = null;
            this.showCheckOut = true;
            await this.loadCheckoutPreview(session.id, soloOut);
        },

        async openGroupCheckout(group) {
            this.checkoutGroup = group;
            this.checkoutSession = null;
            this.checkoutResult = null;
            this.checkoutSolo = false;
            this.overrideOn = false;
            this.overrideTotal = null;
            this.showCheckOut = true;
            // Any member id works — backend gathers the whole group from group_id
            await this.loadCheckoutPreview(group.members[0].id);
        },

        async loadCheckoutPreview(sessionId, soloOut = false) {
            try {
                const soloParam = soloOut ? "&solo=1" : "";
                const res = await axios.post(
                    `/api/lounge/check-out/${sessionId}?preview=1${soloParam}`,
                    {},
                    { headers: this.headers() },
                );
                if (res.data.success) {
                    this.checkoutResult = res.data;
                    this._checkoutId = sessionId;
                }
            } catch (e) {
                console.error(e);
            }
        },

        async confirmCheckout() {
            this.confirmingCheckout = true;
            const payload = {};
            if (this.overrideOn && this.overrideTotal !== null) {
                payload.override_total = this.overrideTotal;
            }
            try {
                const soloParam = this.checkoutSolo ? "?solo=1" : "";
                await axios.post(
                    `/api/lounge/check-out/${this._checkoutId}${soloParam}`,
                    payload,
                    { headers: this.headers() },
                );
                this.showCheckOut = false;
                this.checkoutResult = null;
                await this.fetchSessions();
                await this.fetchHistory();
            } catch (e) {
                console.error(e);
            } finally {
                this.confirmingCheckout = false;
            }
        },
    },
};
</script>
