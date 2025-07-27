<template>
    <div class="">
        <div class="flex justify-center">
            <button
                @click="isOpen = true"
                class="bg-green-500 px-4 py-2 rounded-md text-white text-sm hover:bg-green-600"
                type="button"
                :class="{ 'cursor-not-allowed': modalContent.disablebtn }"
            >
                {{ buttonLabel }}
            </button>

            <div
                v-if="isOpen"
                class="fixed inset-0 flex items-center justify-center bg-gray-700 bg-opacity-50 z-50 min-h-screen"
            >
                <div class="p-6 bg-white rounded-md shadow-xl min-w-[400px]">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl">{{ modalContent.title }}</h3>
                    </div>
                    <div class="mt-4">
                        <p class="mb-4 text-sm">
                            {{ modalContent.content }}
                        </p>
                        <slot />
                        <button
                            @click="isOpen = false"
                            class="px-4 py-2 text-green-800 border border-green-600 rounded-md text-sm"
                        >
                            {{ cancelLabel }}
                        </button>
                        <button
                            @click="saveData"
                            class="px-4 py-2 ml-2 text-white bg-green-600 rounded-md text-sm"
                        >
                            {{ saveLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        modalContent: {
            type: Object,
            required: true,
        },
        buttonLabel: {
            type: String,
            default: "Open Modal",
        },
        cancelLabel: {
            type: String,
            default: "Cancel",
        },
        saveLabel: {
            type: String,
            default: "Save",
        },
        saveOption: {
            type: Boolean,
        },
    },
    data() {
        return {
            isOpen: false,
        };
    },
    methods: {
        saveData() {
            this.$emit("save");

            if (this.saveOption) {
                this.isOpen = false;
            }
        },
    },
};
</script>
