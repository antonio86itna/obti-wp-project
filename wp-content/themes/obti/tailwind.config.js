module.exports = {
  content: [
    "./**/*.php",
    "../../plugins/obti-elementor-widgets/**/*.{php,js}"
  ],
  theme: {
    extend: {
      container: {
        center: true,
        screens: { DEFAULT: '1280px' },
      },
      colors: {
        'theme-primary': '#16a34a',
        'yellow-400': '#facc15',
      },
      keyframes: {
        blob: {
          '0%': { transform: 'translate(0px, 0px) scale(1)' },
          '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
          '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
          '100%': { transform: 'translate(0px, 0px) scale(1)' },
        },
      },
      animation: {
        blob: 'blob 8s infinite',
      },
    },
  },
  plugins: [],
}
