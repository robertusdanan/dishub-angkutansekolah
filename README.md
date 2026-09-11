# Sistem Layanan Transportasi Publik - Dishub Tulungagung (Laravel 11)

Aplikasi layanan transportasi publik Dinas Perhubungan Kabupaten Tulungagung yang telah dimigrasikan penuh dari PHP native ke **Laravel 11**.

---

## 🌟 Modul & Fitur

1. **Beranda & Toggle Menu Layanan** — Pengaturan aktif/nonaktif modul layanan publik secara terpusat via tabel Supabase `menu_layanan`.
2. **Akun Pengguna Terpusat (SSO Google)** — Login Google OAuth, profil pengguna, manajemen keluarga, dan upload dokumen terenkripsi.
3. **Rute & Peta Live Angkutan Sekolah** — Peta gabungan interaktif bus/MPU, tracking posisi armada live, dan detail rute.
4. **Absensi Siswa (RFID & QR Code)** — Scan kartu RFID/QR kios publik, GPS tracking berkala tiap armada, dan portal manajemen List Link.
5. **Peta Interaktif ASDP** — Titik lokasi penyeberangan air & perahu tambangan Kabupaten Tulungagung.
6. **Pemesanan Trayek Wisata Gratis** — Jadwal keberangkatan, booking tiket online, survei kepuasan, kuota real-time, dan manajemen galeri foto/video.
7. **Pemesanan Balik Gratis** — Form pendaftaran musim mudik, verifikasi tiket, fail-closed jika periode belum dibuka admin.
8. **Panel Admin & RBAC Bertingkat** — Dashboard analitik, manajemen akun & role bertingkat (strict hierarchy), proteksi superadmin terakhir, audit log 1 tahun, dan 2FA (TOTP).

---

## 🔒 Fitur Keamanan Tambahan (#2 – #8)

- **#2 Rate Limiting Login**: Dibatasi maksimal 5 percobaan/menit per kombinasi IP + username pada form login admin & kios RFID (`AppServiceProvider`).
- **#3 Security Headers**: Global middleware (`SecurityHeaders`) menambahkan header `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `Strict-Transport-Security`, dan CSP menyeluruh.
- **#4 Audit Log Aksi Admin**: Jejak audit sensitif (create/update/delete akun & role, reset password, toggle layanan, cleanup) disimpan ke channel terpisah `storage/logs/audit.log` dengan rotasi harian dan retensi 365 hari.
- **#5 Two-Factor Authentication (TOTP)**: Dukungan verifikasi 2 langkah untuk admin (Google Authenticator / Authy) dengan kode pemulihan sekali pakai (hashed bcrypt). Ditulis murni PHP RFC 6238 tanpa library luar.
- **#8 Cleanup Terjadwal Otomatis**: Pembersihan otomatis bulanan data absensi (>6 bulan) dan pemesanan (>2 tahun) via scheduler Laravel (`app/Console/Commands/CleanupOldData.php`).

---

## ⚙️ Persyaratan Server

- PHP >= 8.2 (ekstensi: `pdo`, `mbstring`, `openssl`, `curl`, `json`, `gd` / `imagick`)
- Composer
- Database SQLite / PostgreSQL / MySQL (aplikasi menggunakan Supabase via REST API, SQLite hanya untuk internal cache/sessions bila diperlukan)

---

## 🚀 Instalasi & Menjalankan Lokal

```bash
# 1. Install dependencies
composer install

# 2. Salin environment
cp .env.example .env
php artisan key:generate

# 3. Setup database lokal (bawaan SQLite)
touch database/database.sqlite
php artisan migrate

# 4. Jalankan server lokal
php artisan serve
```

Buka `http://localhost:8000` di browser Anda.

---

## 🗄️ Setup Database Supabase

Jalankan script SQL di folder `database/supabase-sql/` pada **Supabase SQL Editor**:
- `002_two_factor_auth.sql` — Menambahkan kolom 2FA pada tabel `admin_accounts`.

---

## ⏰ Konfigurasi Crontab Produksi

Untuk menjalankan scheduler cleanup data lama otomatis (#8), tambahkan baris berikut di crontab server:

```crontab
* * * * * cd /path-ke-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📦 Deployment Produksi

Sebelum go-live ke domain resmi, pastikan file `.env` disesuaikan:
```ini
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
APP_URL=https://domain-resmi-anda.go.id
```
Lalu jalankan:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
