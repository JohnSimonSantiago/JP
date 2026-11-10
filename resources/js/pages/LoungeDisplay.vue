<template>
    <!-- Customer-facing screen for the second monitor. No <Layout>, so no menus. -->
    <div
        class="fixed inset-0 flex flex-col items-center justify-center text-center select-none px-8 transition-colors duration-500"
        :class="[bgClass, isFullscreen ? 'cursor-none' : '']"
        @click="goFullscreen"
    >
        <!-- Idle: logo + clock -->
        <template v-if="!msg">
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
                Tap your card to time in or out
            </div>
            <div
                v-if="!isFullscreen"
                class="absolute bottom-6 text-gray-600 text-base"
            >
                Click anywhere for fullscreen
            </div>
        </template>

        <!-- Unknown card -->
        <template v-else-if="msg.kind === 'unknown'">
            <i
                class="text-white text-8xl mb-10"
                :class="msg.source === 'qr' ? 'pi pi-qrcode' : 'pi pi-id-card'"
            ></i>
            <div class="text-white text-7xl font-bold">
                {{
                    msg.source === "qr"
                        ? "QR code not recognised"
                        : "Card not registered"
                }}
            </div>
            <div class="text-white/80 text-4xl mt-6">
                Please see the counter
            </div>
        </template>

        <!-- Time in -->
        <template v-else-if="msg.kind === 'in'">
            <img
                src="/MainIcon.png"
                alt=""
                class="w-28 h-28 object-contain mb-8 opacity-90"
            />
            <div class="text-white text-8xl font-bold">
                Welcome, {{ msg.name }}!
            </div>
            <div class="text-white/90 text-5xl mt-8">
                <template v-if="msg.mode === 'free'">Free access</template>
                <template v-else-if="msg.mode === 'consumable'">
                    Balance left: {{ formatMinutes(msg.balance) }}
                </template>
                <template v-else>Pay by hour</template>
            </div>
        </template>

        <!-- Time out -->
        <template v-else-if="msg.kind === 'out'">
            <div class="text-white text-8xl font-bold">
                Goodbye, {{ msg.name }}!
            </div>
            <div v-if="msg.used" class="text-white/90 text-5xl mt-8">
                Time used: {{ msg.used }}
            </div>
            <div
                v-if="msg.mode === 'consumable'"
                class="text-white text-5xl mt-6 font-semibold"
            >
                Balance left: {{ formatMinutes(msg.balance) }}
            </div>
            <div
                v-if="msg.mode === 'hourly' && msg.total != null"
                class="text-white text-5xl mt-6 font-semibold"
            >
                Total: ₱{{ Number(msg.total).toFixed(2) }}
            </div>
            <div
                v-if="isNegative"
                class="text-white text-4xl mt-10 bg-black/20 rounded-2xl px-10 py-5"
            >
                Please buy time at the counter
            </div>
        </template>
    </div>
</template>

<script>
// Channel shared with LoungeSessionsDashboard.vue. Works between tabs/windows
// of the same Chrome profile on the same site; nothing goes through the server.
const CHANNEL = "level-lounge-display";
const SHOW_SECONDS = 8;

export default {
    name: "LoungeDisplay",

    data() {
        return {
            msg: null, // { kind: 'in' | 'out' | 'unknown', name, mode, balance, used, total }
            now: new Date(),
            isFullscreen: !!document.fullscreenElement,
        };
    },

    computed: {
        isNegative() {
            return (
                this.msg?.kind === "out" &&
                this.msg.mode === "consumable" &&
                this.msg.balance < 0
            );
        },
        bgClass() {
            if (!this.msg) return "bg-gray-950";
            if (this.msg.kind === "unknown") return "bg-amber-600";
            if (this.isNegative) return "bg-red-700";
            if (this.msg.kind === "in") return "bg-green-700";
            return "bg-indigo-800"; // goodbye
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

    mounted() {
        document.title = "Level Lounge — Display";
        this._clock = setInterval(() => (this.now = new Date()), 1000);
        this._onFs = () => (this.isFullscreen = !!document.fullscreenElement);
        document.addEventListener("fullscreenchange", this._onFs);

        if ("BroadcastChannel" in window) {
            this._channel = new BroadcastChannel(CHANNEL);
            this._channel.onmessage = (e) => this.show(e.data);
        }
    },

    beforeUnmount() {
        clearInterval(this._clock);
        clearTimeout(this._hide);
        document.removeEventListener("fullscreenchange", this._onFs);
        this._channel?.close();
    },

    methods: {
        show(data) {
            if (!data || !data.kind) return;
            this.msg = data;
            clearTimeout(this._hide); // a new tap restarts the 8 seconds
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
