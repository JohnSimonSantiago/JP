<template>
    <section class="flex bg-gray-50 dark:bg-gray-900">
        <div
            class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0"
        >
            <!-- Enhanced Logo Section -->
            <div class="flex flex-col items-center mb-8">
                <div
                    class="flex items-center justify-center w-28 h-28 mb-4 bg-blue-100 dark:bg-blue-900 rounded-full"
                >
                    <img
                        src="../../../public/logo.png"
                        alt="JP Logo"
                        class="w-20 h-20 rounded-full"
                    />
                </div>
                <h1
                    class="text-3xl font-bold text-gray-900 dark:text-white text-center"
                >
                    Welcome to JP
                </h1>
                <p
                    class="text-sm text-gray-600 dark:text-gray-400 mt-2 text-center"
                >
                    Create your account to get started
                </p>
            </div>

            <div
                class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700"
            >
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h2
                        class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white text-center"
                    >
                        Create an account
                    </h2>

                    <!-- Admin Approval Notice -->
                    <div
                        class="bg-blue-50 border border-blue-200 rounded-lg p-4 dark:bg-blue-900/20 dark:border-blue-800"
                    >
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg
                                    class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p
                                    class="text-sm text-blue-700 dark:text-blue-300"
                                >
                                    <strong>Please note:</strong> After creating
                                    your account, please wait for admin approval
                                    before you can access the system.
                                </p>
                            </div>
                        </div>
                    </div>

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
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            />
                        </div>
                        <div>
                            <label
                                for="password"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >
                                Password
                            </label>
                            <div class="relative">
                                <input
                                    v-model="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    name="password"
                                    id="password"
                                    placeholder="••••••••"
                                    required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pr-10 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100"
                                >
                                    <svg
                                        v-if="!showPassword"
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414L12 12m-3.122-3.122l1.415-1.414M12 12l2.122 2.122m0 0l1.414 1.414M12 12l-2.122-2.122"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 3l18 18"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <p
                                v-if="
                                    password.length > 0 && password.length < 6
                                "
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                Password must be at least 6 characters ({{
                                    password.length
                                }}/6)
                            </p>
                            <p
                                v-else-if="password.length >= 6"
                                class="mt-1 text-sm text-green-600 dark:text-green-400"
                            >
                                Password length is valid ✓
                            </p>
                        </div>
                        <div>
                            <label
                                for="confirm_password"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                            >
                                Confirm Password
                            </label>
                            <div class="relative">
                                <input
                                    v-model="confirmPassword"
                                    :type="
                                        showConfirmPassword
                                            ? 'text'
                                            : 'password'
                                    "
                                    name="confirm_password"
                                    id="confirm_password"
                                    placeholder="••••••••"
                                    required
                                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 pr-10 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                />
                                <button
                                    type="button"
                                    @click="
                                        showConfirmPassword =
                                            !showConfirmPassword
                                    "
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:text-gray-300 dark:hover:text-gray-100"
                                >
                                    <svg
                                        v-if="!showConfirmPassword"
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                    <svg
                                        v-else
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.464 8.464m1.414 1.414L12 12m-3.122-3.122l1.415-1.414M12 12l2.122 2.122m0 0l1.414 1.414M12 12l-2.122-2.122"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 3l18 18"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <p
                                v-if="
                                    password &&
                                    confirmPassword &&
                                    password !== confirmPassword
                                "
                                class="mt-1 text-sm text-red-600 dark:text-red-400"
                            >
                                Passwords do not match
                            </p>
                        </div>
                        <button
                            type="submit"
                            :disabled="!isFormValid"
                            class="w-full text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors duration-200"
                            :class="
                                isFormValid
                                    ? 'bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800'
                                    : 'bg-gray-400 cursor-not-allowed'
                            "
                        >
                            Create an account
                        </button>
                        <p
                            class="text-sm font-light text-gray-500 dark:text-gray-400 text-center"
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
            confirmPassword: "",
            showPassword: false,
            showConfirmPassword: false,
        };
    },
    computed: {
        isFormValid() {
            return (
                this.name.trim() !== "" &&
                this.password.length >= 6 &&
                this.password === this.confirmPassword
            );
        },
    },
    methods: {
        signup() {
            // Client-side validation
            if (this.password !== this.confirmPassword) {
                alert("Passwords do not match!");
                return;
            }

            if (this.password.length < 6) {
                alert("Password must be at least 6 characters long!");
                return;
            }

            axios
                .post("/signup", {
                    name: this.name,
                    password: this.password,
                    password_confirmation: this.confirmPassword,
                })
                .then(() => {
                    alert(
                        "Account created successfully! Please wait for admin approval before you can access the system."
                    );
                    this.$router.push("/");
                })
                .catch((err) => {
                    console.error("Signup failed", err);

                    // Handle specific error messages from Laravel validation
                    if (
                        err.response &&
                        err.response.data &&
                        err.response.data.errors
                    ) {
                        const errors = err.response.data.errors;
                        let errorMessage = "Signup failed:\n";

                        Object.keys(errors).forEach((key) => {
                            errorMessage += `• ${errors[key][0]}\n`;
                        });

                        alert(errorMessage);
                    } else {
                        alert("Signup failed. Please try again.");
                    }
                });
        },
    },
};
</script>
