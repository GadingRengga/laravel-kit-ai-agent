import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class', // WAJIB: dark mode ikut class .dark di <html>, bukan preferensi OS
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['DM Sans', ...defaultTheme.fontFamily.sans],
                mono: ['DM Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                brand: {
                    50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                    400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                    800: '#3730a3', 900: '#312e81',
                },
                surface: {
                    50: '#fafafa', 100: '#f5f5f5', 200: '#eeeeee', 300: '#e0e0e0',
                    400: '#bdbdbd', 500: '#9e9e9e', 600: '#757575', 700: '#616161',
                    800: '#424242', 900: '#212121',
                },
            },
        },
    },
    plugins: [],
};