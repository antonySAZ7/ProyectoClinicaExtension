import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                brand: {
                    primary: '#35342f',
                    primaryStrong: '#24231f',
                    soft: '#eceee7',
                    surface: '#f8f8f3',
                    muted: '#6b6f66',
                    border: '#cdd1c6',
                    contrast: '#fdfdf9',

                    success: '#22c55e',
                    successLight: '#dcfce7',

                    error: '#d4183d',
                    errorLight: '#fee2e2',

                    warning: '#f59e0b',
                    warningLight: '#fef3c7',

                    info: '#3b82f6',
                    infoLight: '#dbeafe',
                }
            }
        },
    },

    plugins: [forms],
};