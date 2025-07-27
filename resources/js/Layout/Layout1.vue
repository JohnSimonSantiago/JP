<template lang="html">
    <div class="h-screen">
        <!-- Top Row -->
        <div
            class="bg-white-100 z-50 flex items-center border-b border-yellow-400 border-b-2 ps-2.5 pt-0.5 gap-2 fixed bg-white h-16 w-screen over-flow-clip"
        >
            <img src="../../../public/runnrs.jpg" alt="" class="w-10 h-10" />
            <span
                class="pl-11 fixed self-center text-xl font-semibold whitespace-nowrap dark:text-white"
                >CBEA Kitchen Equipment</span
            >
        </div>

        <div>
            <div
                class="w-32 mt-16 fixed bg-white/60 shadow-sm backdrop-blur-sm h-full flex flex-col items-center px-5"
            >
                <div class="flex text-center items-center">
                    <div class="space-y-20 text-center py-8">
                        <div
                            class="hover:bg-gray-200 rounded-md p-2.5 transition-all ease-in duration-100"
                        >
                            <router-link
                                active-class="text-green-700"
                                to="/dashboard"
                                class="text-sm font-medium text-gray-900 dark:text-white"
                            >
                                <p class="">
                                    <i
                                        class="pi pi-th-large"
                                        style="font-size: 1.5rem"
                                    >
                                    </i>
                                </p>
                                Dashboard</router-link
                            >
                        </div>

                        <div
                            class="hover:bg-gray-200 rounded-md p-2.5 transition-all ease-in duration-100"
                        >
                            <router-link
                                active-class="text-green-700"
                                to="/reservations"
                                class="text-sm font-medium text-gray-900 dark:text-white"
                                ><p class="mx-10">
                                    <i
                                        class="pi pi-book"
                                        style="font-size: 1.5rem"
                                    ></i>
                                </p>
                                Reservations</router-link
                            >
                        </div>

                        <div class="flex justify-center items-center">
                            <button
                                @click="logout"
                                class="flex flex-col items-center mb-2 text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-200"
                            >
                                <i
                                    class="pi pi-sign-out"
                                    style="font-size: 1.5rem"
                                ></i>
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="min-h-screen flex flex-col items-center justify-center ml-32"
            >
                <slot />
            </div>
        </div>
    </div>
</template>

<script>
import Button from "primevue/button";
export default {
    components: {
        Button,
    },
    data() {
        return {};
    },
    methods: {
        checkAuth() {
            axios.get("/checkUser").then(({ data }) => {
                if (!data) {
                    this.$router.push("/");
                }
            });
        },
        logout() {
            axios.post("/logout").then(() => {
                this.$router.push("/");
            });
        },
    },
    mounted() {
        this.checkAuth();
    },
};
</script>

<style></style>
