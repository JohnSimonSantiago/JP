<template>
    <section class="flex top-16 bg-gray-50 dark:bg-gray-900">
        <div
            class="flex flex-col items-center justify-center px-6 py-8 mx-auto md:h-screen lg:py-0"
        >
            <!-- Enhanced Logo Section -->
            <div class="flex flex-col items-center mb-8">
                <div
                    class="flex items-center justify-center w-28 h-28 mb-4 bg-blue-100 dark:bg-blue-900 rounded-full"
                >
                    <img
                        src="../../../public/logotemp.png"
                        alt="Level Up Logo"
                        class="w-20 h-20 rounded-full"
                    />
                </div>
                <h1
                    class="text-3xl font-bold text-gray-900 dark:text-white text-center"
                >
                    Welcome to Suki Me
                </h1>
                <p
                    class="text-sm text-gray-600 dark:text-gray-400 mt-2 text-center"
                >
                    Digital Membership
                </p>
            </div>

            <div
                class="w-full bg-white rounded-lg shadow dark:border md:mt-0 sm:max-w-md xl:p-0 dark:bg-gray-800 dark:border-gray-700"
            >
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h2
                        class="text-xl font-bold leading-tight tracking-tight text-gray-900 md:text-2xl dark:text-white text-center"
                    >
                        Sign in to your account
                    </h2>

                    <!-- Error Messages with different styling for approval pending -->
                    <div
                        v-if="errorMsg != ''"
                        class="p-4 mb-4 text-sm rounded-lg"
                        :class="
                            errorType === 'approval_pending'
                                ? 'text-yellow-800 bg-yellow-50 border border-yellow-300 dark:bg-gray-800 dark:text-yellow-400 dark:border-yellow-600'
                                : 'text-red-800 bg-red-50 border border-red-300 dark:bg-gray-800 dark:text-red-400 dark:border-red-600'
                        "
                        role="alert"
                    >
                        <div class="flex">
                            <!-- Warning icon for approval pending -->
                            <svg
                                v-if="errorType === 'approval_pending'"
                                class="flex-shrink-0 w-4 h-4 mt-0.5 mr-2"
                                aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"
                                />
                            </svg>
                            <!-- Error icon for other errors -->
                            <svg
                                v-else
                                class="flex-shrink-0 w-4 h-4 mt-0.5 mr-2"
                                aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"
                                />
                            </svg>
                            <div>
                                <span class="font-medium">
                                    {{
                                        errorType === "approval_pending"
                                            ? "Account Pending Approval"
                                            : "Log in failed!"
                                    }}
                                </span>
                                <div class="mt-1">{{ errorMsg }}</div>
                                <!-- Additional help text for approval pending -->
                                <div
                                    v-if="errorType === 'approval_pending'"
                                    class="mt-2 text-sm"
                                >
                                    Your registration has been submitted and is
                                    awaiting administrator approval. You will be
                                    able to log in once your account has been
                                    reviewed and approved.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div
                        v-if="message"
                        class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-300 dark:bg-gray-800 dark:text-green-400 dark:border-green-600"
                        role="alert"
                    >
                        <div class="flex">
                            <svg
                                class="flex-shrink-0 w-4 h-4 mt-0.5 mr-2"
                                aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"
                                />
                            </svg>
                            <span class="font-medium">{{ message }}</span>
                        </div>
                    </div>

                    <!-- Loading indicator -->
                    <div
                        v-if="isLoading"
                        class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                        role="alert"
                    >
                        <span class="font-medium">Signing in...</span>
                    </div>

                    <form
                        class="space-y-4 md:space-y-6"
                        action="#"
                        @submit.prevent="login"
                        @click="clearErrMsg"
                    >
                        <div>
                            <label
                                for="name"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                >Your username</label
                            >
                            <input
                                v-model="name"
                                type="text"
                                name="name"
                                id="name"
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="Enter your username"
                                required=""
                                :disabled="isLoading"
                            />
                        </div>
                        <div>
                            <label
                                for="password"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                >Password</label
                            >
                            <input
                                v-model="password"
                                type="password"
                                name="password"
                                id="password"
                                placeholder="••••••••"
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                required=""
                                :disabled="isLoading"
                            />
                        </div>

                        <button
                            type="submit"
                            class="w-full text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                            :disabled="isLoading"
                        >
                            {{ isLoading ? "Signing in..." : "Sign in" }}
                        </button>

                        <p
                            class="text-sm font-light text-gray-500 dark:text-gray-400 text-center"
                        >
                            <RouterLink to="/signup">
                                Don't have an account yet?
                                <a
                                    href="#"
                                    class="font-medium text-blue-600 hover:underline dark:text-blue-500"
                                    >Sign up</a
                                >
                            </RouterLink>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    data() {
        return {
            name: "",
            password: "",
            message: this.$route.query.messageSent,
            errorMsg: "",
            errorType: "", // Track the type of error for different styling
            isLoading: false,
        };
    },
    methods: {
        async login() {
            // Clear previous errors and set loading state
            this.errorMsg = "";
            this.errorType = "";
            this.isLoading = true;

            try {
                const { name, password } = this;

                const response = await axios.post("/login", { name, password });

                console.log("Login response:", response.data);

                if (response.status === 200 && response.data.token) {
                    const { token, user, message } = response.data;

                    // Store the token with the original key that all other components use
                    localStorage.setItem("auth-token", token);

                    // Store user data (optional, for offline access)
                    localStorage.setItem("user", JSON.stringify(user));

                    // Set axios default header for future requests
                    axios.defaults.headers.common[
                        "Authorization"
                    ] = `Bearer ${token}`;

                    console.log(
                        "✅ Token stored successfully with key 'auth-token':",
                        token
                    );
                    console.log("✅ User role:", user.role);

                    // Redirect to dashboard
                    this.$router.push("/dashboard");
                } else {
                    throw new Error("Login successful but no token received");
                }
            } catch (error) {
                console.error("Login error:", error);

                if (
                    error.response &&
                    error.response.data &&
                    error.response.data.message
                ) {
                    this.errorMsg = error.response.data.message;

                    // Check if this is an approval pending error
                    if (error.response.data.type === "approval_pending") {
                        this.errorType = "approval_pending";
                    }
                } else {
                    this.errorMsg =
                        "An unexpected error occurred. Please try again.";
                }
            } finally {
                this.isLoading = false;
            }
        },

        clearErrMsg() {
            this.errorMsg = "";
            this.errorType = "";
        },
    },

    mounted() {
        // Check for token with the key that all components use
        const token = localStorage.getItem("auth-token");
        if (token) {
            // Set the authorization header
            axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
            // Redirect to dashboard if already logged in
            this.$router.push("/dashboard");
        }
    },
};
</script>
