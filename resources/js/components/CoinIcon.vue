<template>
    <!-- The one coin icon for the app wallet ("Coins"). Use this everywhere, never a copy.
         Default: gold coin, sized to the surrounding text (1em).
         outline: line style in the current text color, for menus next to other line icons. -->
    <svg
        viewBox="0 0 24 24"
        :width="size"
        :height="size"
        class="inline-block shrink-0 align-[-0.125em]"
        role="img"
        aria-label="Coins"
    >
        <template v-if="outline">
            <circle cx="12" cy="12" r="9.5" fill="none" stroke="currentColor" stroke-width="2" />
            <circle cx="12" cy="12" r="6.5" fill="none" stroke="currentColor" stroke-width="1.2" />
            <path :d="cPath" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
        </template>
        <template v-else>
            <circle cx="12" cy="12" r="11" fill="#F5B301" />
            <circle cx="12" cy="12" r="8.2" fill="#FFC93C" stroke="#C98A00" stroke-width="1.2" />
            <path :d="cPath" fill="none" stroke="#8A5A00" stroke-width="2.4" stroke-linecap="round" />
        </template>
    </svg>
</template>

<script>
export default {
    name: "CoinIcon",
    props: {
        outline: { type: Boolean, default: false },
        size: { type: String, default: "1em" },
    },
    data() {
        // A "C" drawn as an arc, so it looks the same on every device (no font needed)
        return { cPath: "M15.2 9.4 A4.2 4.2 0 1 0 15.2 14.6" };
    },
};
</script>
