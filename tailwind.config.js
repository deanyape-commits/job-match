import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            // Tipografías de Ludan
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Space Grotesk"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Color de marca. 500 = coral de la marca (detalles, cifras grandes);
                // 700 = botones y enlaces (contraste suficiente con texto blanco).
                coral: {
                    50: '#FDF3EF',
                    100: '#FDE7DF',
                    200: '#F9CDBD',
                    300: '#F3A88E',
                    400: '#EC7F5D',
                    500: '#E4572E',
                    600: '#CF4A24',
                    700: '#B93C1B',
                    800: '#963119',
                    900: '#7A2B18',
                },
                // Fondos oscuros: portada, login y menú lateral
                carbon: {
                    DEFAULT: '#1F2937',
                    900: '#111827',
                },
                // Fondo cálido de las páginas
                crema: '#FBF8F6',
            },
            keyframes: {
                rebote: {
                    '0%, 100%': { transform: 'scale(1)' },
                    '40%': { transform: 'scale(1.3)' },
                    '70%': { transform: 'scale(0.92)' },
                },
                campana: {
                    '0%, 100%': { transform: 'rotate(0)' },
                    '20%': { transform: 'rotate(14deg)' },
                    '40%': { transform: 'rotate(-12deg)' },
                    '60%': { transform: 'rotate(8deg)' },
                    '80%': { transform: 'rotate(-4deg)' },
                },
                carrusel: {
                    from: { transform: 'translateX(0)' },
                    to: { transform: 'translateX(-50%)' },
                },
                crecer: {
                    from: { transform: 'scaleY(0)' },
                    to: { transform: 'scaleY(1)' },
                },
            },
            animation: {
                rebote: 'rebote 0.45s ease-out',
                campana: 'campana 0.8s ease-in-out',
                carrusel: 'carrusel 40s linear infinite',
                crecer: 'crecer 0.9s cubic-bezier(0.22, 1, 0.36, 1) both',
            },
        },
    },

    plugins: [forms],
};
