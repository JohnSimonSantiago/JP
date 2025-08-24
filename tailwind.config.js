const defaultTheme = require("tailwindcss/defaultTheme");
const colors = require("tailwindcss/colors");

module.exports = {
    important: true,
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
        "./resources/**/*.js",
    ],
    darkMode: "class",
    theme: {
        extend: {
            fontFamily: {
                sans: ["Nunito", ...defaultTheme.fontFamily.sans],
            },
        },
        container: {
            padding: {
                DEFAULT: "0.5rem",
                sm: "1rem",
                lg: "3rem",
                xl: "5rem",
                "2xl": "6rem",
            },
        },
        backgroundColor: (theme) => ({
            ...theme("colors"),
            primary: "#0c4b05",
            accent: "#129b00",
            accent2: "#1a8707",
            accent3: "#187307",
            accent4: "#186907",
            secondary: "#ffcd00",
            tertiary: "#de1900",
            maroon: "#800000",
            default: "#002afc",
        }),
        borderColor: (theme) => ({
            ...theme("colors"),
            primary: "#0c4b05",
            accent: "#129b00",
            accent2: "#1a8707",
            accent3: "#187307",
            accent4: "#186907",
            secondary: "#ffcd00",
            tertiary: "#de1900",
            maroon: "#800000",
            default: "#002afc",
        }),
        colors: {
            transparent: "transparent",
            current: "currentColor",
            black: colors.black,
            white: colors.white,
            gray: colors.gray,
            slate: colors.slate, // ADDED: For silver medals
            purple: colors.purple,
            green: colors.green,
            indigo: colors.indigo,
            red: colors.red,
            yellow: colors.amber, // You had this already
            amber: colors.amber, // ADDED: For bronze medals
            orange: colors.orange, // ADDED: For bronze medals
            blue: colors.blue,
        },
    },

    plugins: [require("@tailwindcss/forms")],
};
