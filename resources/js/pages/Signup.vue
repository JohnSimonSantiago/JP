<template>
    <section class="flex bg-gray-50 dark:bg-gray-900">
        <div
            class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0"
        >
            <a
                href="#"
                class="flex items-center mb-6 text-2xl font-semibold text-gray-900 dark:text-white gap-1"
            >
                <img
                    src="../../../public/runnrs.jpg"
                    alt=""
                    class="w-10 h-10"
                />
                JP
            </a>
            <div
                class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700"
            >
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1
                        class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white"
                    >
                        Create an account
                    </h1>
                    <form
                        class="space-y-4 md:space-y-6"
                        @submit.prevent="signup"
                    >
                        <div>
                            <label
                                for="name"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >
                                Your username
                            </label>
                            <input
                                v-model="name"
                                type="text"
                                name="name"
                                id="name"
                                placeholder="Username"
                                required
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            />
                        </div>
                        <div>
                            <label
                                for="password"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >
                                Password
                            </label>
                            <input
                                v-model="password"
                                type="password"
                                name="password"
                                id="password"
                                placeholder="••••••••"
                                required
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            />
                        </div>
                        <button
                            type="submit"
                            class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center"
                        >
                            Create an account
                        </button>
                        <p
                            class="text-sm font-light text-gray-500 dark:text-gray-400"
                        >
                            Already have an account?
                            <RouterLink
                                to="/"
                                class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                            >
                                Login here
                            </RouterLink>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            name: "",
            password: "",
        };
    },
    methods: {
        signup() {
            axios
                .post("/signup", {
                    name: this.name,
                    password: this.password,
                })
                .then(() => {
                    this.$router.push("/");
                })
                .catch((err) => {
                    console.error("Signup failed", err);
                    alert("Signup failed. Please try again.");
                });
        },
    },
};
</script>
