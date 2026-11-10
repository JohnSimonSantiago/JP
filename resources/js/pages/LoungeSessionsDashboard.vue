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
                <div class="flex items-center gap-2">
                    <!-- QR scanner (Clabel T27H, types like a keyboard + Enter). Hidden,
                         always listening; refocuses itself unless you're typing in a text box. -->
                    <input
                        ref="scanInput"
                        type="text"
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        aria-label="QR scanner input"
                        class="absolute opacity-0 w-px h-px -left-[9999px]"
                    />
                    <span
                        class="hidden sm:flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-lg border"
                        :class="
                            scannerState.scannerFocused
                                ? 'border-green-200 bg-green-50 text-green-700'
                                : 'border-gray-200 text-gray-400'
                        "
                        :title="
                            scannerState.scannerFocused
                                ? 'QR scanner is listening'
                                : 'Paused while you type in a box. Click an empty spot to resume.'
                        "
                    >
                        <span
                            class="w-2 h-2 rounded-full"
                            :class="
                                scannerState.scannerFocused
                                    ? 'bg-green-500'
                                    : 'bg-gray-300'
                            "
                        ></span>
                        <i class="pi pi-qrcode"></i>
                        {{
                            scannerState.scannerFocused
                                ? "Scanner ready"
                                : "Scanner paused"
                        }}
                    </span>
                    <button
                        @click="connectRfid"
                        :disabled="scannerState.rfidConnected"
                        class="border px-3 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                        :class="
                            scannerState.rfidConnected
                                ? 'border-green-200 bg-green-50 text-green-700'
                                : 'border-gray-200 text-gray-600 hover:bg-gray-50'
                        "
                    >
                        <i class="pi pi-id-card"></i>
                        {{
                            scannerState.rfidConnected
                                ? "RFID ready"
                                : "Connect RFID"
                        }}
                    </button>
                    <button
                        @click="openCheckIn()"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2"
                    >
                        <i class="pi pi-plus"></i> Check In
                    </button>
                </div>
            </div>

            <!-- RFID tap result -->
            <div
                v-if="rfidStatus"
                class="mb-4 rounded-lg px-4 py-3 text-sm font-medium border"
                :class="{
                    'bg-green-50 border-green-200 text-green-700':
                        rfidStatus.type === 'success',
                    'bg-indigo-50 border-indigo-200 text-indigo-700':
                        rfidStatus.type === 'info',
                    'bg-yellow-50 border-yellow-200 text-yellow-700':
                        rfidStatus.type === 'warn',
                    'bg-red-50 border-red-200 text-red-700':
                        rfidStatus.type === 'error',
                }"
            >
                <i
                    class="mr-2"
                    :class="
                        rfidStatus.source === 'qr' ? 'pi pi-qrcode' : 'pi pi-id-card'
                    "
                ></i>{{ rfidStatus.text }}
                <span
                    v-if="rfidStatus.source"
                    class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-white/70 border border-current"
                    >via {{ rfidStatus.source === "qr" ? "QR" : "RFID" }}</span
                >
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
                                <span
                                    v-if="shortMinutes(session) > 0"
                                    class="ml-1 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 rounded-full px-2 py-0.5 align-middle"
                                    >Negative balance
                                    {{
                                        formatMinutesBalance(-shortMinutes(session))
                                    }}</span
                                >
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
                        ⏱ 5-min grace period per 30-min block applies
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
                                    <span
                                        v-if="shortMinutes(m) > 0"
                                        class="ml-1 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 rounded-full px-2 py-0.5"
                                        >Negative balance
                                        {{
                                            formatMinutesBalance(-shortMinutes(m))
                                        }}</span
                                    >
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
                                <p class="text-xs text-gray-400">
                                    In {{ formatTime(m.checked_in_at) }}
                                </p>
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
                    <div class="flex items-baseline gap-3">
                        <h3 class="font-semibold text-gray-700">
                            Session History
                        </h3>
                        <span
                            v-if="!loadingHistory && history.length > 0"
                            class="text-sm"
                        >
                            <span class="text-gray-400">
                                {{ rangeLabel }}:
                            </span>
                            <span class="font-bold text-green-600">
                                ₱{{ historyPaidTotal }}
                            </span>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <select
                            v-model="rangePreset"
                            @change="applyPreset"
                            @click="applyPreset"
                            class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        >
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="week">This week</option>

                            <option value="month">This month</option>
                            <option value="all">All Time</option>
                            <option value="custom">Custom</option>
                        </select>
                        <input
                            v-model="fromDate"
                            type="date"
                            class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            @change="onManualDateChange"
                        />
                        <span class="text-gray-400 text-sm">to</span>
                        <input
                            v-model="toDate"
                            type="date"
                            class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            @change="onManualDateChange"
                        />
                    </div>
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
                    No completed sessions for this range.
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
                                            : s.billing_mode === 'consumable'
                                              ? 'text-indigo-600'
                                              : 'text-gray-800'
                                    "
                                >
                                    <span v-if="s.is_free">Free</span>
                                    <span
                                        v-else-if="
                                            s.billing_mode === 'consumable'
                                        "
                                    >
                                        {{ getDuration(s) }} used
                                    </span>
                                    <span v-else>₱{{ s.total_bill }}</span>
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

                        <!-- School (optional) — one value for the whole check-in event -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >School (optional)</label
                            >
                            <input
                                v-model="checkInForm.school"
                                type="text"
                                placeholder="e.g. MMSU"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            />
                        </div>

                        <!-- Duration / Open time (affects printed Time out only) -->
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Duration</label
                            >
                            <div
                                class="grid grid-cols-2 gap-2 mb-2 bg-gray-100 p-1 rounded-lg"
                            >
                                <button
                                    type="button"
                                    @click="checkInForm.open_time = true"
                                    :class="
                                        checkInForm.open_time
                                            ? 'bg-white shadow-sm text-indigo-600'
                                            : 'text-gray-500'
                                    "
                                    class="py-1.5 rounded-md text-sm font-medium transition-all"
                                >
                                    Open Time
                                </button>
                                <button
                                    type="button"
                                    @click="checkInForm.open_time = false"
                                    :class="
                                        !checkInForm.open_time
                                            ? 'bg-white shadow-sm text-indigo-600'
                                            : 'text-gray-500'
                                    "
                                    class="py-1.5 rounded-md text-sm font-medium transition-all"
                                >
                                    Set Time
                                </button>
                            </div>

                            <div v-if="!checkInForm.open_time">
                                <!-- Hour presets -->
                                <div class="flex gap-2 mb-2">
                                    <button
                                        v-for="hr in [1, 2, 3]"
                                        :key="hr"
                                        type="button"
                                        @click="
                                            checkInForm.duration_minutes =
                                                hr * 60
                                        "
                                        :class="
                                            checkInForm.duration_minutes ===
                                            hr * 60
                                                ? 'border-indigo-400 bg-indigo-50 text-indigo-600'
                                                : 'border-gray-200 text-gray-600'
                                        "
                                        class="flex-1 border rounded-lg py-1.5 text-sm font-medium transition-all"
                                    >
                                        {{ hr }}hr
                                    </button>
                                </div>

                                <!-- ±30 min stepper -->
                                <div
                                    class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2"
                                >
                                    <button
                                        type="button"
                                        @click="adjustDuration(-30)"
                                        class="w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-600 font-bold hover:bg-gray-100"
                                    >
                                        −
                                    </button>
                                    <span
                                        class="text-sm font-semibold text-gray-700"
                                    >
                                        {{
                                            formatDuration(
                                                checkInForm.duration_minutes,
                                            )
                                        }}
                                    </span>
                                    <button
                                        type="button"
                                        @click="adjustDuration(30)"
                                        class="w-8 h-8 rounded-full bg-white border border-gray-200 text-gray-600 font-bold hover:bg-gray-100"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>
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
                                    class="px-3 py-2 text-sm flex items-center justify-between"
                                    :class="
                                        memberBlockReason(u.id)
                                            ? 'opacity-50 cursor-not-allowed'
                                            : 'hover:bg-gray-50 cursor-pointer'
                                    "
                                >
                                    <span>{{ u.name }}</span>
                                    <span
                                        v-if="memberBlockReason(u.id)"
                                        class="text-xs text-red-500 font-medium"
                                        >{{ memberBlockReason(u.id) }}</span
                                    >
                                    <span v-else class="text-xs text-gray-400"
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
                                <!-- Members always use consumable time (no "Pay by Hour") -->
                                <div
                                    class="bg-gray-100 rounded-lg px-3 py-1.5 text-sm font-medium text-indigo-600 flex items-center gap-2"
                                >
                                    <i class="pi pi-clock text-xs"></i>
                                    Consumable time
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
                                        >— counts down below zero; they'll need
                                        to buy time</span
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
                                        In
                                        {{ formatTime(line.checked_in_at) }}
                                        · {{ line.duration }}
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
                                    <template v-if="line.is_free"
                                        >Free</template
                                    >
                                    <template v-else>
                                        <!-- Override: show the original bill crossed out -->
                                        <span
                                            v-if="line.share != line.bill"
                                            class="text-xs text-gray-400 line-through font-normal mr-1"
                                            >₱{{ line.bill }}</span
                                        >₱{{ line.share ?? line.bill }}
                                    </template>
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
                                    step="1"
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

            <!-- ── Link RFID Card Modal ── -->
            <div
                v-if="showLinkCard"
                class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6"
                >
                    <h3 class="text-lg font-bold text-gray-800 mb-1">
                        Link RFID Card
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        New card
                        <span class="font-mono font-semibold">{{
                            linkUid
                        }}</span>
                        — pick the member who owns it.
                    </p>
                    <input
                        v-model="linkSearch"
                        @input="searchLinkUsers"
                        type="text"
                        placeholder="Type a name..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                    />
                    <div
                        v-if="linkResults.length > 0"
                        class="border border-gray-200 rounded-lg mt-1 max-h-48 overflow-y-auto shadow-sm"
                    >
                        <div
                            v-for="u in linkResults"
                            :key="u.id"
                            @click="linkCardTo(u)"
                            class="px-3 py-2 text-sm flex items-center justify-between hover:bg-gray-50 cursor-pointer"
                            :class="
                                linking ? 'opacity-50 pointer-events-none' : ''
                            "
                        >
                            <span>{{ u.name }}</span>
                            <span class="text-xs text-gray-400"
                                >Level {{ u.level }}</span
                            >
                        </div>
                    </div>
                    <div v-if="linkError" class="text-red-500 text-sm mt-3">
                        {{ linkError }}
                    </div>
                    <button
                        @click="closeLinkCard"
                        class="w-full mt-4 border border-gray-200 text-gray-600 py-2 rounded-lg text-sm hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>

            <!-- ── Not Enough Time Modal (staff scan of a member who'd end below zero) ── -->
            <div
                v-if="notEnough"
                class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50"
            >
                <div
                    class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6"
                >
                    <h3 class="text-lg font-bold text-gray-800 mb-1">
                        Not enough time
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        <span class="font-semibold text-gray-700">{{
                            notEnough.user.name
                        }}</span>
                        would end with a negative balance of
                        <span class="font-semibold text-red-600">{{
                            formatMinutesBalance(-notEnough.shortMinutes)
                        }}</span>
                        ({{ notEnough.shortMinutes }} min).
                    </p>

                    <!-- Buy Time -->
                    <div
                        v-if="!notEnough.bought"
                        class="border border-gray-200 rounded-lg p-4 mb-4"
                    >
                        <p class="text-sm font-medium text-gray-700 mb-2">
                            Buy Time
                        </p>
                        <div class="flex items-center gap-3 mb-3">
                            <button
                                @click="
                                    notEnough.hours = Math.max(
                                        1,
                                        notEnough.hours - 1,
                                    )
                                "
                                class="w-9 h-9 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50"
                            >
                                <i class="pi pi-minus text-xs"></i>
                            </button>
                            <span
                                class="text-lg font-bold text-gray-800 w-12 text-center"
                                >{{ notEnough.hours }}h</span
                            >
                            <button
                                @click="
                                    notEnough.hours = Math.min(
                                        24,
                                        notEnough.hours + 1,
                                    )
                                "
                                class="w-9 h-9 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50"
                            >
                                <i class="pi pi-plus text-xs"></i>
                            </button>
                            <span class="ml-auto text-lg font-bold text-indigo-600"
                                >₱{{ consumablePrice(notEnough.hours) }}</span
                            >
                        </div>
                        <div class="flex gap-2 mb-2">
                            <button
                                v-for="pm in [
                                    { key: 'cash', label: 'Cash' },
                                    { key: 'balance', label: 'Coins' },
                                ]"
                                :key="pm.key"
                                @click="notEnough.paymentMethod = pm.key"
                                class="flex-1 border rounded-lg px-3 py-2 text-sm transition-colors"
                                :class="
                                    notEnough.paymentMethod === pm.key
                                        ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-medium'
                                        : 'border-gray-200 text-gray-600 hover:bg-gray-50'
                                "
                            >
                                {{ pm.label }}
                                <template v-if="pm.key === 'balance'">
                                    (<CoinAmount
                                        :value="notEnough.user.cash || 0"
                                        :label="false"
                                    />)
                                </template>
                            </button>
                        </div>
                        <p
                            v-if="
                                notEnough.paymentMethod === 'balance' &&
                                Number(notEnough.user.cash || 0) <
                                    consumablePrice(notEnough.hours)
                            "
                            class="text-xs text-red-500 mb-2"
                        >
                            Not enough Coins for this amount.
                        </p>
                        <p class="text-xs text-gray-500">
                            Balance after time out:
                            <span class="font-semibold">{{
                                formatMinutesBalance(
                                    notEnough.newBalance + notEnough.hours * 60,
                                )
                            }}</span>
                        </p>
                    </div>
                    <div
                        v-else
                        class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-3 py-2 text-sm mb-4"
                    >
                        {{ notEnough.hours }}h bought. Only the time out is left.
                    </div>

                    <div
                        v-if="notEnough.error"
                        class="text-red-500 text-sm mb-3"
                    >
                        {{ notEnough.error }}
                    </div>

                    <button
                        @click="buyAndTimeOut"
                        :disabled="
                            notEnough.busy ||
                            (!notEnough.bought &&
                                notEnough.paymentMethod === 'balance' &&
                                Number(notEnough.user.cash || 0) <
                                    consumablePrice(notEnough.hours))
                        "
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm font-medium transition-colors disabled:opacity-50"
                    >
                        {{
                            notEnough.busy
                                ? "Working..."
                                : notEnough.bought
                                  ? "Retry Time Out"
                                  : "Buy & Time Out"
                        }}
                    </button>
                    <button
                        v-if="!notEnough.bought"
                        @click="timeOutWithoutBuying"
                        :disabled="notEnough.busy"
                        class="w-full mt-2 border border-gray-200 text-gray-600 py-2 rounded-lg text-sm hover:bg-gray-50 transition-colors disabled:opacity-50"
                    >
                        Time Out Without Buying
                    </button>
                    <button
                        @click="closeNotEnough"
                        :disabled="notEnough.busy"
                        class="w-full mt-2 text-sm text-gray-400 hover:text-gray-600 transition-colors disabled:opacity-50"
                    >
                        Cancel (stay checked in)
                    </button>
                </div>
            </div>
        </div>
    </Layout>
