/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/View/Components/**/*.php',
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['DM Sans', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
                display: ['DM Serif Display', 'Georgia', 'serif'],
            },
            colors: {
                brand: {
                    50:  '#edfcfc',
                    100: '#d0f7f7',
                    200: '#a4eeee',
                    300: '#67e2e2',
                    400: '#2ecfcf',
                    500: '#00b5b5',
                    600: '#008f8f',
                    700: '#006e6e',
                    800: '#005757',
                    900: '#1a4e6a',
                    950: '#0d2d3e',
                },
            },
            boxShadow: {
                card: '0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
};
