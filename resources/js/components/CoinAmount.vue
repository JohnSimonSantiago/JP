<template>
    <!-- Wallet amount: [coin] 150 Coins. Use :label="false" for just [coin] 150. -->
    <span class="inline-flex items-center gap-1 whitespace-nowrap">
        <CoinIcon />
        <span>{{ formatted }}</span>
        <span v-if="label">{{ unit }}</span>
    </span>
</template>

<script>
import CoinIcon from "@/components/CoinIcon.vue";
import { formatCoins, coinsLabel } from "@/utils/coins";

export default {
    name: "CoinAmount",
    components: { CoinIcon },
    props: {
        value: { type: [Number, String], default: 0 },
        label: { type: Boolean, default: true },
    },
    computed: {
        formatted() {
            return formatCoins(this.value);
        },
        unit() {
            return coinsLabel(this.value);
        },
    },
};
</script>
