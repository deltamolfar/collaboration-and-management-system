import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'accent': 'rgba(240,70,70,255)',
            }
        },
    },

    plugins: [forms],

    safelist: [
        {pattern: /^bg-(red|blue|green|yellow)-[1-4]00$/, variants: ['hover']},
        {pattern: /^border-(red|blue|green|yellow)-[1-4]00$/,},
    ],
};
