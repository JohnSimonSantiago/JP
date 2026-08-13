<template>
    <div class="flex flex-wrap items-center gap-2">
        <select
            v-model="mode"
            @change="onModeChange"
            class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
        >
            <option value="today">Today</option>
            <option value="week">This week</option>
            <option value="month">This month</option>
            <option value="pickMonth">Pick a month</option>
            <option value="pickDay">Pick a day</option>
            <option value="custom">Date range</option>
            <option value="all">All time</option>
        </select>

        <!-- Pick a specific month -->
        <input
            v-if="mode === 'pickMonth'"
            v-model="monthValue"
            type="month"
            class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
            @change="applyMonth"
        />

        <!-- Pick a single day -->
        <input
            v-if="mode === 'pickDay'"
            v-model="dayValue"
            type="date"
            class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
            @change="applyDay"
        />

        <!-- Custom range -->
        <template v-if="mode === 'custom'">
            <input
                v-model="fromDate"
                type="date"
                class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                @change="emitRange"
            />
            <span class="text-gray-400 text-sm">to</span>
            <input
                v-model="toDate"
                type="date"
                class="text-sm border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
                @change="emitRange"
            />
        </template>

        <span class="text-xs text-gray-400 ml-1">{{ label }}</span>
    </div>
</template>

<script>
export default {
    name: "DateRangePicker",
    emits: ["change"],
    data() {
        return {
            mode: "today",
            fromDate: null,
            toDate: null,
            monthValue: null, // "YYYY-MM"
            dayValue: null, // "YYYY-MM-DD"
            todayDate: null,
        };
    },
    computed: {
        label() {
            if (this.mode === "all") return "All time";
            if (this.mode === "today") return "Today";
            if (this.mode === "week") return "This week";
            if (this.mode === "month") return "This month";
            if (this.mode === "pickMonth") return "Selected month";
            if (this.mode === "pickDay")
                return this.fromDate === this.todayDate ? "Today" : "That day";
            return "Range";
        },
    },
    methods: {
        localDate(d) {
            const off = d.getTimezoneOffset() * 60000;
            return new Date(d.getTime() - off).toISOString().split("T")[0];
        },
        emit() {
            this.$emit("change", { from: this.fromDate, to: this.toDate });
        },
        emitRange() {
            if (this.fromDate && this.toDate) this.emit();
        },
        onModeChange() {
            const now = new Date();
            if (this.mode === "all") {
                this.fromDate = null;
                this.toDate = null;
                this.emit();
                return;
            }
            if (this.mode === "today") {
                this.fromDate = this.localDate(now);
                this.toDate = this.localDate(now);
                this.emit();
            } else if (this.mode === "week") {
                const day = now.getDay();
                const diff = day === 0 ? 6 : day - 1; // Monday start
                const monday = new Date(now);
                monday.setDate(now.getDate() - diff);
                this.fromDate = this.localDate(monday);
                this.toDate = this.localDate(now);
                this.emit();
            } else if (this.mode === "month") {
                const first = new Date(now.getFullYear(), now.getMonth(), 1);
                this.fromDate = this.localDate(first);
                this.toDate = this.localDate(now);
                this.emit();
            }
            // pickMonth / pickDay / custom wait for user input
        },
        applyMonth() {
            if (!this.monthValue) return;
            const [y, m] = this.monthValue.split("-").map(Number);
            const first = new Date(y, m - 1, 1);
            const last = new Date(y, m, 0); // day 0 of next month = last day
            this.fromDate = this.localDate(first);
            this.toDate = this.localDate(last);
            this.emit();
        },
        applyDay() {
            if (!this.dayValue) return;
            this.fromDate = this.dayValue;
            this.toDate = this.dayValue;
            this.emit();
        },
    },
    mounted() {
        const today = this.localDate(new Date());
        this.todayDate = today;
        this.fromDate = today;
        this.toDate = today;
        this.emit(); // fire once so parent loads "today" on open
    },
};
</script>
