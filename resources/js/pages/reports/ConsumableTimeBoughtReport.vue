<template>
    <div>
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="font-semibold text-gray-800">Top Spenders</h3>
            <DateRangePicker @change="onRangeChange" />
        </div>

        <!-- Controls -->
        <div class="flex items-center gap-2 mb-4 flex-wrap">
            <input
                v-model="search"
                type="text"
                placeholder="Search name…"
                class="flex-1 min-w-[160px] text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300"
            />
            <button
                @click="sortAlphabetical = !sortAlphabetical"
                class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 text-gray-600 hover:bg-gray-50"
            >
                {{ sortAlphabetical ? "Sort: A–Z" : "Sort: Top spend" }}
            </button>
        </div>

        <div v-if="loading" class="text-center py-8 text-gray-400">
            <i class="pi pi-spin pi-spinner text-xl"></i>
        </div>

        <template v-else>
            <div v-if="filtered.length" class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr
                            class="text-left text-gray-400 border-b border-gray-100"
                        >
                            <th class="py-2 pr-2 w-8">#</th>
                            <th class="py-2 pr-4">Name</th>
                            <th class="py-2 pr-4 text-right">Spent</th>
                            <th class="py-2 pr-4 text-right">Hours</th>
                            <th class="py-2 text-right">Purchases</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(u, i) in filtered"
                            :key="u.user_id"
                            class="border-b border-gray-50 hover:bg-gray-50"
                        >
                            <td class="py-2 pr-2 text-gray-400">{{ i + 1 }}</td>
                            <td class="py-2 pr-4">
                                <p class="text-gray-800 font-medium">
                                    {{ u.name }}
                                </p>
                                <p
                                    v-if="u.username"
                                    class="text-xs text-gray-400"
                                >
                                    @{{ u.username }}
                                </p>
                            </td>
                            <td
                                class="py-2 pr-4 text-right font-bold text-green-600"
                            >
                                ₱{{ u.total_spent.toLocaleString() }}
                            </td>
                            <td class="py-2 pr-4 text-right text-indigo-600">
                                {{ formatMinutes(u.total_minutes) }}
                            </td>
                            <td class="py-2 text-right text-gray-500">
                                {{ u.purchase_count }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-center py-8 text-gray-400 text-sm">
                No purchases for this range.
            </div>
        </template>
    </div>
</template>

<script>
import axios from "axios";
import DateRangePicker from "@/components/DateRangePicker.vue";

export default {
    name: "ConsumableTimeBoughtReport",
    components: { DateRangePicker },
    data() {
        return {
            loading: false,
            users: [],
            search: "",
            sortAlphabetical: false,
        };
    },
    computed: {
        filtered() {
            const q = this.search.trim().toLowerCase();
            let list = this.users;
            if (q) {
                list = list.filter(
                    (u) =>
                        u.name?.toLowerCase().includes(q) ||
                        u.username?.toLowerCase().includes(q),
                );
            }
            // copy before sort so we don't mutate source order
            list = [...list];
            if (this.sortAlphabetical) {
                list.sort((a, b) => (a.name || "").localeCompare(b.name || ""));
            } else {
                list.sort((a, b) => b.total_spent - a.total_spent);
            }
            return list;
        },
    },
    methods: {
        formatMinutes(mins) {
            mins = mins || 0;
            const h = Math.floor(mins / 60);
            const m = mins % 60;
            return `${h}h ${m}m`;
        },
        async onRangeChange({ from, to }) {
            this.loading = true;
            try {
                const res = await axios.get(
                    "/api/lounge/consumable/top-spenders",
                    {
                        params: { from, to },
                    },
                );
                if (res.data.success) this.users = res.data.users;
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
