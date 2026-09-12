/**
 * Konfigurasi build untuk public/assets/css/tailwind.min.css.
 *
 * Halaman ini (dan seluruh app) sudah punya CSS sendiri (admin-shell.css,
 * style.css tiap modul), jadi preflight/reset Tailwind DIMATIKAN - sama seperti
 * pengaturan inline `tailwind = { config: { corePlugins: { preflight: false } } }`
 * yang sebelumnya dipakai CDN Play.
 *
 * Cara rebuild (butuh Node/Tailwind CLI, hanya dilakukan di mesin development -
 * hasilnya ikut di-commit sehingga cPanel TIDAK perlu Node sama sekali):
 *
 *   npx @tailwindcss/cli -i resources/css/tailwind.css \
 *       -o public/assets/css/tailwind.min.css --minify
 *
 * atau pakai binary standalone Tailwind v3:
 *
 *   ./tailwindcss -i resources/css/tailwind.css \
 *       -o public/assets/css/tailwind.min.css --minify
 */
module.exports = {
  content: [
    './resources/views/**/*.blade.php',
    './public/assets/**/*.js',
  ],
  corePlugins: {
    preflight: false,
  },
  theme: {
    extend: {},
  },
  plugins: [],
};