</template>
<script>
import CoinAmount from "@/components/CoinAmount.vue";
import { RECEIPTS_ENABLED } from "@/config/features";
import { createLoungeScanner } from "@/lounge/scanner";
export default {
    name: "LoungeSessionsDashboard",
    components: { CoinAmount },
    data() {
        return {
            activeSessions: [],
            history: [],
            loadingSessions: true,
            loadingHistory: false,
            fromDate: null,
            toDate: null,
            todayDate: null,
            rangePreset: "today",
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
                school: "",
                open_time: true, // true = no fixed end; false = use duration_minutes
                duration_minutes: 60, // chosen length when not open time
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

            // Scanners (RFID reader + T27H QR) — shared module, same as the kiosk screen.
            // scannerState is the module's reactive state: rfidConnected, scannerFocused, busy.
            scannerState: { rfidConnected: false, scannerFocused: false, busy: false },
            rfidStatus: null, // { type: 'success' | 'info' | 'warn' | 'error', text, source }
            rfidStatusTimer: null,

            // "Not enough time" window: staff scanned a member who'd end below zero
            notEnough: null,

            // Link-a-card modal
            showLinkCard: false,
            linkUid: null,
            linkSearch: "",
            linkResults: [],
            linkError: null,
            linking: false,
            linkSearchTimeout: null,

        };
    },

    computed: {
        // A staff window is open (scans still work; only results that need a window wait)
        anyWindowOpen() {
            return (
                this.showCheckIn ||
                this.showCheckOut ||
                this.showLinkCard ||
                !!this.notEnough
            );
        },

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
        historyPaidTotal() {
            return this.history.reduce(
                (sum, s) => sum + (Number(s.total_bill) || 0),
                0,
            );
        },
        rangeLabel() {
            if (this.rangePreset === "all") return "All time";
            if (this.fromDate === this.toDate) {
                return this.fromDate === this.todayDate ? "Today" : "That day";
            }
            if (this.rangePreset === "week") return "This week";
            if (this.rangePreset === "month") return "This month";
            return "Range";
        },
        // The backend's total (override already rounded to whole pesos there)
        displayTotal() {
            return this.checkoutResult?.total_bill ?? 0;
        },
    },

    watch: {
        // Typing an override re-asks the backend for the split, so the preview
        // shows exactly what will be saved (the split rule lives only in Laravel)
        overrideTotal() {
            this.refreshCheckoutPreview();
        },
        overrideOn() {
            this.refreshCheckoutPreview();
        },
    },

    async mounted() {
        const today = this.localDate(new Date());
        this.fromDate = today;
        this.toDate = today;
        this.todayDate = today;

        await this.fetchSessions();
        await this.fetchHistory();
        this.tickInterval = setInterval(() => {
            this.tick++;
        }, 1000);

        // Scanners: same module (and same /lounge/scan decision) as the kiosk screen
        this._scanner = createLoungeScanner({
            getToken: () => localStorage.getItem("auth-token"),
            onResult: (data) => this.onScanResult(data),
            onError: (message) => this.showRfidStatus("error", message),
            onStatus: (type, text) => this.showRfidStatus(type, text),
        });
        this.scannerState = this._scanner.state;
        this._scanner.attachInput(this.$refs.scanInput);
        this._scanner.autoConnectRfid();

        // Kiosk screens scan without this page, so refresh every 10 seconds
        this._refreshTimer = setInterval(() => {
            if (!document.hidden) this.refreshLounge();
        }, 10000);

        // Customer screen (/lounge/display) listens on this channel
        if ("BroadcastChannel" in window) {
            this._displayChannel = new BroadcastChannel("level-lounge-display");
        }
    },

    beforeUnmount() {
        clearInterval(this.tickInterval);
        clearTimeout(this.rfidStatusTimer);
        clearInterval(this._refreshTimer);
        this._scanner?.destroy();
        this._displayChannel?.close();
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

        adjustDuration(delta) {
            // Never below 30 min; cap at a sane 12 hours
            const next = (this.checkInForm.duration_minutes || 0) + delta;
            this.checkInForm.duration_minutes = Math.max(
                30,
                Math.min(next, 720),
            );
        },

        formatDuration(mins) {
            mins = mins || 0;
            const h = Math.floor(mins / 60);
            const m = mins % 60;
            if (h && m) return `${h}h ${m}m`;
            if (h) return `${h}h`;
            return `${m}m`;
        },

        async fetchSessions(silent = false) {
            if (!silent) this.loadingSessions = true;
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

        localDate(d) {
            // Avoids the UTC shift that toISOString() causes in PH time
            const off = d.getTimezoneOffset() * 60000;
            return new Date(d.getTime() - off).toISOString().split("T")[0];
        },

        applyPreset() {
            const now = new Date();

            if (this.rangePreset === "today") {
                this.fromDate = this.localDate(now);
                this.toDate = this.localDate(now);
            } else if (this.rangePreset === "yesterday") {
                const yesterday = new Date(now);
                yesterday.setDate(now.getDate() - 1);
                this.fromDate = this.localDate(yesterday);
                this.toDate = this.localDate(yesterday);
            } else if (this.rangePreset === "all") {
                this.fromDate = null;
                this.toDate = null;
            } else if (this.rangePreset === "week") {
                // Monday as the start of the week
                const day = now.getDay();
                const diff = day === 0 ? 6 : day - 1;
                const monday = new Date(now);
                monday.setDate(now.getDate() - diff);
                this.fromDate = this.localDate(monday);
                this.toDate = this.localDate(now);
            } else if (this.rangePreset === "month") {
                const first = new Date(now.getFullYear(), now.getMonth(), 1);
                this.fromDate = this.localDate(first);
                this.toDate = this.localDate(now);
            } else {
                return; // custom — leave dates alone
            }

            this.fetchHistory();
        },

        onManualDateChange() {
            this.rangePreset = "custom";
            this.fetchHistory();
        },

        async fetchHistory() {
            this.loadingHistory = true;
            try {
                const res = await axios.get("/api/lounge/session-history", {
                    headers: this.headers(),
                    params: { from: this.fromDate, to: this.toDate },
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

        // ── Duplicate guards ──
        isCheckedIn(userId) {
            const id = Number(userId);
            return this.activeSessions.some(
                (s) => Number(s.user_id ?? s.user?.id) === id,
            );
        },
        isQueued(userId) {
            const id = Number(userId);
            return this.pendingGroup.some((p) => Number(p.user_id) === id);
        },
        memberBlockReason(userId) {
            if (!userId) return null;
            if (this.isCheckedIn(userId)) return "Already checked in";
            if (this.isQueued(userId)) return "Already in this group";
            return null;
        },

        selectUser(u) {
            const blocked = this.memberBlockReason(u.id);
            if (blocked) {
                this.checkInError = `${u.name} — ${blocked.toLowerCase()}.`;
                return;
            }
            this.checkInError = null;
            this.selectedUser = u;
            this.checkInForm.user_id = u.id;
            this.checkInForm.customer_name = u.name;
            this.userResults = [];
            this.userSearch = u.name;

            // Level 1 members ALWAYS use consumable time, even at 0 or negative
            // (same rule the backend enforces in checkIn). Level 2/3 are free.
            this.checkInForm.billing_mode =
                u.level === 1 ? "consumable" : "hourly";
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
            // (No balance check: members time in on consumable time even at 0 or
            // negative; the balance just counts down further.)
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
                school: "",
                open_time: true,
                duration_minutes: 60,
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

            // (No balance check: members time in on consumable time even at 0 or
            // negative, same as the backend.)

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

            // Snapshot info for the slip before we clear the form
            const school = this.checkInForm.school;
            const groupId = this.checkInForm.group_id;
            const isGroup = people.length > 1;
            const openTime = this.checkInForm.open_time;
            const durationMinutes = this.checkInForm.duration_minutes;

            this.checkingIn = true;
            const done = []; // people the server accepted
            try {
                // Re-check the whole list against the latest sessions before sending anything
                await this.fetchSessions(true);
                const seen = new Set();
                for (const p of people) {
                    if (!p.user_id) continue; // walk-ins have no account to check
                    const id = Number(p.user_id);
                    if (seen.has(id)) {
                        this.checkInError = `${p.customer_name} is in the list twice.`;
                        return;
                    }
                    if (this.isCheckedIn(id)) {
                        this.checkInError = `${p.customer_name} is already checked in.`;
                        return;
                    }
                    seen.add(id);
                }

                // Check each person in, all sharing the same group_id (may be null for solo)
                for (const person of people) {
                    await axios.post(
                        "/api/lounge/check-in",
                        {
                            ...person,
                            group_id: groupId,
                            school: school,
                        },
                        { headers: this.headers() },
                    );
                    done.push(person);
                }

                // One CN number per check-in event (whole group shares one)
                let cn = null;
                try {
                    const res = await axios.post(
                        "/api/lounge/reserve-receipt",
                        { group_id: groupId },
                        { headers: this.headers() },
                    );
                    if (res.data.success) cn = res.data.number;
                } catch (e) {
                    console.error("CN reserve failed", e);
                }

                // Print the slip (one per event). Skipped when RECEIPTS_ENABLED is off;
                // the CN number above is still reserved either way.
                this.printCheckInSlip({
                    people,
                    school,
                    isGroup,
                    cn,
                    openTime,
                    durationMinutes,
                });

                this.showCheckIn = false;
                this.resetCheckIn();
                await this.fetchSessions();
            } catch (e) {
                let msg = e.response?.data?.message || "Check-in failed.";

                if (done.length > 0) {
                    // Some got in before the failure — remove them so a retry only sends the rest
                    const formDone = done.some(
                        (p) => !this.pendingGroup.includes(p),
                    );
                    this.pendingGroup = this.pendingGroup.filter(
                        (p) => !done.includes(p),
                    );
                    if (formDone) this.clearPersonFields();

                    msg += ` (${done.map((p) => p.customer_name).join(", ")} already checked in — press Check In again for the rest)`;
                    await this.fetchSessions(true);
                }

                this.checkInError = msg;
            } finally {
                this.checkingIn = false;
            }
        },

        // ── Receipt printing ──
        // Opens a hidden 80mm window and prints one slip. Reusable for reprints.
        // Every print goes through here, so the switch in config/features.js covers all of them.
        printCheckInSlip({
            people,
            school,
            isGroup,
            cn,
            openTime,
            durationMinutes,
        }) {
            if (!RECEIPTS_ENABLED) return; // receipts off: no pop-up, no print dialog

            const now = new Date();
            const dateStr = now.toLocaleDateString("en-PH");
            const timeIn = now.toLocaleTimeString("en-PH", {
                hour: "2-digit",
                minute: "2-digit",
            });

            // Time out: computed clock time, or "OPEN TIME"
            let timeOut;
            let durationText;
            if (openTime) {
                timeOut = "OPEN TIME";
                durationText = "Open Time";
            } else {
                const out = new Date(now.getTime() + durationMinutes * 60000);
                timeOut = out.toLocaleTimeString("en-PH", {
                    hour: "2-digit",
                    minute: "2-digit",
                });
                durationText = this.formatDuration(durationMinutes);
            }

            const cnStr =
                cn !== null ? "CN:" + String(cn).padStart(3, "0") : "CN:___";

            // Individual → the one name. Group → a numbered list of everyone.
            const membersLine = isGroup ? String(people.length) : "1";
            const esc0 = (s) =>
                String(s ?? "").replace(
                    /[&<>]/g,
                    (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;" })[c],
                );

            let nameBlock;
            if (isGroup) {
                const list = people
                    .map((p, i) => `${i + 1}. ${esc0(p.customer_name)}`)
                    .join("<br>");
                nameBlock = `Full Name (Group of ${people.length}):<br>${list}`;
            } else {
                nameBlock = `Full Name: ${esc0(people[0]?.customer_name || "")}`;
            }

            const esc = (s) =>
                String(s ?? "").replace(
                    /[&<>]/g,
                    (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;" })[c],
                );

            const html = `
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { size: 80mm auto; margin: 0; }
  * { box-sizing: border-box; }
  body {
    width: 80mm;
    margin: 0;
    padding: 4mm 5mm;
    font-family: 'Courier New', monospace;
    color: #000;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    font-weight: bold;
  }
  .brand { text-align: center; font-size: 18px; font-weight: 900; margin-bottom: 2mm; }
  .row { font-size: 13px; margin: 2mm 0; font-weight: bold; }
  .lbl { display: inline-block; }
  .fill { border-bottom: 1px solid #000; display: inline-block; min-width: 30mm; }
  .type { font-size: 13px; margin: 2mm 0; font-weight: bold; }
  .cn { text-align: left; font-size: 16px; font-weight: 900; margin-top: 4mm; }
  hr { border: none; border-top: 1px dashed #000; margin: 2mm 0; }
</style>
</head>
<body>
  <div class="brand" style="display:flex; align-items:center; justify-content:center; gap:3mm;">
    <svg width="40" height="40" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
      <!-- Up arrow: wider arrowhead, even hollow border -->
      <path fill-rule="evenodd" fill="#000" d="
        M32 8 L66 48 L47 48 L47 108 L17 108 L17 48 L-2 48 Z
        M32 26 L50 44 L37 44 L37 98 L27 98 L27 44 L14 44 Z"/>
      <!-- Down arrow: wider arrowhead, even hollow border -->
      <path fill-rule="evenodd" fill="#000" d="
        M88 112 L122 72 L103 72 L103 12 L73 12 L73 72 L54 72 Z
        M88 94 L106 76 L93 76 L93 22 L83 22 L83 76 L70 76 Z"/>
    </svg>
    <span>LEVEL LOUNGE</span>
  </div>
  <div style="text-align:center; font-size:12px; margin-bottom:2mm;">
    Brgy 5. Ablan Avenue<br>
    Beside Calle Kape and Starlight Lodging House
  </div>
  <hr>
  <div class="row"><span class="lbl">Date: ${esc(dateStr)}</span></div>
  <div class="row">Time in: ${esc(timeIn)}</div>
  <div class="row">Time out: ${esc(timeOut)}</div>
  <div class="type">
    Registration Type: <strong>${isGroup ? "Group" : "Individual"}</strong>
  </div>
  <div class="row">Duration: ${esc(durationText)}</div>
  <div class="row">${nameBlock}</div>
  <div class="row">School: ${esc(school || "-")}</div>
  <div class="row">Number of Members: ${esc(membersLine)}</div>
  <div class="row" style="margin-top:4mm">
    Signature: <span class="fill" style="min-width:35mm"></span>
  </div>
  <div class="cn">${esc(cnStr)}</div>
  <div style="text-align:center; margin-top:5mm; font-size:10px;">.</div>
</body>
</html>`;

            const w = window.open("", "_blank", "width=380,height=600");
            if (!w) {
                console.error("Popup blocked — allow popups to print slips.");
                return;
            }
            w.document.write(html);
            w.document.close();
            w.focus();
            setTimeout(() => {
                w.print();
                w.close();
            }, 300);
        },

        // ── Checkout flows ──
        // soloOut = true forces just this person even if they're in a group
        async openCheckout(session, soloOut = false) {
            this._rfidHourlyOut = null; // set again by onScanResult for a scanned hourly session
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
            this._rfidHourlyOut = null;
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
            const requestNo = (this._previewRequestNo = (this._previewRequestNo || 0) + 1);
            try {
                const soloParam = soloOut ? "&solo=1" : "";
                const res = await axios.post(
                    `/api/lounge/check-out/${sessionId}?preview=1${soloParam}`,
                    this.overridePayload(),
                    { headers: this.headers() },
                );
                // Ignore an older answer that arrives after a newer one
                if (requestNo !== this._previewRequestNo) return;
                if (res.data.success) {
                    this.checkoutResult = res.data;
                    this._checkoutId = sessionId;
                }
            } catch (e) {
                console.error(e);
            }
        },

        // Same override value for the preview and the real time out
        overridePayload() {
            const payload = {};
            if (
                this.overrideOn &&
                this.overrideTotal !== null &&
                this.overrideTotal !== ""
            ) {
                payload.override_total = this.overrideTotal;
            }
            return payload;
        },

        refreshCheckoutPreview() {
            if (!this.showCheckOut || !this._checkoutId) return;
            clearTimeout(this._previewTimer);
            this._previewTimer = setTimeout(() => {
                this.loadCheckoutPreview(this._checkoutId, this.checkoutSolo);
            }, 300);
        },

        async confirmCheckout() {
            this.confirmingCheckout = true;
            clearTimeout(this._previewTimer);
            const payload = this.overridePayload();
            try {
                const soloParam = this.checkoutSolo ? "?solo=1" : "";
                const res = await axios.post(
                    `/api/lounge/check-out/${this._checkoutId}${soloParam}`,
                    payload,
                    { headers: this.headers() },
                );
                if (this._rfidHourlyOut) {
                    this.postDisplay({
                        kind: "out",
                        name: this._rfidHourlyOut.name,
                        mode: "hourly",
                        used: res.data.line_items?.[0]?.duration,
                        total: res.data.total_bill,
                    });
                    this._rfidHourlyOut = null;
                }
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

        // ── Scanning (RFID + QR) ──
        // The shared scanner module reads both scanners and calls /lounge/scan, where
        // the backend decides time in / time out. This page shows the result and adds
        // the staff-only extras: link a new card, bill an old hourly session.
        connectRfid() {
            this._scanner?.connectRfid();
        },

        showRfidStatus(type, text, source = null) {
            this.rfidStatus = { type, text, source };
            clearTimeout(this.rfidStatusTimer);
            this.rfidStatusTimer = setTimeout(() => {
                this.rfidStatus = null;
            }, 6000);
        },

        async onScanResult(data) {
            const src = data.source;
            const name = data.user?.name;

            if (data.result === "in") {
                const short = data.short_minutes > 0;
                this.showRfidStatus(
                    short ? "warn" : "success",
                    short
                        ? `${name} timed in — negative balance ${this.formatMinutesBalance(-data.short_minutes)}. Ask them to buy time.`
                        : `${name} timed in.`,
                    src,
                );
                this.postDisplay({ kind: "in", name, mode: data.mode, balance: data.balance });
                await this.refreshLounge();
                return;
            }

            if (data.result === "out") {
                let text = data.message;
                if (data.mode === "consumable") {
                    text = `${name} timed out — ${this.formatMinutesBalance(data.balance)} left`;
                }
                this.showRfidStatus("success", text, src);
                this.postDisplay({ kind: "out", name, mode: data.mode, used: data.used, balance: data.balance });
                await this.refreshLounge();
                return;
            }

            // Would end below zero: not timed out. Open the "Not enough time" window.
            if (data.result === "short") {
                this.postDisplay({ kind: "short", name, short: data.short_minutes, used: data.used, balance: data.balance });
                if (this.anyWindowOpen) {
                    this.showRfidStatus("warn", `${data.message} Finish or close the open window first, then scan again.`, src);
                    this._scanner?.forgetLastCode();
                } else {
                    this.showRfidStatus("warn", data.message, src);
                    this.openNotEnough(data);
                }
                return;
            }

            if (data.result === "duplicate") {
                this.showRfidStatus("info", data.message, src);
                return;
            }

            if (data.result === "unknown") {
                this.postDisplay({ kind: "unknown", source: src });
                // A new RFID card -> offer to link it (staff only), unless a window is open
                if (src === "rfid" && data.uid) {
                    if (this.anyWindowOpen) {
                        this.showRfidStatus("warn", "New card: finish or close the open window first, then tap again.", src);
                        this._scanner?.forgetLastCode();
                    } else {
                        this.openLinkCard(data.uid);
                    }
                } else {
                    this.showRfidStatus("error", data.message, src);
                }
                return;
            }

            if (data.result === "see_staff") {
                this.postDisplay({ kind: "see_staff", source: src });
                // An old hourly session: open its bill (just this person)
                const session = data.session_id
                    ? this.activeSessions.find((s) => s.id === data.session_id)
                    : null;
                if (data.reason === "hourly_bill" && session && !this.anyWindowOpen) {
                    this.showRfidStatus("info", `${name} — pays by the hour. Confirm the bill.`, src);
                    await this.openCheckout(session, true);
                    this._rfidHourlyOut = { name };
                } else {
                    this.showRfidStatus("warn", data.message, src);
                }
                return;
            }

            this.showRfidStatus("error", data.message || "Scan failed.", src);
        },

        // Active sessions, quietly (no spinner)
        async refreshLounge() {
            await this.fetchSessions(true);
        },

        // How far below zero a consumable member already is (timed in negative)
        shortMinutes(session) {
            if (session.billing_mode !== "consumable") return 0;
            const bal = session.user?.consumable_minutes ?? 0;
            return bal < 0 ? -bal : 0;
        },

        // ── "Not enough time" window ──
        // From a staff scan ("short"): the member is still checked in.
        openNotEnough(data) {
            const short = data.short_minutes;
            this.notEnough = {
                sessionId: data.session_id,
                source: data.source,
                user: { id: data.user.id, name: data.user.name, cash: data.wallet?.cash ?? 0 },
                shortMinutes: short,
                newBalance: data.balance_after, // if timed out now, without buying
                hours: Math.min(24, Math.max(1, Math.ceil(short / 60))),
                paymentMethod: "cash",
                bought: false, // time already bought, only the time out is left
                busy: false,
                error: null,
            };
        },

        closeNotEnough() {
            this.notEnough = null; // they stay checked in
            this._scanner?.forgetLastCode();
        },

        // Same prices as the Buy Time page: ₱100 per 3 hours, ₱40 per extra hour
        // (the backend charges the real amount)
        consumablePrice(hours) {
            return Math.floor(hours / 3) * 100 + (hours % 3) * 40;
        },

        async buyAndTimeOut() {
            const n = this.notEnough;
            if (!n || n.busy) return;
            n.busy = true;
            n.error = null;

            if (!n.bought) {
                try {
                    await axios.post(
                        "/api/lounge/consumable/buy",
                        { user_id: n.user.id, hours: n.hours, payment_method: n.paymentMethod },
                        { headers: this.headers() },
                    );
                    n.bought = true;
                } catch (e) {
                    n.error = e.response?.data?.message || "Couldn't buy the time. Nothing was charged.";
                    n.busy = false;
                    return;
                }
            }

            await this.finishNotEnough(`bought ${n.hours}h and timed out`);
        },

        async timeOutWithoutBuying() {
            const n = this.notEnough;
            if (!n || n.busy) return;
            n.busy = true;
            n.error = null;
            await this.finishNotEnough("timed out");
        },

        // Times out just this person (staff checkout). Balance may go negative.
        async finishNotEnough(action) {
            const n = this.notEnough;
            try {
                const res = await axios.post(
                    `/api/lounge/check-out/${n.sessionId}?solo=1`,
                    {},
                    { headers: this.headers() },
                );
                const line = res.data.line_items?.[0] || {};
                const bal = line.new_balance ?? 0;
                this.notEnough = null;
                this.showRfidStatus(
                    bal >= 0 ? "success" : "warn",
                    `${n.user.name} ${action} — ` +
                        (bal >= 0
                            ? `${this.formatMinutesBalance(bal)} left`
                            : `negative balance ${this.formatMinutesBalance(bal)}`),
                    n.source,
                );
                this.postDisplay({ kind: "out", name: n.user.name, mode: "consumable", used: line.duration, balance: bal });
                await this.refreshLounge();
            } catch (e) {
                n.error =
                    (n.bought
                        ? "The time was bought, but the time out failed: "
                        : "Time out failed: ") +
                    (e.response?.data?.message || "check the connection.");
                n.busy = false;
            }
        },

        // Sends one result to the customer screen in this browser (if it's open).
        // Never breaks the counter if the display tab isn't open.
        postDisplay(msg) {
            try {
                this._displayChannel?.postMessage(msg);
            } catch (e) {
                console.error("Display message failed", e);
            }
        },

        // ── Link a new card ──
        openLinkCard(uid) {
            this.linkUid = uid;
            this.linkSearch = "";
            this.linkResults = [];
            this.linkError = null;
            this.showLinkCard = true;
        },

        closeLinkCard() {
            this.showLinkCard = false;
            this.linkUid = null;
            this._scanner?.forgetLastCode(); // allow an immediate re-tap
        },

        searchLinkUsers() {
            clearTimeout(this.linkSearchTimeout);
            if (this.linkSearch.length < 2) {
                this.linkResults = [];
                return;
            }
            this.linkSearchTimeout = setTimeout(async () => {
                try {
                    const res = await axios.get("/api/users/search", {
                        headers: this.headers(),
                        params: { q: this.linkSearch },
                    });
                    this.linkResults = res.data.users || [];
                } catch (e) {
                    this.linkResults = [];
                }
            }, 300);
        },

        async linkCardTo(u) {
            if (this.linking) return;
            this.linking = true;
            this.linkError = null;
            try {
                await axios.post(
                    "/api/lounge/rfid/link",
                    { uid: this.linkUid, user_id: u.id },
                    { headers: this.headers() },
                );
                this.closeLinkCard();
                this.showRfidStatus(
                    "success",
                    `Card linked to ${u.name}. Tap again to time in.`,
                );
            } catch (e) {
                this.linkError =
                    e.response?.data?.message || "Couldn't link the card.";
            } finally {
                this.linking = false;
            }
        },
    },
};
</script>
