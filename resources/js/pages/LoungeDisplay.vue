<template>
    <!-- Customer-facing kiosk screen. No <Layout>, so no menus and no staff controls.
         Works on its own with a kiosk token (paired once by staff); /lounge doesn't
         need to be open. Scans go to POST /lounge/scan through the shared scanner. -->
    <div
        class="fixed inset-0 flex flex-col items-center justify-center text-center select-none px-8 transition-colors duration-500"
        :class="[bgClass, isFullscreen && mode === 'kiosk' ? 'cursor-none' : '']"
        @click="goFullscreen"
    >
        <!-- Hidden QR scanner input (T27H types the QR + Enter). Kiosk mode only. -->
        <input
            v-if="mode === 'kiosk'"
            ref="scanInput"
            type="text"
            autocomplete="off"
            autocapitalize="off"
            spellcheck="false"
            aria-label="QR scanner input"
            class="absolute opacity-0 w-px h-px -left-[9999px]"
        />

        <!-- ── Checking the kiosk token ── -->
        <template v-if="mode === 'checking'">
            <i class="pi pi-spin pi-spinner text-white/60 text-5xl"></i>
        </template>

        <!-- ── Not paired ── -->
        <template v-else-if="mode === 'unpaired'">
            <!-- Staff logged in on this screen: set it up -->
            <div
                v-if="staffLoggedIn"
                class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-8 text-left cursor-default"
                @click.stop
            >
                <template v-if="setupStep === 'pair'">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        Set up this screen as a kiosk
                    </h2>
                    <p class="text-sm text-gray-500 mb-6">
                        Members will scan their QR or tap their card here to time in
                        and out. After setup this screen only needs its kiosk key, so
                        you can log out of staff. It can't open bills, buy time or
                        change balances.
                    </p>
                    <p v-if="setupError" class="text-sm text-red-600 mb-4">
                        {{ setupError }}
                    </p>
                    <button
                        @click="pairKiosk"
                        :disabled="setupBusy"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-lg disabled:opacity-50"
                    >
                        {{ setupBusy ? "Setting up..." : "Set up this screen as a kiosk" }}
                    </button>
                </template>

                <template v-else>
                    <h2 class="text-2xl font-bold text-green-700 mb-2">
                        <i class="pi pi-check-circle mr-1"></i> Kiosk is set up
                    </h2>
                    <p class="text-sm text-gray-500 mb-5">
                        Two last steps while you're here:
                    </p>

                    <div class="border border-gray-200 rounded-lg p-4 mb-3">
                        <p class="text-sm font-medium text-gray-700 mb-1">
                            1. RFID reader (optional)
                        </p>
                        <p class="text-xs text-gray-500 mb-3">
                            Plug in the ESP32, then pick it (COM5). This screen remembers
                            it and reconnects by itself after that.
                        </p>
                        <button
                            v-if="!scannerState.rfidConnected"
                            @click="connectRfidForSetup"
                            class="border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg"
                        >
                            <i class="pi pi-id-card mr-1"></i> Connect RFID reader
                        </button>
                        <p v-else class="text-sm text-green-600 font-medium">
                            <i class="pi pi-check mr-1"></i> RFID reader connected
                        </p>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4 mb-5">
                        <p class="text-sm font-medium text-gray-700 mb-1">
                            2. Log out of staff on this screen
                        </p>
                        <p class="text-xs text-gray-500">
                            Recommended, so nobody at the kiosk can use your staff login.
                        </p>
                    </div>

                    <button
                        @click="finishSetup(true)"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-lg"
                    >
                        Log out staff and start the kiosk
                    </button>
                    <button
                        @click="finishSetup(false)"
                        class="w-full mt-2 text-sm text-gray-400 hover:text-gray-600"
                    >
                        Start the kiosk and stay logged in
                    </button>
                </template>
            </div>

            <!-- Nobody logged in: just say so -->
            <template v-else>
                <img
                    src="/MainIcon.png"
                    alt="Level Lounge"
                    class="w-40 h-40 object-contain mb-10 opacity-60"
                />
                <div class="text-white text-6xl font-bold">Kiosk not paired</div>
                <div class="text-white/70 text-4xl mt-6">Please see staff</div>
                <button
                    @click.stop="goStaffLogin"
                    class="absolute bottom-6 text-gray-500 hover:text-gray-300 text-base underline"
                >
                    Staff: log in to set up this screen
                </button>
            </template>
        </template>

        <!-- ── Kiosk: idle ── -->
        <template v-else-if="!msg">
            <img
                src="/MainIcon.png"
                alt="Level Lounge"
                class="w-56 h-56 object-contain mb-10"
            />
            <div class="text-white text-8xl font-bold tabular-nums">
                {{ clock }}
            </div>
            <div class="text-gray-400 text-3xl mt-4">{{ dateText }}</div>
            <div class="text-gray-500 text-2xl mt-16">
                Scan your QR or tap your card to time in or out
            </div>
            <div
                v-if="!isFullscreen"
                class="absolute bottom-6 text-gray-600 text-base"
            >
                Click anywhere for fullscreen
            </div>
        </template>

        <!-- ── Kiosk: would end below zero -> NOT timed out, still checked in ── -->
        <template v-else-if="msg.kind === 'short'">
            <!-- Negative balance at time out: big pulsing ERROR between two warning icons -->
            <div class="flex items-center justify-center gap-8 mb-10">
                <i class="pi pi-exclamation-triangle text-white text-8xl"></i>
                <span
                    class="bg-white text-red-600 text-8xl font-extrabold tracking-widest rounded-2xl px-12 py-4 shadow-xl animate-pulse"
                    >ERROR</span
                >
                <i class="pi pi-exclamation-triangle text-white text-8xl"></i>
            </div>
            <div class="text-white/80 text-5xl font-semibold">{{ msg.name }}</div>
            <div class="text-white text-7xl font-bold mt-4">Not enough time</div>
            <div class="text-white/80 text-4xl mt-10 uppercase tracking-wide">
                Negative balance
            </div>
            <div class="text-white text-9xl font-extrabold tabular-nums mt-2">
                {{ formatMinutes(-msg.short) }}
            </div>
            <div
                class="text-white text-4xl mt-8 bg-black/20 rounded-2xl px-10 py-5 font-semibold"
            >
                Please see staff to time out.
            </div>
        </template>

        <!-- ── Kiosk: please see staff (unknown code, old hourly session, error) ── -->
        <template v-else-if="msg.kind === 'see_staff'">
            <i class="pi pi-user text-white text-8xl mb-10"></i>
            <div class="text-white text-7xl font-bold">Please see staff</div>
            <div v-if="msg.note" class="text-white/80 text-4xl mt-6">
                {{ msg.note }}
            </div>
        </template>

        <!-- ── Kiosk: time out with a negative balance (ERROR screen, red card) ── -->
        <template
            v-else-if="(msg.kind === 'in' || msg.kind === 'out') && isTimeOutError"
        >
            <!-- Negative balance at time out: big pulsing ERROR between two warning icons -->
            <div class="flex items-center justify-center gap-8 mb-10">
                <i class="pi pi-exclamation-triangle text-white text-8xl"></i>
                <span
                    class="bg-white text-red-600 text-8xl font-extrabold tracking-widest rounded-2xl px-12 py-4 shadow-xl animate-pulse"
                    >ERROR</span
                >
                <i class="pi pi-exclamation-triangle text-white text-8xl"></i>
            </div>
            <div class="text-white text-7xl font-bold">
                See you, {{ msg.name }}!
            </div>
            <div class="text-white/80 text-4xl mt-10 uppercase tracking-wide">
                Balance left
            </div>
            <div class="text-white text-9xl font-extrabold tabular-nums mt-2">
                {{ formatMinutes(msg.balance) }}
            </div>
            <div
                class="text-white text-4xl mt-8 bg-black/20 rounded-2xl px-10 py-5 font-semibold"
            >
                Negative balance — please buy time from staff
            </div>
        </template>

        <!-- ── Kiosk: time in / time out (green card) ── -->
        <template v-else-if="msg.kind === 'in' || msg.kind === 'out'">
            <!-- Time in slides in from the left, time out from the right -->
            <transition
                appear
                :name="msg.kind === 'in' ? 'slide-from-left' : 'slide-from-right'"
            >
                <div :key="msgSeq" class="flex flex-col items-center w-full">
                    <!-- TIME IN / TIME OUT label (with its direction icon) -->
                    <span
                        class="inline-flex items-center gap-6 bg-white text-green-700 text-8xl font-extrabold tracking-wider rounded-full px-20 py-6 shadow-xl"
                    >
                        <i
                            class="pi text-7xl"
                            :class="msg.kind === 'in' ? 'pi-sign-in' : 'pi-sign-out'"
                        ></i>
                        {{ msg.kind === "in" ? "TIME IN" : "TIME OUT" }}
                    </span>

                    <div class="text-white text-7xl font-bold mt-8">
                        {{ msg.kind === "in" ? "Welcome" : "See you" }},
                        {{ msg.name }}!
                    </div>

                    <!-- TIME IN: name + remaining balance -->
                    <template v-if="msg.kind === 'in'">
                        <div
                            v-if="msg.mode === 'free'"
                            class="text-white text-8xl font-extrabold mt-10"
                        >
                            Free member
                        </div>
                        <template v-else-if="msg.mode === 'consumable'">
                            <div
                                class="text-white/80 text-4xl mt-10 uppercase tracking-wide"
                            >
                                Balance
                            </div>
                            <!-- Negative: big bold balance on a red badge (card stays green) -->
                            <div
                                v-if="isNegativeBalance"
                                class="inline-flex items-center gap-6 bg-red-600 text-white text-9xl font-extrabold tabular-nums rounded-3xl px-12 py-4 mt-4 shadow-xl"
                            >
                                <i class="pi pi-exclamation-triangle text-7xl"></i>
                                {{ formatMinutes(msg.balance) }}
                            </div>
                            <!-- Zero or positive -->
                            <div
                                v-else
                                class="text-white text-9xl font-extrabold tabular-nums mt-2"
                            >
                                {{ formatMinutes(msg.balance) }}
                            </div>
                            <div
                                v-if="isNegativeBalance"
                                class="text-white text-4xl mt-8 bg-black/25 rounded-2xl px-10 py-5 font-semibold"
                            >
                                Negative balance — please buy time from staff
                            </div>
                        </template>
                        <!-- walk-ins / old hourly sessions: no balance line -->
                    </template>

                    <!-- TIME OUT: small summary list -->
                    <div
                        v-else
                        class="mt-10 w-full max-w-3xl bg-black/15 rounded-3xl px-10 py-4 divide-y divide-white/20"
                    >
                        <div
                            v-if="msg.used"
                            class="flex items-baseline justify-between gap-6 py-4"
                        >
                            <span class="text-white/80 text-4xl">Time used</span>
                            <span class="text-white text-5xl font-bold tabular-nums">{{
                                msg.used
                            }}</span>
                        </div>
                        <div
                            v-if="msg.mode === 'free'"
                            class="flex items-baseline justify-between gap-6 py-4"
                        >
                            <span class="text-white/80 text-4xl">Membership</span>
                            <span class="text-white text-5xl font-bold">Free member</span>
                        </div>
                        <div
                            v-else-if="msg.mode === 'consumable'"
                            class="flex items-baseline justify-between gap-6 py-4"
                        >
                            <span class="text-white/80 text-4xl">Balance left</span>
                            <span
                                class="text-white text-7xl font-extrabold tabular-nums"
                                >{{ formatMinutes(msg.balance) }}</span
                            >
                        </div>
                    </div>
                </div>
            </transition>
        </template>

        <!-- Small status line (no controls): scanner + RFID -->
        <div
            v-if="mode === 'kiosk'"
            class="absolute bottom-4 left-4 flex items-center gap-4 text-xs text-white/40"
        >
            <span class="flex items-center gap-1.5">
                <span
                    class="w-2 h-2 rounded-full"
                    :class="scannerState.scannerFocused ? 'bg-green-400' : 'bg-gray-500'"
                ></span>
                QR scanner
            </span>
            <span v-if="scannerState.rfidSupported" class="flex items-center gap-1.5">
                <span
                    class="w-2 h-2 rounded-full"
                    :class="scannerState.rfidConnected ? 'bg-green-400' : 'bg-gray-500'"
                ></span>
                RFID {{ scannerState.rfidConnected ? "" : "(not connected)" }}
            </span>
            <span v-if="offline" class="text-amber-300">Offline — retrying</span>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { createLoungeScanner } from "@/lounge/scanner";

