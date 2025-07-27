const defaultTheme = require('tailwindcss/defaultTheme');
const colors = require('tailwindcss/colors');

module.exports = {
    important: true,
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/**/*.js',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
        },
        container: {
            padding: {
                DEFAULT: '0.5rem',
                sm: '1rem',
                lg: '3rem',
                xl: '5rem',
                '2xl': '6rem',
            },
        },
        backgroundColor: theme => ({
            ...theme('colors'),
            'primary': '#0c4b05',
            'accent': '#129b00',
            'accent2': '#1a8707',
            'accent3': '#187307',
            'accent4': '#186907',
            'secondary': '#ffcd00',
            'tertiary': '#de1900',
            'maroon': '#800000',
            'default': '#002afc',
        }),
        borderColor: theme => ({
            ...theme('colors'),
            'primary': '#0c4b05',
            'accent': '#129b00',
            'accent2': '#1a8707',
            'accent3': '#187307',
            'accent4': '#186907',
            'secondary': '#ffcd00',
            'tertiary': '#de1900',
            'maroon': '#800000',
            'default': '#002afc',
        }),
        colors:{
            transparent: 'transparent',
            current: 'currentColor',
            black: colors.black,
            white: colors.white,
            gray: colors.zinc,
            green: colors.green,
            indigo: colors.indigo,
            red: colors.red,
            yellow: colors.amber,
            blue: colors.blue
        }

    },

    // variants: {
    //     extend: {
    //         opacity: ['disabled'],
    //     },
    // },

    plugins: [require('@tailwindcss/forms')],
};