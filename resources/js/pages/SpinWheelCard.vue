<template>
    <div
        v-if="visible"
        class="bg-white rounded-xl shadow-sm border border-amber-100 p-5 mb-6"
    >
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 bg-amber-50 rounded-full flex items-center justify-center flex-shrink-0"
                >
                    <i class="pi pi-ticket text-amber-500"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">Lucky Spin</p>
                    <p class="text-xs text-gray-400">
                        1 spin for every hour of time bought with cash
                    </p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-amber-600">
                    {{ spinCredits }}
                </p>
                <p class="text-xs text-gray-400">
                    {{ spinCredits === 1 ? "spin" : "spins" }} left
                </p>
            </div>
        </div>

        <div class="flex flex-col items-center">
            <!-- Wheel -->
            <div class="relative w-full max-w-[260px]">
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
                    <g v-for="(s, i) in slices" :key="i">
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
                    You won
                    <strong class="text-lg">{{ result.amount }}</strong> coins!
                    New balance:
                    <strong>₱{{ formatCash(result.new_cash) }}</strong>
                </p>
            </div>

            <p v-if="errorMessage" class="mt-3 text-xs text-red-500">
                {{ errorMessage }}
            </p>

            <!-- Spin button -->
            <button
                @click="spin"
                :disabled="spinning || spinCredits < 1"
                class="mt-5 w-full bg-amber-500 hover:bg-amber-600 disabled:bg-gray-300 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors"
            >
                {{
                    spinning
                        ? "Spinning..."
                        : spinCredits > 0
                          ? `Spin! (${spinCredits} left)`
                          : "No spins left"
                }}
            </button>

            <p
                v-if="spinCredits === 0 && !spinning"
                class="mt-2 text-xs text-gray-400 text-center"
            >
                {{
                    Number(level) === 1
                        ? "Buy time at the counter with cash to earn more spins."
                        : "Watch out for bonus spins during events and promos!"
                }}
            </p>
        </div>
    </div>
</template>

<script>
export default {
    name: "SpinWheelCard",
    props: {
        level: { type: [Number, String], default: 1 },
    },
    emits: ["cash-updated"],
    data() {
        return {
            loading: true,
            enabled: false,
            slices: [], // [{ value }]
            spinCredits: 0,

            rotation: 0,
            spinning: false,
            result: null,
            errorMessage: null,
        };
    },
    computed: {
        // Everyone sees the wheel while it's ON (even with 0 spins)
        visible() {
            return !this.loading && this.enabled && this.slices.length >= 2;
        },
        sliceAngles() {
            const size = 360 / (this.slices.length || 1);
            return this.slices.map((_, i) => ({
                start: i * size,
                end: (i + 1) * size,
                size,
            }));
        },
        rankedAmounts() {
            return [...new Set(this.slices.map((s) => s.value))].sort(
                (a, b) => b - a,
            );
        },
    },
    methods: {
        async fetchWheel() {
            this.loading = true;
            try {
                // Child components start before the Dashboard sets the token,
                // so on a hard refresh we attach it ourselves.
                const token = localStorage.getItem("auth-token");
                if (token) {
                    axios.defaults.headers.common["Authorization"] =
                        `Bearer ${token}`;
                }

                const res = await axios.get("/api/coins/my-wheel");
                if (res.data.success) {
                    this.enabled = res.data.enabled;
                    this.slices = res.data.slices || [];
                    this.spinCredits = res.data.spin_credits || 0;
                }
            } catch (error) {
                console.error("Failed to load wheel:", error);
                this.enabled = false;
            } finally {
                this.loading = false;
            }
        },

        // Server picks the prize; the wheel just animates to it
        async spin() {
            if (this.spinning || this.spinCredits < 1) return;

            this.spinning = true;
            this.result = null;
            this.errorMessage = null;

            try {
                const res = await axios.post("/api/coins/spin");
                if (!res.data.success) throw new Error();

                // If the admin changed the wheel since this page loaded, redraw first
                if (
                    JSON.stringify(res.data.slices) !==
                    JSON.stringify(this.slices)
                ) {
                    this.slices = res.data.slices;
                }

                this.spinCredits = res.data.spin_credits;

                // Land at a random spot inside the winning slice (not dead center)
                const { start, size } = this.sliceAngles[res.data.slice_index];
                const point = start + size * (0.2 + Math.random() * 0.6);
                const target = (360 - point) % 360;
                const base = this.rotation - (this.rotation % 360);
                this.rotation = base + 360 * 6 + target;

                setTimeout(() => {
                    this.result = res.data;
                    this.spinning = false;
                    this.$emit("cash-updated", res.data.new_cash);
                }, 4200);
            } catch (error) {
                this.errorMessage =
                    error.response?.data?.message ||
                    "Spin failed. Please try again.";
                this.spinning = false;

                if (error.response?.status === 403) this.enabled = false; // wheel closed
                if (error.response?.status === 422) this.spinCredits = 0; // out of spins
            }
        },

        // ── Drawing (same colors as the admin preview) ──
        tierOf(value) {
            const ranked = this.rankedAmounts;
            if (ranked.length >= 2 && value === ranked[0]) return "top";
            if (ranked.length >= 3 && value === ranked[1]) return "second";
            return "common";
        },
        sliceColor(i) {
            const tier = this.tierOf(this.slices[i].value);
            if (tier === "top") return "#f59e0b"; // gold
            if (tier === "second") return "#6366f1"; // indigo
            const commonsBefore = this.slices
                .slice(0, i)
                .filter((s) => this.tierOf(s.value) === "common").length;
            return commonsBefore % 2 === 0 ? "#c7d2fe" : "#e0e7ff";
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

        formatCash(cash) {
            return parseFloat(cash || 0).toFixed(2);
        },
    },
    mounted() {
        this.fetchWheel();
    },
};
</script>
