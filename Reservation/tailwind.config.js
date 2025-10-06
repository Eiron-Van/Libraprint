module.exports = {
  content: [
    "./**/*.php",   // make sure PHP files are included
    "./**/*.html",
    "./src/**/*.{js,ts,jsx,tsx}"
  ],
  safelist: [
    'bg-[#5c6bc0]'
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
module.exports = {
  content: [
    "./*.{html,php}",
    "./**/*.{html,php}"
  ],
  safelist: [
    'text-green-600',
    'text-red-600',
    'bg-blue-600',
    'hover:bg-blue-700'
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}
