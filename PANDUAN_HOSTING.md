# Panduan Lengkap Deploy VoucherNet ke Shared Hosting (cPanel)

Aplikasi **VoucherNet** kini **100% Siap Hosting**. Ikuti langkah mudah berikut saat Anda meng-upload ke cPanel (DomaiNesia / Dewaweb / IDCloudHost):

---

## 1. Persiapan File Zip di Laptop

Sebelum di-upload, pastikan Anda men-zip folder project `voucher_net`.
- **Yang Wajib Ikut di-Zip**:
  - Folder `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `vendor/`
  - File `.env`, `composer.json`, `.htaccess` (root), dan `public/.htaccess`
- **Yang Tidak Perlu di-Zip (Bisa Dikecualikan)**:
  - Folder `.git/` (jika ada)
  - File `.dockerignore`, `Dockerfile`, `docker-compose.yml`, `docker-entrypoint.sh` (karena di hosting biasa tidak pakai Docker)

---

## 2. Pengaturan di cPanel Hosting

### Langkah A: Pilih Versi PHP
1. Masuk ke **cPanel**.
2. Cari menu **Select PHP Version** (atau *MultiPHP Manager*).
3. Pilih **PHP 8.2** atau **PHP 8.3**.
4. Pastikan ekstensi berikut aktif (biasanya sudah default aktif):
   - `pdo_sqlite`
   - `mbstring`
   - `gd`
   - `zip`
   - `bcmath`
   - `fileinfo`

### Langkah B: Upload File Project
1. Buka menu **File Manager** di cPanel.
2. Masuk ke folder `public_html/`.
3. Upload file zip project Anda, lalu klik **Extract**.
4. *(Rekomendasi Terbaik)*: Di cPanel menu **Domains**, ubah **Document Root** domain Anda mengarah langsung ke folder `public` (misalnya: `public_html/voucher_net/public`).
   *Catatan: Jika Document Root tidak bisa diubah, jangan khawatir! File `.htaccess` yang sudah kita pasang di root akan otomatis mengarahkan pengunjung ke folder `public`.*

### Langkah C: Izin File (Permissions)
Pastikan folder berikut memiliki permission **755** atau **775** (biasanya otomatis sudah benar saat extract):
- Folder `storage/`
- Folder `bootstrap/cache/`
- Folder `database/` dan file `database/database.sqlite`

### Langkah D: Sesuaikan File `.env`
Buka file `.env` di File Manager cPanel, edit bagian berikut:
```env
APP_NAME="VoucherNet RT RW"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://namadomainanda.my.id
```
*(Ganti `namadomainanda.my.id` dengan domain resmi yang Anda beli).*

---

## 3. Fitur Cadangan Database (Backup)

1. **Backup Otomatis Hosting**:
   - Jika Anda memilih DomaiNesia atau Dewaweb, fitur **JetBackup** akan otomatis mencadangkan seluruh data Anda setiap malam.
2. **Backup Mandiri 1-Klik**:
   - Masuk ke aplikasi VoucherNet sebagai **Super Admin**.
   - Buka menu **Nomor WA & Rekening** (Settings).
   - Di bagian bawah, ada tombol hijau: **"Unduh Cadangan Database Sekarang (.sqlite)"**.
   - Sekali klik, file database langsung ter-download ke laptop/HP Anda.

---

## 4. Pengujian Setelah Online
Buka browser di HP atau laptop:
1. Akses `https://namadomainanda.my.id/login`.
2. Login dengan akun Anda:
   - Super Admin: `superadmin` / `password`
   - Admin: `admin` / `password`
   - Mitra Warung: `warung_siti` / `password`
3. Segera ubah password default melalui menu profil (ikon gembok) demi keamanan.
