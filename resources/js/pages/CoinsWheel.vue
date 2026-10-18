<template>
    <Layout>
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">Coins</h2>
                <p class="text-sm text-gray-500">
                    Select a member and spin. Winnings go straight to their
                    balance.
                </p>
            </div>

            <!-- Search -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6"
            >
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Search Member
                </label>
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <i
                            class="pi pi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"
                        ></i>
                        <input
                            v-model="search"
                            @input="onSearchInput"
                            :disabled="spinning"
                            type="text"
                            placeholder="Search by name or username..."
                            class="w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 disabled:bg-gray-50"
                        />
                    </div>
                    <button
                        @click="openScanner"
                        :disabled="spinning"
                        class="w-12 h-[42px] flex items-center justify-center rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 disabled:opacity-50 transition-colors"
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
                            <p class="text-sm font-medium text-gray-800">
                                {{ u.name }}
                            </p>
                            <p class="text-xs text-gray-400">
                                @{{ u.username }} · Level {{ u.level }}
                            </p>
                        </div>
                        <span
                            class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-50 text-green-600"
                        >
                            ₱{{ formatCash(u.cash) }}
                        </span>
                    </button>
                </div>

                <div
                    v-else-if="search.length > 0 && !searching"
                    class="text-center py-4 text-gray-400 text-sm"
                >
                    No members found.
                </div>
            </div>

            <!-- Selected member -->
            <div
                v-if="selectedUser"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6 flex items-center justify-between"
            >
                <div>
                    <p class="font-semibold text-gray-800">
                        {{ selectedUser.name }}
                    </p>
                    <p class="text-xs text-gray-400">
                        @{{ selectedUser.username }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">Current Coins</p>
                    <p class="text-lg font-bold text-green-600">
                        ₱{{ formatCash(selectedUser.cash) }}
                    </p>
                </div>
            </div>

            <!-- Wheel -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col items-center"
            >
                <div class="relative w-full max-w-xs">
                    <!-- Pointer -->
                    <div
                        class="absolute left-1/2 -translate-x-1/2 -top-1 z-10 w-0 h-0 border-l-[12px] border-r-[12px] border-t-[22px] border-l-transparent border-r-transparent border-t-red-500 drop-shadow"
                    ></div>

                    <svg
                        viewBox="0 0 300 300"
                        class="w-full"
                        :style="{
                            transform: `rotate(${rotation}deg)`,
                            transition:
                                'transform 4s cubic-bezier(0.17, 0.67, 0.12, 0.99)',
                        }"
                    >
                        <g v-for="(value, i) in slices" :key="i">
                            <path
                                :d="slicePath(i)"
                                :fill="sliceColor(value, i)"
                                stroke="#ffffff"
                                stroke-width="1.5"
                            />
                            <text
                                :x="labelPos(i).x"
                                :y="labelPos(i).y"
                                :transform="`rotate(${labelPos(i).angle}, ${labelPos(i).x}, ${labelPos(i).y})`"
                                text-anchor="middle"
                                dominant-baseline="middle"
                                font-size="14"
                                font-weight="700"
                                :fill="value === 5 ? '#3730a3' : '#ffffff'"
                            >
                                {{ value }}
                            </text>
                        </g>
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

                <!-- Result -->
                <div
                    v-if="result"
                    class="mt-5 w-full bg-green-50 border border-green-200 rounded-lg p-3 text-center"
                >
                    <p class="text-sm text-green-700">
                        <strong>{{ result.member.name }}</strong> won
                        <strong class="text-lg">{{ result.amount }}</strong>
                        coins! New balance:
                        <strong>₱{{ formatCash(result.new_cash) }}</strong>
                    </p>
                </div>

                <p v-if="errorMessage" class="mt-3 text-xs text-red-500">
                    {{ errorMessage }}
                </p>

                <button
                    @click="spin"
                    :disabled="!selectedUser || spinning || !slices.length"
                    class="mt-5 w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white font-medium py-2.5 rounded-lg text-sm transition-colors"
                >
                    {{
                        spinning
                            ? "Spinning..."
                            : selectedUser
                              ? `Spin for ${selectedUser.name}`
                              : "Select a member first"
                    }}
                </button>
            </div>

            <!-- Spin History -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mt-6"
            >
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-baseline gap-3">
                        <h3 class="font-semibold text-gray-700">
                            Spin History
                        </h3>
                        <span v-if="totalSpins > 0" class="text-sm">
                            <span class="text-gray-400"
                                >{{ totalSpins }} spins:</span
                            >
                            <span class="font-bold text-green-600">
                                {{ totalCoins }} coins
                            </span>
                        </span>
                    </div>
                    <button
                        @click="fetchHistory"
                        class="text-xs text-indigo-600 hover:underline"
                    >
                        <i class="pi pi-refresh mr-1"></i> Refresh
                    </button>
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
                    No spins yet.
                </div>

                <div v-else class="overflow-x-auto max-h-96 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="text-left text-gray-400 border-b border-gray-100"
                            >
                                <th class="py-2 font-medium">Member</th>
                                <th class="py-2 font-medium">Won</th>
                                <th class="py-2 font-medium">New Balance</th>
                                <th class="py-2 font-medium">By</th>
                                <th class="py-2 font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(s, i) in history"
                                :key="i"
                                class="border-b border-gray-50"
                            >
                                <td class="py-2 text-gray-700">
                                    {{ s.member_name }}
                                </td>
                                <td class="py-2">
                                    <span
                                        class="text-xs font-semibold px-2 py-0.5 rounded-full"
                                        :class="
                                            s.amount === 15
                                                ? 'bg-amber-50 text-amber-600'
                                                : s.amount === 10
                                                  ? 'bg-indigo-50 text-indigo-600'
                                                  : 'bg-gray-50 text-gray-600'
                                        "
                                    >
                                        +{{ s.amount }}
                                    </span>
                                </td>
                                <td class="py-2 text-gray-700">
                                    ₱{{ formatCash(s.new_cash) }}
                                </td>
                                <td class="py-2 text-gray-500">
                                    {{ s.admin_name }}
                                </td>
                                <td class="py-2 text-gray-400 text-xs">
                                    {{ formatDateTime(s.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
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
            search: "",
            searching: false,
            results: [],
            searchTimeout: null,

            selectedUser: null,

            slices: [],
            rotation: 0,
            spinning: false,
            result: null,
            errorMessage: null,

            history: [],
            loadingHistory: false,
            totalCoins: 0,
            totalSpins: 0,

            showScanner: false,
            scanError: null,
            scanBusy: false,
        };
    },
    methods: {
        async fetchWheel() {
            try {
                const response = await axios.get("/api/admin/coins/wheel");
                if (response.data.success) {
                    this.slices = response.data.slices;
                }
            } catch (error) {
                console.error("Failed to load wheel:", error);
                this.errorMessage = "Failed to load wheel.";
            }
        },

        onSearchInput() {
            clearTimeout(this.searchTimeout);

            if (this.search.trim().length === 0) {
                this.results = [];
                return;
            }

            this.searchTimeout = setTimeout(() => {
                this.doSearch();
            }, 300);
        },

        async doSearch() {
            this.searching = true;
            try {
                const response = await axios.get("/api/admin/coins/search", {
                    params: { q: this.search },
                });
                if (response.data.success) {
                    this.results = response.data.users;
                }
            } catch (error) {
                console.error("Search failed:", error);
                this.results = [];
            } finally {
                this.searching = false;
            }
        },

        selectUser(u) {
            this.selectedUser = u;
            this.result = null;
            this.errorMessage = null;
            this.results = [];
            this.search = "";
        },

        // ── Wheel drawing ──
        slicePath(i) {
            const n = this.slices.length;
            const cx = 150,
                cy = 150,
                r = 145;
            const a0 = ((i * 360) / n) * (Math.PI / 180);
            const a1 = (((i + 1) * 360) / n) * (Math.PI / 180);
            const x0 = cx + r * Math.sin(a0);
            const y0 = cy - r * Math.cos(a0);
            const x1 = cx + r * Math.sin(a1);
            const y1 = cy - r * Math.cos(a1);
            return `M ${cx} ${cy} L ${x0} ${y0} A ${r} ${r} 0 0 1 ${x1} ${y1} Z`;
        },

        labelPos(i) {
            const n = this.slices.length;
            const angle = ((i + 0.5) * 360) / n;
            const rad = angle * (Math.PI / 180);
            const r = 115;
            return {
                x: 150 + r * Math.sin(rad),
                y: 150 - r * Math.cos(rad),
                angle,
            };
        },

        sliceColor(value, i) {
            if (value === 15) return "#f59e0b";
            if (value === 10) return "#6366f1";
            return i % 2 === 0 ? "#c7d2fe" : "#e0e7ff";
        },

        // ── Spin: server picks the slice, wheel just lands on it ──
        async spin() {
            if (!this.selectedUser || this.spinning) return;

            this.spinning = true;
            this.result = null;
            this.errorMessage = null;

            try {
                const response = await axios.post("/api/admin/coins/spin", {
                    user_id: this.selectedUser.id,
                });

                if (!response.data.success) throw new Error();

                const n = this.slices.length;
                const index = response.data.slice_index;
                const target = (360 - ((index + 0.5) * 360) / n) % 360;
                const base = this.rotation - (this.rotation % 360);
                this.rotation = base + 360 * 6 + target;

                setTimeout(() => {
                    this.result = response.data;
                    this.selectedUser.cash = response.data.new_cash;
                    this.spinning = false;
                    this.fetchHistory();

                    this.$toast?.add({
                        severity: "success",
                        summary: "Coins added",
                        detail: `${response.data.member.name} won ${response.data.amount} coins!`,
                        life: 3000,
                    });
                }, 4200);
            } catch (error) {
                this.errorMessage =
                    error.response?.data?.message ||
                    "Spin failed. Please try again.";
                this.spinning = false;
            }
        },

        // ── QR scan (same levelup://user/123 format as mobile) ──
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
            if (this.scanBusy) return; // ignore repeat frames
            this.scanBusy = true;

            const match = text.match(/user\/(\d+)/);
            if (!match) {
                this.scanError = "Not a member code. Try again.";
                this.scanBusy = false;
                return;
            }

            try {
                const response = await axios.get(
                    `/api/admin/coins/member/${match[1]}`,
                );
                if (response.data.success) {
                    await this.closeScanner();
                    this.selectUser(response.data.user);
                }
            } catch (error) {
                this.scanError = "Member not found. Try again.";
            } finally {
                this.scanBusy = false;
            }
        },

        async fetchHistory() {
            this.loadingHistory = true;
            try {
                const response = await axios.get("/api/admin/coins/history");
                if (response.data.success) {
                    this.history = response.data.spins;
                    this.totalCoins = response.data.total_coins;
                    this.totalSpins = response.data.total_spins;
                }
            } catch (error) {
                console.error("Failed to fetch spin history:", error);
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
        this.fetchWheel();
        this.fetchHistory();
    },
    beforeUnmount() {
        this.closeScanner(); // release the camera if admin leaves mid-scan
    },
};
</script>
