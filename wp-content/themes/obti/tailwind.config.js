module.exports = {
  content: [
    "./**/*.php",
    "../../plugins/obti-elementor-widgets/**/*.{php,js}",
    "../../plugins/obti-booking/**/*.{php,js}"
  ],
  theme: {
    extend: {
      container: {
        center: true,
        screens: { DEFAULT: '1280px' },
      },
    },
  },
  plugins: [],
}
