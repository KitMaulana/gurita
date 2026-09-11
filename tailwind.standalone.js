module.exports = {
    content: [
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
        './app/View/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#1A365D',
                    50: '#EEF3F9',
                    100: '#D6E2F0',
                    500: '#24548F',
                    600: '#2D4A6F',
                    700: '#1A365D',
                    800: '#142B4A',
                    900: '#0E1F36',
                },
                accent: {
                    DEFAULT: '#7C9885',
                    50: '#F1F5F2',
                    100: '#DFE9E2',
                    500: '#7C9885',
                    600: '#6B8874',
                    700: '#587260',
                },
            },
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0', transform: 'translateY(4px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                'fade-in': 'fadeIn 0.15s ease-out both',
            },
        },
    },
};