// /lounge (in the same Chrome) still sends its own scan results here
const CHANNEL = "level-lounge-display";
const SHOW_SECONDS = 8;
const TOKEN_KEY = "kiosk-token"; // survives staff log out (logout only clears auth-token/user)
const HEALTH_CHECK_MS = 60000;

export default {
    name: "LoungeDisplay",

    data() {
        return {
            mode: "checking", // checking | unpaired | kiosk
            setupStep: "pair", // pair | finish
            setupBusy: false,
            setupError: null,
            offline: false,
            scannerState: { rfidSupported: false, rfidConnected: false, scannerFocused: false },

            msg: null, // { kind: 'in' | 'out' | 'short' | 'see_staff', name, mode, balance, used, short, note }
            msgSeq: 0, // +1 per result, so each one slides in fresh
            now: new Date(),
            isFullscreen: !!document.fullscreenElement,
        };
    },

    computed: {
        staffLoggedIn() {
            void this.mode; // re-check after pairing / logging out
            try {
                const user = JSON.parse(localStorage.getItem("user") || "null");
                return !!localStorage.getItem("auth-token") && ["admin", "staff"].includes(user?.role);
            } catch (e) {
                return false;
            }
        },
        // The member's remaining balance on this result, in minutes, or null when
        // there isn't one (free members, walk-ins, "please see staff").
        // "short" = a kiosk time out that would end at this (negative) balance.
        resultBalance() {
            const m = this.msg;
            if (!m) return null;
            if (m.kind === "short") return -m.short;
            if ((m.kind === "in" || m.kind === "out") && m.mode === "consumable") {
                return m.balance ?? null;
            }
            return null;
        },
        // Negative = below 0 (0 itself is fine)
        isNegativeBalance() {
            return this.resultBalance !== null && this.resultBalance < 0;
        },
        // Time out with a negative balance -> the ERROR screen
        isTimeOutError() {
            return (
                this.isNegativeBalance &&
                (this.msg.kind === "out" || this.msg.kind === "short")
            );
        },
        bgClass() {
            if (this.mode !== "kiosk") return "bg-gray-950";
            if (!this.msg) return "bg-gray-950";
            if (this.msg.kind === "see_staff") return "bg-amber-600";
            // time out with a negative balance: whole screen red, white text
            if (this.isTimeOutError) return "bg-red-600";
            // welcome and goodbye (time in stays green even with a negative balance)
            return "bg-green-700";
        },
        clock() {
            return this.now.toLocaleTimeString("en-PH", {
                hour: "numeric",
                minute: "2-digit",
                timeZone: "Asia/Manila",
            });
        },
        dateText() {
            return this.now.toLocaleDateString("en-PH", {
                weekday: "long",
                month: "long",
                day: "numeric",
                timeZone: "Asia/Manila",
            });
        },
    },

    async mounted() {
        document.title = "Level Lounge — Kiosk";
        this._clock = setInterval(() => (this.now = new Date()), 1000);
        this._onFs = () => (this.isFullscreen = !!document.fullscreenElement);
        document.addEventListener("fullscreenchange", this._onFs);

        if ("BroadcastChannel" in window) {
            this._channel = new BroadcastChannel(CHANNEL);
            this._channel.onmessage = (e) => this.showFromDashboard(e.data);
        }

        if (localStorage.getItem(TOKEN_KEY) && (await this.checkToken())) {
            this.startKiosk();
        } else {
            this.mode = "unpaired";
        }
    },

    beforeUnmount() {
        clearInterval(this._clock);
        clearTimeout(this._hide);
        document.removeEventListener("fullscreenchange", this._onFs);
        this._channel?.close();
        this.stopKiosk();
    },

    methods: {
        // ── Kiosk token ──
        // Ask the server whether the kiosk token still works. /lounge/scan with no
        // code is refused by validation (422) only AFTER the token passed: no side effects.
        async checkToken() {
            try {
                await axios.post("/api/lounge/scan", {}, { headers: this.kioskHeaders() });
                return true;
            } catch (e) {
                const status = e.response?.status;
                if (status === 422) {
                    this.offline = false;
                    return true;
                }
                if (status === 401 || status === 403) {
                    localStorage.removeItem(TOKEN_KEY); // revoked or invalid
                    return false;
                }
                this.offline = true; // no answer: keep going, try again later
                return true;
            }
        },

        kioskHeaders() {
            return { Authorization: `Bearer ${localStorage.getItem(TOKEN_KEY)}` };
        },

        startKiosk() {
            this.mode = "kiosk";
            this.msg = null;
            this.$nextTick(() => {
                this.ensureScanner();
                this._scanner.attachInput(this.$refs.scanInput);
            });
            clearInterval(this._health);
            this._health = setInterval(async () => {
                if (!(await this.checkToken())) this.becomeUnpaired();
            }, HEALTH_CHECK_MS);
        },

        stopKiosk() {
            clearInterval(this._health);
            this._scanner?.destroy();
            this._scanner = null;
        },

        becomeUnpaired() {
            localStorage.removeItem(TOKEN_KEY);
            this.stopKiosk();
            this.msg = null;
            this.setupStep = "pair";
            this.mode = "unpaired";
        },

        // One scanner for this screen (RFID reader + QR). This screen owns the reader.
        ensureScanner() {
            if (this._scanner) return;
            this._scanner = createLoungeScanner({
                getToken: () => localStorage.getItem(TOKEN_KEY),
                onResult: (data) => this.showScan(data),
                onError: (message, e) => {
                    const status = e?.response?.status;
                    if (status === 401 || status === 403) {
                        this.becomeUnpaired();
                        return;
                    }
                    this.offline = !e?.response;
                    this.show({ kind: "see_staff", note: "Can't connect right now" });
                },
                onStatus: () => {}, // reader status shows in the small status line
            });
            this.scannerState = this._scanner.state;
            this._scanner.autoConnectRfid();
        },

        // ── Pairing (staff logged in on this screen) ──
        async pairKiosk() {
            this.setupBusy = true;
            this.setupError = null;
            try {
                const res = await axios.post(
                    "/api/lounge/kiosk/pair",
                    {},
                    { headers: { Authorization: `Bearer ${localStorage.getItem("auth-token")}` } },
                );
                localStorage.setItem(TOKEN_KEY, res.data.token);
                this.ensureScanner(); // so the RFID reader can be connected now
                this.setupStep = "finish";
            } catch (e) {
                this.setupError =
                    e.response?.status === 401
                        ? "Your staff login expired. Log in again, then come back here."
                        : e.response?.data?.message || "Couldn't set up the kiosk.";
            } finally {
                this.setupBusy = false;
            }
        },

        connectRfidForSetup() {
            this._scanner?.connectRfid(); // shows Chrome's picker (needs this click)
        },

        async finishSetup(logOutStaff) {
            if (logOutStaff) {
                try {
                    await axios.post(
                        "/api/logout",
                        {},
                        { headers: { Authorization: `Bearer ${localStorage.getItem("auth-token")}` } },
                    );
                } catch (e) {
                    // already logged out: fine
                }
                localStorage.removeItem("auth-token");
                localStorage.removeItem("user");
            }
            this.startKiosk();
        },

        goStaffLogin() {
            this.$router.push("/");
        },

        // ── Showing results ──
        // From this screen's own scan (/lounge/scan answer)
        showScan(data) {
            this.offline = false;
            const name = data.user?.display_name || data.user?.name || "";
            switch (data.result) {
                case "in":
                    this.show({ kind: "in", name, mode: data.mode, balance: data.balance, short: data.short_minutes || 0 });
                    break;
                case "out":
                    this.show({ kind: "out", name, mode: data.mode, used: data.used, balance: data.balance, short: data.short_minutes || 0 });
                    break;
                case "duplicate":
                    break; // same person a second time: keep the current screen
                case "see_staff":
                    if (data.reason === "short") {
                        // would end below zero: NOT timed out, still checked in
                        this.show({ kind: "short", name, short: data.short_minutes, used: data.used, balance: data.balance });
                    } else {
                        this.show({ kind: "see_staff" }); // e.g. an old hourly session
                    }
                    break;
                default:
                    // unknown card / QR / code, old hourly session, anything else
                    this.show({ kind: "see_staff" });
            }
        },

        // From /lounge in the same Chrome (staff scanned there)
        showFromDashboard(data) {
            if (!data?.kind) return;
            if (data.kind === "in" || data.kind === "out") {
                const short = data.mode === "consumable" && data.balance < 0 ? -data.balance : 0;
                this.show({ ...data, short });
            } else if (data.kind === "short") {
                this.show(data);
            } else {
                this.show({ kind: "see_staff" });
            }
        },

        show(msg) {
            this.msg = msg;
            this.msgSeq++;
            clearTimeout(this._hide); // a new scan restarts the 8 seconds
            this._hide = setTimeout(() => (this.msg = null), SHOW_SECONDS * 1000);
        },

        // Browsers only allow fullscreen after a click, so the first click does it
        goFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen?.().catch(() => {});
            }
        },

        formatMinutes(mins) {
            mins = mins ?? 0;
            const sign = mins < 0 ? "-" : "";
            const abs = Math.abs(mins);
            return `${sign}${Math.floor(abs / 60)}h ${abs % 60}m`;
        },
    },
};
</script>

<style scoped>
.slide-from-left-enter-active,
.slide-from-right-enter-active {
    transition:
        transform 0.45s ease-out,
        opacity 0.45s ease-out;
}
.slide-from-left-enter-from {
    transform: translateX(-160px);
    opacity: 0;
}
.slide-from-right-enter-from {
    transform: translateX(160px);
    opacity: 0;
}
@media (prefers-reduced-motion: reduce) {
    .slide-from-left-enter-active,
    .slide-from-right-enter-active {
        transition: none;
    }
}
</style>
