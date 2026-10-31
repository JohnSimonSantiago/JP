<template>
    <Layout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">Coins Wheel</h2>
                <p class="text-sm text-gray-500">
                    Members earn 1 spin per hour of buy-time paid in cash, then
                    spin on their dashboard. Winnings go to their app balance.
                </p>
            </div>

            <div v-if="loadingSettings" class="text-center py-12 text-gray-400">
                <i class="pi pi-spin pi-spinner text-2xl"></i>
            </div>

            <template v-else>
                <!-- ON / OFF -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold text-gray-800">
                                Wheel is
                                <span
                                    :class="
                                        isEnabled
                                            ? 'text-green-600'
                                            : 'text-gray-400'
                                    "
                                >
                                    {{ isEnabled ? "ON" : "OFF" }}
                                </span>
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{
                                    isEnabled
                                        ? "Members can see the wheel and cash purchases earn spins."
                                        : "Hidden from members, and purchases don't earn spins."
                                }}
                            </p>
                        </div>
                        <button
                            @click="toggleWheel"
                            :disabled="togglingEnabled"
                            class="relative inline-flex h-7 w-12 flex-shrink-0 items-center rounded-full transition-colors disabled:opacity-50"
                            :class="isEnabled ? 'bg-green-500' : 'bg-gray-300'"
                        >
                            <span
                                class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform"
                                :class="
                                    isEnabled
                                        ? 'translate-x-6'
                                        : 'translate-x-1'
                                "
                            ></span>
                        </button>
                    </div>
                    <div
                        class="mt-4 pt-4 border-t border-gray-100 flex items-center gap-2 text-sm"
                    >
                        <i class="pi pi-ticket text-amber-500"></i>
                        <span class="text-gray-500"
                            >Unused spins held by members:</span
                        >
                        <span class="font-bold text-gray-800">{{
                            unusedSpins
                        }}</span>
                    </div>
                </div>

                <!-- Prizes + Preview -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
                    <div
                        class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-100 p-5"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-semibold text-gray-700">
                                Prizes &amp; Odds
                            </h3>
                            <span
                                v-if="isDirty"
                                class="text-xs font-medium text-amber-600"
                                >Unsaved changes</span
                            >
                        </div>
                        <p class="text-xs text-gray-500 mb-4">
                            <strong>Chance</strong> is the real odds.
                            <strong>Slices</strong> is only how many times the
                            amount appears on the wheel.
                        </p>

                        <div
                            class="grid grid-cols-12 gap-2 text-xs font-medium text-gray-400 mb-1 px-1"
                        >
                            <div class="col-span-4">Coins</div>
                            <div class="col-span-4">Chance %</div>
                            <div class="col-span-3">Slices</div>
                            <div class="col-span-1"></div>
                        </div>

                        <div
                            v-for="(row, i) in prizeRows"
                            :key="i"
                            class="grid grid-cols-12 gap-2 mb-2 items-center"
                        >
                            <input
                                v-model.number="row.amount"
                                type="number"
                                min="1"
                                step="1"
                                class="col-span-4 w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            />
                            <input
                                v-model.number="row.chance"
                                type="number"
                                min="0.01"
                                max="100"
                                step="0.01"
                                class="col-span-4 w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            />
                            <input
                                v-model.number="row.slices"
                                type="number"
                                min="1"
                                max="30"
                                step="1"
                                class="col-span-3 w-full border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            />
                            <button
                                @click="removeRow(i)"
                                :disabled="prizeRows.length <= 1"
                                class="col-span-1 text-gray-400 hover:text-red-500 disabled:opacity-30 disabled:hover:text-gray-400"
                                title="Remove"
                            >
                                <i class="pi pi-trash"></i>
                            </button>
                        </div>

                        <button
                            @click="addRow"
                            :disabled="prizeRows.length >= 10"
                            class="text-xs text-indigo-600 hover:underline disabled:opacity-40 mt-1"
                        >
                            <i class="pi pi-plus mr-1"></i> Add prize
                        </button>

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div
                                class="rounded-lg p-3"
                                :class="chanceOk ? 'bg-green-50' : 'bg-red-50'"
                            >
                                <p
                                    class="text-xs"
                                    :class="
                                        chanceOk
                                            ? 'text-green-500'
                                            : 'text-red-400'
                                    "
                                >
                                    Total chance
                                </p>
                                <p
                                    class="text-lg font-bold"
                                    :class="
                                        chanceOk
                                            ? 'text-green-700'
                                            : 'text-red-600'
                                    "
                                >
                                    {{ totalChance }}%
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-400">
                                    Total slices
                                </p>
                                <p class="text-lg font-bold text-gray-700">
                                    {{ totalSlices }} / 40
                                </p>
                            </div>
                        </div>

                        <ul
                            v-if="isDirty && prizeErrors.length"
                            class="mt-3 text-xs text-red-500 space-y-0.5"
                        >
                            <li v-for="e in prizeErrors" :key="e">• {{ e }}</li>
                        </ul>

                        <p
                            v-if="prizeMessage"
                            class="mt-3 text-sm"
                            :class="
                                prizeSuccess ? 'text-green-600' : 'text-red-600'
                            "
                        >
                            {{ prizeMessage }}
                        </p>

                        <div class="flex gap-2 mt-4">
                            <button
                                @click="savePrizes"
                                :disabled="
                                    !prizesValid || !isDirty || savingPrizes
                                "
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white font-medium py-2 rounded-lg text-sm transition-colors"
                            >
                                {{ savingPrizes ? "Saving..." : "Save Prizes" }}
                            </button>
                            <button
                                v-if="isDirty"
                                @click="resetRows"
                                class="px-4 bg-white border border-gray-200 text-gray-600 text-sm rounded-lg hover:bg-gray-50"
                            >
                                Undo
                            </button>
                        </div>
                    </div>

                    <!-- Live preview -->
                    <div
                        class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col items-center"
                    >
                        <h3 class="font-semibold text-gray-700 self-start">
                            Preview
                        </h3>
                        <p class="text-xs text-gray-500 self-start mb-4">
                            What members will see.
                        </p>
                        <div class="relative w-full max-w-xs">
                            <div
                                class="absolute left-1/2 -translate-x-1/2 -top-1 z-10 w-0 h-0 border-l-[12px] border-r-[12px] border-t-[22px] border-l-transparent border-r-transparent border-t-red-500 drop-shadow"
                            ></div>
                            <svg viewBox="0 0 300 300" class="w-full">
                                <template v-if="previewSlices.length >= 2">
                                    <g v-for="(s, i) in previewSlices" :key="i">
                                        <path
                                            :d="slicePath(i)"
                                            :fill="sliceColor(i)"
                                            stroke="#ffffff"
                                            stroke-width="1.5"
                                        />
                                        <text
                                            :x="labelPos(i).x"
                                            :y="labelPos(i).y"
                                            :transform="`rotate(${labelPos(i).rotate}, ${labelPos(i).x}, ${labelPos(i).y})`"
                                            text-anchor="middle"
                                            dominant-baseline="middle"
                                            :font-size="labelPos(i).size"
                                            font-weight="700"
                                            :fill="
                                                tierOf(s.value) === 'common'
                                                    ? '#3730a3'
                                                    : '#ffffff'
                                            "
                                        >
                                            {{ s.value }}
                                        </text>
                                    </g>
                                </template>
                                <circle
                                    v-else
                                    cx="150"
                                    cy="150"
                                    r="145"
                                    fill="#f3f4f6"
                                />
                                <circle
                                    cx="150"
                                    cy="150"
                                    r="22"
                                    fill="#ffffff"
                                    stroke="#e5e7eb"
                                    stroke-width="2"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Give bonus spins -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6"
                >
                    <h3 class="font-semibold text-gray-700">
                        Give Bonus Spins
                    </h3>
                    <p class="text-xs text-gray-500 mb-4">
                        For events, promos or fixing mistakes. Use a negative
                        number to remove spins.
                    </p>

                    <div v-if="!selectedUser">
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <i
                                    class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"
                                ></i>
                                <input
                                    v-model="search"
                                    @input="onSearchInput"
                                    type="text"
                                    placeholder="Search by name or username..."
                                    class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                                />
                            </div>
                            <button
                                @click="openScanner"
                                class="w-12 h-[42px] flex items-center justify-center rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors"
                                title="Scan member QR"
                            >
                                <i class="pi pi-qrcode text-lg"></i>
                            </button>
                        </div>

                        <div
                            v-if="searching"
                            class="text-center py-4 text-gray-400 text-sm"
                        >
                            <i class="pi pi-spin pi-spinner"></i>
                        </div>
                        <div
                            v-else-if="results.length > 0"
                            class="mt-3 space-y-1 max-h-64 overflow-y-auto"
                        >
                            <button
                                v-for="u in results"
                                :key="u.id"
                                @click="selectUser(u)"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-gray-50 text-left transition-colors"
                            >
                                <div>
                                    <p
                                        class="text-sm font-medium text-gray-800"
                                    >
                                        {{ u.name }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        @{{ u.username }} · Level {{ u.level }}
                                    </p>
                                </div>
                                <span
                                    class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-50 text-amber-600"
                                >
                                    <i class="pi pi-ticket mr-1 text-[10px]"></i
                                    >{{ u.spin_credits || 0 }}
                                </span>
                            </button>
                        </div>
                        <div
                            v-else-if="search.length > 0"
                            class="text-center py-4 text-gray-400 text-sm"
                        >
                            No members found.
                        </div>
                    </div>

                    <div v-else>
                        <div
                            class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 mb-4"
                        >
                            <div>
                                <p class="font-semibold text-gray-800">
                                    {{ selectedUser.name }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    @{{ selectedUser.username }}
                                </p>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="text-xs text-gray-400">
                                        Current spins
                                    </p>
                                    <p class="text-lg font-bold text-amber-600">
                                        {{ selectedUser.spin_credits || 0 }}
                                    </p>
                                </div>
                                <button
                                    @click="clearSelected"
                                    class="text-gray-400 hover:text-gray-600"
                                    title="Choose another member"
                                >
                                    <i class="pi pi-times"></i>
                                </button>
                            </div>
                        </div>

                        <label
                            class="block text-sm font-medium text-gray-700 mb-2"
                            >Spins</label
                        >
                        <div class="flex items-center gap-2 mb-3">
                            <button
                                v-for="n in [1, 3, 5]"
                                :key="n"
                                @click="grantSpins = n"
                                class="px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors"
                                :class="
                                    grantSpins === n
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
                                "
                            >
                                +{{ n }}
                            </button>
                            <input
                                v-model.number="grantSpins"
                                type="number"
                                min="-100"
                                max="100"
                                step="1"
                                class="w-24 border border-gray-200 rounded-lg px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                            />
                        </div>

                        <input
                            v-model="grantReason"
                            type="text"
                            maxlength="255"
                            placeholder="Reason (optional), e.g. Halloween promo"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                        />

                        <p v-if="grantError" class="text-xs text-red-500 mb-3">
                            {{ grantError }}
                        </p>

                        <button
                            @click="giveSpins"
                            :disabled="!grantValid || granting"
                            class="w-full text-white font-medium py-2.5 rounded-lg text-sm transition-colors disabled:bg-gray-300"
                            :class="
                                grantSpins < 0
                                    ? 'bg-red-600 hover:bg-red-700'
                                    : 'bg-indigo-600 hover:bg-indigo-700'
                            "
                        >
                            {{ granting ? "Saving..." : grantLabel }}
                        </button>
                    </div>
                </div>

                <!-- History -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-5"
                >
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="flex gap-1 bg-gray-100 p-1 rounded-lg text-sm"
                        >
                            <button
                                @click="historyTab = 'spins'"
                                class="px-3 py-1 rounded-md font-medium transition-all"
                                :class="
                                    historyTab === 'spins'
                                        ? 'bg-white shadow-sm text-indigo-600'
                                        : 'text-gray-500'
                                "
                            >
                                Spins
                            </button>
                            <button
                                @click="historyTab = 'grants'"
                                class="px-3 py-1 rounded-md font-medium transition-all"
                                :class="
                                    historyTab === 'grants'
                                        ? 'bg-white shadow-sm text-indigo-600'
                                        : 'text-gray-500'
                                "
                            >
                                Bonus Spins
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                v-if="historyTab === 'spins' && totalSpins > 0"
                                class="text-sm"
                            >
                                <span class="text-gray-400"
                                    >{{ totalSpins }} spins:</span
                                >
                                <span class="font-bold text-green-600">
                                    {{ totalCoins }} coins</span
                                >
                            </span>
                            <button
                                @click="fetchHistory"
                                class="text-xs text-indigo-600 hover:underline"
                            >
                                <i class="pi pi-refresh mr-1"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <div
                        v-if="loadingHistory"
                        class="text-center py-6 text-gray-400"
                    >
                        <i class="pi pi-spin pi-spinner"></i>
                    </div>

                    <!-- Spins -->
                    <template v-else-if="historyTab === 'spins'">
                        <div
                            v-if="history.length === 0"
                            class="text-center py-6 text-gray-400 text-sm"
                        >
                            No spins yet.
                        </div>
                        <div
                            v-else
                            class="overflow-x-auto max-h-96 overflow-y-auto"
                        >
                            <table class="w-full text-sm">
                                <thead>
                                    <tr
                                        class="text-left text-gray-400 border-b border-gray-100"
                                    >
                                        <th class="py-2 font-medium">Member</th>
                                        <th class="py-2 font-medium">Won</th>
                                        <th class="py-2 font-medium">
                                            New Balance
                                        </th>
                                        <th class="py-2 font-medium">
                                            Spun By
                                        </th>
                                        <th class="py-2 font-medium">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="s in history"
                                        :key="s.id"
                                        class="border-b border-gray-50"
                                    >
                                        <td class="py-2 text-gray-700">
                                            {{ s.member_name }}
                                        </td>
                                        <td class="py-2">
                                            <span
                                                class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                                :class="
                                                    amountChipClass(s.amount)
                                                "
                                            >
                                                +{{ s.amount }}
                                            </span>
                                        </td>
                                        <td class="py-2 text-gray-700">
                                            ₱{{ formatCash(s.new_cash) }}
                                        </td>
                                        <td class="py-2 text-gray-500">
                                            {{ s.admin_name || "Member" }}
                                        </td>
                                        <td class="py-2 text-gray-400 text-xs">
                                            {{ formatDateTime(s.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>

                    <!-- Bonus spins -->
                    <template v-else>
                        <div
                            v-if="grants.length === 0"
                            class="text-center py-6 text-gray-400 text-sm"
                        >
                            No bonus spins given yet.
                        </div>
                        <div
                            v-else
                            class="overflow-x-auto max-h-96 overflow-y-auto"
                        >
                            <table class="w-full text-sm">
                                <thead>
                                    <tr
                                        class="text-left text-gray-400 border-b border-gray-100"
                                    >
                                        <th class="py-2 font-medium">Member</th>
                                        <th class="py-2 font-medium">Spins</th>
                                        <th class="py-2 font-medium">Reason</th>
                                        <th class="py-2 font-medium">By</th>
                                        <th class="py-2 font-medium">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="g in grants"
                                        :key="g.id"
                                        class="border-b border-gray-50"
                                    >
                                        <td class="py-2 text-gray-700">
                                            {{ g.member_name }}
                                        </td>
                                        <td class="py-2">
                                            <span
                                                class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                                :class="
                                                    g.spins > 0
                                                        ? 'bg-green-50 text-green-600'
                                                        : 'bg-red-50 text-red-600'
                                                "
                                            >
                                                {{ g.spins > 0 ? "+" : ""
                                                }}{{ g.spins }}
                                            </span>
                                        </td>
                                        <td class="py-2 text-gray-500">
                                            {{ g.reason || "—" }}
                                        </td>
                                        <td class="py-2 text-gray-500">
                                            {{ g.admin_name }}
                                        </td>
                                        <td class="py-2 text-gray-400 text-xs">
                                            {{ formatDateTime(g.created_at) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- QR Scanner Modal -->
        <div
            v-if="showScanner"
            class="fixed inset-0 z-[60] bg-black flex flex-col items-center justify-center"
        >
            <button
                @click="closeScanner"
                class="absolute top-4 right-4 w-11 h-11 rounded-full bg-black/50 text-white flex items-center justify-center"
            >
                <i class="pi pi-times text-xl"></i>
            </button>

            <div id="coin-qr-reader" class="w-full max-w-sm"></div>

            <p class="text-white text-sm font-semibold mt-5 text-center px-4">
                {{ scanError || "Point at the member's Check-In Code" }}
            </p>
            <p v-if="scanBusy" class="text-indigo-300 text-xs mt-2">
                <i class="pi pi-spin pi-spinner mr-1"></i> Loading member...
            </p>
        </div>
    </Layout>
</template>

<script>
import { Html5Qrcode } from "html5-qrcode";

export default {
    name: "CoinsWheel",
    data() {
        return {
            // Settings
            loadingSettings: true,
            isEnabled: false,
            togglingEnabled: false,
            unusedSpins: 0,

            // Prizes editor
            prizeRows: [], // [{ amount, chance, slices }]
            savedPrizesJson: "[]",
            savingPrizes: false,
            prizeMessage: null,
            prizeSuccess: false,

            // Give spins
            search: "",
            searching: false,
            results: [],
            searchTimeout: null,
            selectedUser: null,
            grantSpins: 1,
            grantReason: "",
            granting: false,
            grantError: null,

            // History
            historyTab: "spins",
            history: [],
            grants: [],
            loadingHistory: false,
            totalCoins: 0,
            totalSpins: 0,

            // QR
            showScanner: false,
            scanError: null,
            scanBusy: false,
        };
    },
    computed: {
        totalChance() {
            const sum = this.prizeRows.reduce(
                (s, r) => s + (Number(r.chance) || 0),
                0,
            );
            return Math.round(sum * 100) / 100;
        },
        chanceOk() {
            return Math.round(this.totalChance * 100) === 10000;
        },
        totalSlices() {
            return this.prizeRows.reduce(
                (s, r) => s + (Number(r.slices) || 0),
                0,
            );
        },
        prizeErrors() {
            const errs = [];
            const rows = this.prizeRows;
            const isWhole = (v) => Number.isInteger(Number(v)) && v !== "";

            if (rows.length === 0) errs.push("Add at least one prize.");
            if (rows.some((r) => !isWhole(r.amount) || Number(r.amount) < 1))
                errs.push(
                    "Each coin amount must be a whole number of 1 or more.",
                );
            if (rows.some((r) => !(Number(r.chance) > 0)))
                errs.push("Each chance must be more than 0%.");
            if (
                rows.some(
                    (r) =>
                        !isWhole(r.slices) ||
                        Number(r.slices) < 1 ||
                        Number(r.slices) > 30,
                )
            )
                errs.push("Slices must be a whole number from 1 to 30.");

            const amounts = rows.map((r) => Number(r.amount));
            if (new Set(amounts).size !== amounts.length)
                errs.push("Each coin amount can only appear once.");
            if (!this.chanceOk)
                errs.push(
                    `Chances must add up to 100% (now ${this.totalChance}%).`,
                );
            if (this.totalSlices < 2 || this.totalSlices > 40)
                errs.push("The wheel needs between 2 and 40 slices.");

            return errs;
        },
        prizesValid() {
            return this.prizeErrors.length === 0;
        },
        isDirty() {
            return this.normalize(this.prizeRows) !== this.savedPrizesJson;
        },

        // Rows good enough to draw (preview updates live while typing)
        drawableRows() {
            return this.prizeRows
                .map((r) => ({
                    amount: Number(r.amount),
                    slices: Number(r.slices),
                }))
                .filter(
                    (r) =>
                        Number.isInteger(r.amount) &&
                        r.amount >= 1 &&
                        Number.isInteger(r.slices) &&
                        r.slices >= 1 &&
                        r.slices <= 30,
                )
                .sort((a, b) => a.amount - b.amount);
        },
        previewSlices() {
            return this.buildSlices(this.drawableRows);
        },
        rankedAmounts() {
            return [...new Set(this.drawableRows.map((r) => r.amount))].sort(
                (a, b) => b - a,
            );
        },
        sliceAngles() {
            const size = 360 / (this.previewSlices.length || 1);
            return this.previewSlices.map((_, i) => ({
                start: i * size,
                end: (i + 1) * size,
                size,
            }));
        },

        grantValid() {
            const n = this.grantSpins;
            return Number.isInteger(n) && n !== 0 && Math.abs(n) <= 100;
        },
        grantLabel() {
            if (!this.selectedUser) return "";
            const n = this.grantSpins;
            if (!this.grantValid) return "Enter a number from -100 to 100";
            return n > 0
                ? `Give ${n} spin(s) to ${this.selectedUser.name}`
                : `Remove ${Math.abs(n)} spin(s) from ${this.selectedUser.name}`;
        },
    },
    methods: {
        notify(severity, detail) {
            if (this.$toast) {
                this.$toast.add({
                    severity,
                    summary: severity === "error" ? "Error" : "Done",
                    detail,
                    life: 3000,
                });
            } else if (severity === "error") {
                alert(detail);
            }
        },

        // ── Settings ──
        async fetchSettings() {
            this.loadingSettings = true;
            try {
                const res = await axios.get("/api/admin/coins/settings");
                if (res.data.success) {
                    this.isEnabled = res.data.is_enabled;
                    this.unusedSpins = res.data.unused_spins;
                    this.setRows(res.data.prizes || []);
                }
            } catch (error) {
                console.error("Failed to load wheel settings:", error);
                this.prizeSuccess = false;
                this.prizeMessage = "Failed to load wheel settings.";
            } finally {
                this.loadingSettings = false;
            }
        },

        async toggleWheel() {
            const next = !this.isEnabled;
            if (
                !next &&
                !confirm(
                    "Turn the wheel OFF? Members won't see it and cash purchases stop earning spins. Spins they already have are kept.",
                )
            ) {
                return;
            }

            this.togglingEnabled = true;
            try {
                const res = await axios.post("/api/admin/coins/toggle", {
                    is_enabled: next,
                });
                if (res.data.success) {
                    this.isEnabled = res.data.is_enabled;
                    this.notify("success", res.data.message);
                }
            } catch (error) {
                this.notify(
                    "error",
                    error.response?.data?.message || "Failed to update wheel.",
                );
            } finally {
                this.togglingEnabled = false;
            }
        },

        // ── Prizes editor ──
        normalize(rows) {
            return JSON.stringify(
                [...rows]
                    .map((r) => ({
                        amount: Number(r.amount) || 0,
                        chance: Number(r.chance) || 0,
                        slices: Number(r.slices) || 0,
                    }))
                    .sort((a, b) => a.amount - b.amount),
            );
        },
        setRows(prizes) {
            this.prizeRows = prizes.map((p) => ({
                amount: Number(p.amount),
                chance: Number(p.chance),
                slices: Number(p.slices),
            }));
            this.savedPrizesJson = this.normalize(this.prizeRows);
        },
        resetRows() {
            this.prizeRows = JSON.parse(this.savedPrizesJson);
            this.prizeMessage = null;
        },
        addRow() {
            this.prizeRows.push({ amount: "", chance: "", slices: 1 });
            this.prizeMessage = null;
        },
        removeRow(i) {
            this.prizeRows.splice(i, 1);
            this.prizeMessage = null;
        },
        async savePrizes() {
            if (!this.prizesValid || this.savingPrizes) return;

            this.savingPrizes = true;
            this.prizeMessage = null;
            try {
                const res = await axios.put("/api/admin/coins/prizes", {
                    prizes: JSON.parse(this.normalize(this.prizeRows)),
                });
                if (res.data.success) {
                    this.setRows(res.data.prizes);
                    this.prizeSuccess = true;
                    this.prizeMessage = res.data.message;
                }
            } catch (error) {
                this.prizeSuccess = false;
                this.prizeMessage =
                    error.response?.data?.message || "Failed to save prizes.";
            } finally {
                this.savingPrizes = false;
            }
        },

        // ── Wheel drawing (same layout rules as the server) ──
        buildSlices(rows) {
            const slots = [];
            rows.forEach((p) => {
                const n = Math.max(1, p.slices);
                for (let k = 0; k < n; k++) {
                    slots.push({ value: p.amount, pos: (k + 0.5) / n, n });
                }
            });
            slots.sort((a, b) => a.pos - b.pos || a.n - b.n);
            return slots.map((s) => ({ value: s.value }));
        },
        tierOf(value) {
            const ranked = this.rankedAmounts;
            if (ranked.length >= 2 && value === ranked[0]) return "top";
            if (ranked.length >= 3 && value === ranked[1]) return "second";
            return "common";
        },
        sliceColor(i) {
            const tier = this.tierOf(this.previewSlices[i].value);
            if (tier === "top") return "#f59e0b"; // gold
            if (tier === "second") return "#6366f1"; // indigo
            const commonsBefore = this.previewSlices
                .slice(0, i)
                .filter((s) => this.tierOf(s.value) === "common").length;
            return commonsBefore % 2 === 0 ? "#c7d2fe" : "#e0e7ff";
        },
        amountChipClass(amount) {
            const tier = this.tierOf(amount);
            if (tier === "top") return "bg-amber-50 text-amber-600";
            if (tier === "second") return "bg-indigo-50 text-indigo-600";
            return "bg-gray-50 text-gray-600";
        },
        slicePath(i) {
            const { start, end, size } = this.sliceAngles[i];
            const cx = 150,
                cy = 150,
                r = 145;
            const largeArc = size > 180 ? 1 : 0;
            const a0 = start * (Math.PI / 180);
            const a1 = end * (Math.PI / 180);
            const x0 = cx + r * Math.sin(a0);
            const y0 = cy - r * Math.cos(a0);
            const x1 = cx + r * Math.sin(a1);
            const y1 = cy - r * Math.cos(a1);
            return `M ${cx} ${cy} L ${x0} ${y0} A ${r} ${r} 0 ${largeArc} 1 ${x1} ${y1} Z`;
        },
        labelPos(i) {
            const { start, end, size } = this.sliceAngles[i];
            const angle = (start + end) / 2;
            const rad = angle * (Math.PI / 180);
            const thin = size < 15;
            const r = thin ? 118 : 110;
            return {
                x: 150 + r * Math.sin(rad),
                y: 150 - r * Math.cos(rad),
                rotate: thin ? angle - 90 : angle,
                size: thin ? 10 : 16,
            };
        },

        // ── Give spins ──
        onSearchInput() {
            clearTimeout(this.searchTimeout);
            if (this.search.trim().length === 0) {
                this.results = [];
                return;
            }
            this.searchTimeout = setTimeout(() => this.doSearch(), 300);
        },
        async doSearch() {
            this.searching = true;
            try {
                const res = await axios.get("/api/admin/coins/search", {
                    params: { q: this.search },
                });
                if (res.data.success) this.results = res.data.users;
            } catch (error) {
                console.error("Search failed:", error);
                this.results = [];
            } finally {
                this.searching = false;
            }
        },
        selectUser(u) {
            this.selectedUser = u;
            this.grantSpins = 1;
            this.grantReason = "";
            this.grantError = null;
            this.results = [];
            this.search = "";
        },
        clearSelected() {
            this.selectedUser = null;
            this.grantError = null;
        },
        async giveSpins() {
            if (!this.grantValid || this.granting) return;

            const n = this.grantSpins;
            if (
                n < 0 &&
                !confirm(
                    `Remove ${Math.abs(n)} spin(s) from ${this.selectedUser.name}?`,
                )
            ) {
                return;
            }

            this.granting = true;
            this.grantError = null;
            try {
                const res = await axios.post("/api/admin/coins/grant", {
                    user_id: this.selectedUser.id,
                    spins: n,
                    reason: this.grantReason.trim() || null,
                });
                if (res.data.success) {
                    this.selectedUser.spin_credits = res.data.spin_credits;
                    this.unusedSpins = res.data.unused_spins;
                    this.grantSpins = 1;
                    this.grantReason = "";
                    this.notify("success", res.data.message);
                    this.historyTab = "grants";
                    this.fetchHistory();
                }
            } catch (error) {
                this.grantError =
                    error.response?.data?.message || "Failed to update spins.";
            } finally {
                this.granting = false;
            }
        },

        // ── QR scan (levelup://user/123 format, same as mobile) ──
        async openScanner() {
            this.scanError = null;
            this.scanBusy = false;
            this.showScanner = true;
            await this.$nextTick();

            try {
                this.qrScanner = new Html5Qrcode("coin-qr-reader");
                await this.qrScanner.start(
                    { facingMode: "environment" },
                    { fps: 10, qrbox: { width: 240, height: 240 } },
                    this.onScanSuccess,
                    () => {}, // ignore "no QR in frame" noise
                );
            } catch (error) {
                console.error("Camera failed:", error);
                this.scanError =
                    "Camera blocked or unavailable. Allow camera access and try again.";
            }
        },
        async closeScanner() {
            try {
                if (this.qrScanner?.isScanning) await this.qrScanner.stop();
                this.qrScanner?.clear();
            } catch {}
            this.qrScanner = null;
            this.showScanner = false;
        },
        async onScanSuccess(text) {
            if (this.scanBusy) return;
            this.scanBusy = true;

            const match = text.match(/user\/(\d+)/);
            if (!match) {
                this.scanError = "Not a member code. Try again.";
                this.scanBusy = false;
                return;
            }

            try {
                const res = await axios.get(
                    `/api/admin/coins/member/${match[1]}`,
                );
                if (res.data.success) {
                    await this.closeScanner();
                    this.selectUser(res.data.user);
                }
            } catch (error) {
                this.scanError = "Member not found. Try again.";
            } finally {
                this.scanBusy = false;
            }
        },

        // ── History ──
        async fetchHistory() {
            this.loadingHistory = true;
            try {
                const res = await axios.get("/api/admin/coins/history");
                if (res.data.success) {
                    this.history = res.data.spins;
                    this.grants = res.data.grants;
                    this.totalCoins = res.data.total_coins;
                    this.totalSpins = res.data.total_spins;
                }
            } catch (error) {
                console.error("Failed to fetch history:", error);
            } finally {
                this.loadingHistory = false;
            }
        },

        formatDateTime(dateString) {
            return new Date(dateString).toLocaleString("en-PH", {
                month: "short",
                day: "numeric",
                hour: "numeric",
                minute: "2-digit",
            });
        },
        formatCash(cash) {
            return parseFloat(cash || 0).toFixed(2);
        },
    },
    mounted() {
        this.fetchSettings();
        this.fetchHistory();
    },
    beforeUnmount() {
        this.closeScanner(); // release the camera if admin leaves mid-scan
    },
};
</script>
